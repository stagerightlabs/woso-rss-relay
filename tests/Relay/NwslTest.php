<?php

namespace Tests\Relay;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Feed;
use Relay\Sources\Nwsl;
use Tests\TestCase;

class NwslTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_nwsl_index()
    {
        Http::fake(['*' => Http::response($this->stub('nwsl.json'))]);
        $source = new Nwsl();
        $response = $source->index();
        $links = $source->links($response);

        $this->assertCount(16, $links);
        $this->assertEquals('https://dapi.nwslsoccer.com/v2/content/en-us/stories/2024-25-nwsl-offseason-transaction-tracker', $links->first()->url);
        $this->assertEquals('57a350e9-eafe-4eb9-b5fb-cb71b4997a74', $links->first()->key);
    }

    #[Test]
    public function it_can_parse_an_nwsl_article()
    {
        Http::fake(['*' => Http::response($this->stub('nwsl-article.json'))]);
        $source = new Nwsl();
        $response = $source->content('example.com');
        $article = $source->article($response);
        $summary = "Stay up-to-date with the latest NWSL offseason moves.\nFor more information on free agency:\nFree Agency Hub\nFree Agency Explained\nAll updates since the end of the regular season. Last updated January 9, 2025.";

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals(Feed::NWSL, $article->feed);
        $this->assertEquals('57a350e9-eafe-4eb9-b5fb-cb71b4997a74', $article->key);
        $this->assertEquals('2024-25 NWSL Offseason Transaction Tracker', $article->title);
        $this->assertEquals('https://www.nwslsoccer.com/news/2024-25-nwsl-offseason-transaction-tracker', $article->link);
        $this->assertEquals('https://www.nwslsoccer.com/_next/image?url=https%3A%2F%2Fimages.nwslsoccer.com%2Fimage%2Fprivate%2Ft_ratio21_9-size60%2Fprd%2Fjictgf8szeefvgqmos9e&w=1920&q=75', $article->image);
        $this->assertEquals($summary, $article->summary);

        $this->assertStringContainsString("<p>Stay up-to-date with the latest NWSL offseason moves.</p>\n", $article->content);
    }

    #[Test]
    public function it_can_find_the_largest_content_section()
    {
        Http::fake(['*' => Http::response($this->stub('nwsl-multipart-article.json'))]);
        $source = new Nwsl();
        $response = $source->content('example.com');
        $article = $source->article($response);

        $this->assertStringContainsString("<p>The award, first given in 1998,", $article->content);
    }
}
