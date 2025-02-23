<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Dash;
use Tests\TestCase;

class DashTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_pride_index()
    {
        Http::fake(['*' => Http::response($this->stub('dash.html'))]);
        $parser = new Dash();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(18, $entries);
        $this->assertEquals('https://www.houstondynamofc.com/houstondash/news/houston-dash-announce-updated-preseason-roster-x2054', $entries->first()->url);
        $this->assertEquals('houston-dash-announce-updated-preseason-roster-x2054', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_pride_article()
    {
        Http::fake(['*' => Http::response($this->stub('dash-article.html'))]);
        $parser = new Dash();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://images.mlssoccer.com/image/private/t_editorial_landscape_8_desktop_mobile/f_auto/mls-hou/hjptmg0wkizx0ilb9sf8.jpg" alt="Houston Dash Transfer Tarciane to Olympique Lyonnais Féminin for Record Fee" /></p>\n
        <p>HOUSTON (Feb. 2, 2025) - The Houston Dash completed the transfer of Brazilian defender Tarciane to Olympique Lyonnais Féminin in France for an undisclosed fee, both teams announced today. The transfer fee is a team record and among the highest in NWSL history.</p>
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Houston Dash Transfer Tarciane to Olympique Lyonnais Féminin for Record Fee', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('houston-dash', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Houston Dash', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-02-02', $article->published_at->format('Y-m-d'));
    }
}
