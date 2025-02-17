<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Pride;
use Tests\TestCase;

class PrideTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_pride_index()
    {
        Http::fake(['*' => Http::response($this->stub('pride.html'))]);
        $parser = new Pride();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(18, $entries);
        $this->assertEquals('https://www.orlandocitysc.com/pride/news/orlando-pride-acquires-2023-world-cup-winner-oihane-hernandez', $entries->first()->url);
        $this->assertEquals('orlando-pride-acquires-2023-world-cup-winner-oihane-hernandez', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_pride_article()
    {
        Http::fake(['*' => Http::response($this->stub('pride-article.html'))]);
        $parser = new Pride();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://images.mlssoccer.com/image/private/t_editorial_landscape_8_desktop_mobile/f_auto/mls-orl/wczcvkezy4mwwnac7gt7.jpg" alt="Orlando Pride acquires 2023 World Cup winner Oihane Hernández" /></p>\n
        <p>ORLANDO, Fla. (Feb. 14, 2025) — The Orlando Pride has acquired defender Oihane Hernández on an immediate transfer from Real Madrid Femenino for an undisclosed fee and have signed the former World Cup winner to a two-year contract through the 2026 season, with a mutual option for 2027, it was announced today.</p>
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Orlando Pride acquires 2023 World Cup winner Oihane Hernández', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('orlando-pride', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Orlando Pride Communications', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-02-14', $article->published_at->format('Y-m-d'));
    }
}
