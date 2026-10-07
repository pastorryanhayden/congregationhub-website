<?php
namespace Tests\Unit;
use App\Support\DataTransformer;
use PHPUnit\Framework\TestCase;
class BlogTransformerTest extends TestCase
{
    public function test_blog_listing_preserves_articles_and_uses_its_own_template(): void
    {
        $posts = [['title'=>'Welcome', 'url'=>'/blog/welcome', 'description'=>'Our church news']];
        [$template, $props] = (new DataTransformer)->transformPage('blog', 'blog', ['pageTitle'=>'Blog', 'posts'=>$posts]);
        $this->assertSame('blog', $template);
        $this->assertSame($posts, $props['posts']);
    }
}
