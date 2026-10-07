<?php

namespace Tests\Feature;

use App\Services\CongregationHubApi;
use CongregationHub\Themes\Support\ContentSections;
use Tests\TestCase;

class ContentSectionsTest extends TestCase
{
    public function test_heading_links_are_unique_stable_and_preserve_existing_ids(): void
    {
        $content = '<h2 id="existing">Existing</h2><p>Intro.</p><h2>Faith &amp; Grace</h2><p>First section.</p><h2>Faith &amp; Grace</h2><p>Second section.</p><h2 id="existing">Duplicate</h2>';
        $document = ContentSections::prepare($content);
        $ids = array_column($document['toc'], 'anchor');
        $this->assertSame(['existing', 'section-faith-grace', 'section-faith-grace-2', 'section-duplicate'], $ids);
        $this->assertSame($ids, array_column(ContentSections::prepare($content)['toc'], 'anchor'));
        $this->assertSame('Faith & Grace First section.', $document['sections'][1]['text']);
        $this->assertStringNotContainsString('Second section', $document['sections'][1]['text']);
        foreach ($ids as $id) $this->assertStringContainsString('id="'.$id.'"', $document['html']);
    }

    public function test_constitution_renders_collapsible_toc_with_matching_deep_links(): void
    {
        $this->withoutVite();
        $this->mock(CongregationHubApi::class)->shouldReceive('page')->with('constitution', [])->once()->andReturn([
            '_template' => 'content-page', 'pageTitle' => 'Constitution',
            'pageContent' => "## Article 1: Organization\n\n### Section 1.01 - Name\n\nOur name.\n\n## Article 2: Doctrine\n\n### Section 2.01 - Statement of Faith\n\n## A. The Holy Scriptures\n\nOur beliefs.",
        ]);
        $this->get('/constitution')->assertOk()->assertSee('On this page')
            ->assertSee('href="#section-a-the-holy-scriptures"', false)
            ->assertSee('id="section-a-the-holy-scriptures"', false);
    }

    public function test_short_content_does_not_get_an_empty_toc(): void
    {
        $html = view('themes::components.default.long-content', ['content' => 'A short paragraph.'])->render();
        $this->assertStringNotContainsString('<nav', $html);
        $this->assertStringContainsString('A short paragraph.', $html);
    }
}
