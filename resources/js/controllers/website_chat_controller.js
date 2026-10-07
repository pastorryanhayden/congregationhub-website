import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['launcher', 'panel', 'messages', 'question', 'send', 'notice'];
    static values = { prompt: String, error: String, thinking: String };

    async connect() {
        this.abort = new AbortController();
        this.history = [];
        this.storageKey = 'church-website-chat:v1';
        try {
            const response = await fetch('/chat/status', { headers: { Accept: 'application/json' }, signal: this.abort.signal });
            if (!response.ok) return;
            const status = await response.json();
            if (!status.enabled || !this.element.isConnected) return;
            this.launcherTarget.textContent = this.promptValue.replace(':church', status.name);
            this.element.hidden = false;
            // Only store display history in this tab; it is never included in model instructions.
            try {
                const saved = JSON.parse(sessionStorage.getItem(this.storageKey));
                if (saved?.name === status.name && Array.isArray(saved.messages)) {
                    this.history = saved.messages.slice(-20);
                    this.history.forEach(message => this.render(message));
                    this.panelTarget.hidden = !saved.open;
                }
            } catch (_) { /* Storage may be unavailable. */ }
            this.name = status.name;
            this.launcherTarget.setAttribute('aria-expanded', String(!this.panelTarget.hidden));
        } catch (_) { /* A disabled/unavailable chatbot should not interrupt the website. */ }
    }

    disconnect() { this.abort?.abort(); }

    toggle() {
        this.panelTarget.hidden = !this.panelTarget.hidden;
        this.launcherTarget.setAttribute('aria-expanded', String(!this.panelTarget.hidden));
        if (!this.panelTarget.hidden) this.questionTarget.focus();
        else this.launcherTarget.focus();
        this.save();
    }

    close(event) { if (event.key === 'Escape' && !this.panelTarget.hidden) this.toggle(); }

    clear() {
        this.history = [];
        this.messagesTarget.replaceChildren();
        this.save();
    }

    save() {
        try { sessionStorage.setItem(this.storageKey, JSON.stringify({ name: this.name, open: !this.panelTarget.hidden, messages: this.history.slice(-20) })); } catch (_) {}
    }

    render(message) {
        if (!message || typeof message.text !== 'string') return;
        const box = document.createElement('div');
        box.className = message.role === 'user' ? 'chat chat-end' : 'chat chat-start';
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble whitespace-pre-wrap break-words ' + (message.role === 'user' ? 'chat-bubble-primary' : '');
        bubble.textContent = message.text;
        box.append(bubble);
        if (Array.isArray(message.sources)) {
            const links = document.createElement('div');
            links.className = 'chat-footer flex flex-wrap gap-3 pt-2 opacity-100';
            message.sources.forEach(source => {
                // Citations must remain on this church's website. Never render model HTML.
                if (typeof source.url !== 'string' || !/^\/(?!\/)/.test(source.url) || source.url.includes('\\')) return;
                const link = document.createElement('a');
                link.href = source.url;
                link.className = 'link link-primary';
                link.textContent = String(source.title || source.url);
                links.append(link);
            });
            box.append(links);
        }
        this.messagesTarget.append(box);
        this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
    }

    submitOnEnter(event) {
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
        event.preventDefault();
        event.target.form.requestSubmit();
    }

    async send(event) {
        event.preventDefault();
        const question = this.questionTarget.value.trim();
        if (this.busy || question.length < 3 || question.length > 800) return;
        this.busy = true;
        this.sendTarget.disabled = true;
        this.noticeTarget.textContent = this.thinkingValue;
        const message = { role: 'user', text: question };
        this.history.push(message);
        this.render(message);
        this.questionTarget.value = '';
        this.save();
        try {
            const response = await fetch('/chat', {
                method: 'POST', signal: this.abort.signal,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question })
            });
            if (!response.ok) throw new Error('unavailable');
            const answer = await response.json();
            if (typeof answer.answer !== 'string') throw new Error('invalid_response');
            const reply = { role: 'assistant', text: answer.answer, sources: answer.sources };
            this.history.push(reply);
            this.render(reply);
            this.noticeTarget.textContent = '';
            this.save();
        } catch (error) {
            if (error.name !== 'AbortError') this.noticeTarget.textContent = this.errorValue;
        } finally {
            this.busy = false;
            this.sendTarget.disabled = false;
        }
    }
}
