<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteChatProxyTest extends TestCase
{
    public function test_proxy_uses_server_church_credentials_and_drops_visitor_tenant_fields(): void
    {
        config(['website.api_url' => 'https://hub.example', 'website.api_token' => 'server-token']);
        Http::fake(['hub.example/*' => Http::response(['answer' => 'Welcome', 'sources' => []])]);
        $this->withHeaders(['X-Church-Domain' => 'attacker.example'])->postJson('/chat', ['question' => 'What do you believe?', 'church_id' => 999, 'token' => 'attacker-token'])
            ->assertOk()->assertJsonPath('answer', 'Welcome')->assertHeader('Cache-Control', 'no-store, private');
        Http::assertSent(fn ($r) => $r->url() === 'https://hub.example/api/website/chat' && $r->hasHeader('Authorization', 'Bearer server-token') && $r->data() === ['question' => 'What do you believe?']);
    }

    public function test_proxy_reports_safe_errors_without_upstream_details(): void
    {
        config(['website.api_url' => 'https://hub.example', 'website.api_token' => null]);
        Http::fake(['hub.example/*' => Http::response(['message' => 'Secret provider credentials'], 500)]);
        $this->postJson('https://sayre.example/chat', ['question' => 'What do you believe?'])->assertStatus(503)->assertDontSee('Secret provider credentials');
        Http::assertSent(fn ($r) => $r->hasHeader('X-Church-Domain', 'sayre.example'));
    }

    public function test_invalid_questions_do_not_reach_the_hub(): void
    {
        Http::fake();
        $this->postJson('/chat', ['question' => str_repeat('x', 801)])->assertUnprocessable();
        Http::assertNothingSent();
    }
}
