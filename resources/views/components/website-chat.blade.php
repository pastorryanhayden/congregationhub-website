<div hidden data-theme="corporate" data-controller="website-chat" data-action="keydown->website-chat#close"
     data-website-chat-prompt-value="{{ __('chatbot.prompt') }}"
     data-website-chat-error-value="{{ __('chatbot.unavailable_now') }}"
     data-website-chat-thinking-value="{{ __('chatbot.thinking') }}"
     class="fixed bottom-4 right-4 z-50 max-w-[calc(100vw-2rem)]">
    <section hidden id="church-chat-panel" aria-label="{{ __('chatbot.title') }}" data-website-chat-target="panel"
             class="card bg-base-100 text-base-content shadow-xl border border-base-300 w-96 max-w-full mb-3 max-h-[calc(100dvh-6rem)] overflow-y-auto">
        <div class="card-body p-4 gap-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-bold">{{ __('chatbot.title') }}</h2>
                <button type="button" class="btn btn-ghost btn-sm" data-action="website-chat#toggle" aria-label="{{ __('chatbot.close') }}">×</button>
            </div>
            <p class="text-sm">{{ __('chatbot.intro') }}</p>
            <div data-website-chat-target="messages" role="log" aria-live="polite" aria-label="{{ __('chatbot.messages') }}" class="overflow-y-auto max-h-[40dvh] space-y-3"></div>
            <p role="status" class="text-sm" data-website-chat-target="notice"></p>
            <form data-action="submit->website-chat#send" class="space-y-2">
                <label for="church-chat-question" class="text-sm font-medium">{{ __('chatbot.question') }}</label>
                <textarea id="church-chat-question" data-website-chat-target="question" data-action="keydown->website-chat#submitOnEnter" class="textarea textarea-bordered w-full" rows="2" required minlength="3" maxlength="800"></textarea>
                <div class="flex justify-between gap-2">
                    <button type="button" class="btn btn-ghost btn-sm" data-action="website-chat#clear">{{ __('chatbot.clear') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-website-chat-target="send">{{ __('chatbot.send') }}</button>
                </div>
            </form>
        </div>
    </section>
    <button type="button" data-website-chat-target="launcher" data-action="website-chat#toggle" aria-controls="church-chat-panel" aria-expanded="false" class="btn btn-primary shadow-lg whitespace-normal h-auto min-h-12 py-3 max-w-full"></button>
</div>
