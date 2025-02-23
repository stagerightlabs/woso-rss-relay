<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Current;
use Tests\TestCase;

class CurrentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_current_index()
    {
        Http::fake(['*' => Http::response($this->stub('current.html'))]);
        $parser = new Current();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(25, $entries);
        $this->assertEquals('https://www.kansascitycurrent.com/news/kansas-city-current-forward-haley-hopkins-agree-to-contract-extension', $entries->first()->url);
        $this->assertEquals('kansas-city-current-forward-haley-hopkins-agree-to-contract-extension', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_current_article()
    {
        Http::fake(['*' => Http::response($this->stub('current-article.html'))]);
        $parser = new Current();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://www.kansascitycurrent.com/assets/images/processed/NoCrop_1000x1000/aa6a5337ff33492191eb2e461d7af466.png" alt="Kansas City Current, forward Haley Hopkins agree to contract extension" /></p>\n
        <p>KANSAS CITY (Feb. 20, 2025) — The Kansas City Current and forward Haley Hopkins have agreed to a two-year contract extension through the 2027 National Women’s Soccer League (NWSL) season. Haley joined the club in late January as part of a trade with the North Carolina Courage and is currently with the team in Bradenton, Florida for preseason training.</p>
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Kansas City Current, forward Haley Hopkins agree to contract extension', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('kc-current', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Kansas City Current', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-02-20', $article->published_at->format('Y-m-d'));
    }
}
