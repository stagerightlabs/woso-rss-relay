<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Toronto;
use Tests\TestCase;

class TorontoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_toronto_index()
    {
        Http::fake(['*' => Http::response($this->stub('toronto.html'))]);
        $parser = new Toronto();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.afctoronto.ca/news/afc-toronto-gears-up-for-start-of-historic-1st-season-next-week', $entries->first()->url);
        $this->assertEquals('afc-toronto-gears-up-for-start-of-historic-1st-season-next-week', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_toronto_article()
    {
        Http::fake(['*' => Http::response($this->stub('toronto-article.html'))]);
        $parser = new Toronto();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/admin/Esther.JPG" alt="AFC Toronto Signs Nigerian International Esther Okoronkwo" /></p>\n
        <p>Toronto, ON (February 24, 2025) - AFC Toronto is delighted to announce the signing of Nigerian forward Esther Okoronkwo. The 27-year-old arrives for her next chapter in Toronto having spent the 2024 season playing in the Chinese Women’s Super League with Changchun Dazhong.</p>
        <p>Okoronkwo started her professional career in France with AS Saint-Étienne, before moving to the Canary Islands and playing for UDG Tenerife in Spain’s Liga F. The talented forward also bringsvaluable international experience to AFC Toronto after featuring on the biggest stages, including the FIFA Women’s World Cup and Olympics.</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('AFC Toronto Signs Nigerian International Esther Okoronkwo', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('afc-toronto', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('AFC Toronto', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-02-24', $article->published_at->format('Y-m-d'));
    }
}
