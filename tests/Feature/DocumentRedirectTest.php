<?php

namespace Tests\Feature;

use App\Services\CongregationHubApi;
use Tests\TestCase;

class DocumentRedirectTest extends TestCase
{
    public function test_document_pages_redirect_to_the_uploaded_file(): void
    {
        $api = $this->mock(CongregationHubApi::class);
        foreach (['doctrine', 'gospel', 'constitution'] as $path) {
            $api->shouldReceive('page')->with($path, [])->once()->andReturn([
                '_template' => 'content-page', 'useDocument' => true,
                'documentUrl' => 'https://files.example/beliefs.pdf', 'pageContent' => 'Old text',
            ]);
            $this->get('/'.$path)->assertRedirect('https://files.example/beliefs.pdf')->assertStatus(302);
        }
    }

    public function test_text_mode_does_not_redirect_even_with_an_old_document_url(): void
    {
        $this->withoutVite();
        $this->mock(CongregationHubApi::class)->shouldReceive('page')->with('doctrine', [])->once()->andReturn([
            '_template' => 'content-page', 'useDocument' => false,
            'documentUrl' => 'https://files.example/beliefs.pdf', 'pageContent' => 'Our beliefs',
        ]);
        $this->get('/doctrine')->assertOk()->assertSee('Our beliefs');
    }
}
