<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Gotham;
use Relay\Sites\GothamFC;
use Tests\TestCase;

class GothamTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_gotham_index_page()
    {
        Http::fake(['*' => Http::response($this->stub('gotham.json'))]);
        $parser = new Gotham();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.gothamfc.com/news/gotham-fc-announces-2025-nwsl-season-schedule', $entries->first()->url);
        $this->assertEquals('a31c4357-4e7b-479a-ad73-685148acaa9a', $entries->first()->key);
        $this->assertEquals('https://www.gothamfc.com/news/gotham-fc-announces-2025-nwsl-season-schedule', $entries->first()->context['url']);
        $this->assertEquals('2025-01-22T20:00:44.380Z', $entries->first()->context['date']);
    }

    #[Test]
    public function it_can_parse_a_gotham_article()
    {
        Http::fake(['*' => Http::response($this->stub('gotham-article.html'))]);
        $source = new Gotham();
        $response = Http::get('example.com');
        $article = $source->article($response, ['url' => 'example.com', 'date' => '2025-01-22T20:00:44.380Z']);

        $summary = "HARRISON, NJ (Wednesday, January 22, 2025) - Goalkeeper and NWSL Champion Michelle Betos has announced her retirement from professional soccer today, concluding an incredible career.";

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals(GothamFC::slug(), $article->site);
        $this->assertEquals('e369ac542899e11483ad861c8b8353fe', $article->key);
        $this->assertEquals('NWSL Champion and Goalkeeper Michelle Betos Announces Retirement from Professional Soccer', $article->title);
        $this->assertEquals('example.com', $article->link);
        $this->assertEquals($summary, $article->summary);
        $this->assertEquals('Gotham FC', $article->author);
        $this->assertNotNull($article->published_at);

        $this->assertStringContainsString("<p> <strong>HARRISON, NJ </strong>(<em>Wednesday, January 22, 2025</em>) - Goalkeeper and NWSL Champion Michelle Betos", $article->content);
        $this->assertStringContainsString("https://www.gothamfc.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2F8mpkhoms%2Fproduction%2Fd55f37811d6f8b668a85d3a3b010e44dff712738-1320x880.png%3Fw%3D1600&w=1200&q=75", $article->content);
    }
}
