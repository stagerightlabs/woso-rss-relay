<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Tides;
use Tests\TestCase;

class TidesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_tides_index()
    {
        Http::fake(['*' => Http::response($this->stub('tides.html'))]);
        $parser = new Tides();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(6, $entries);
        $this->assertEquals('https://www.tidesfc.ca/news/halifax-tides-fc-signs-annika-leslie-defender', $entries->first()->url);
        $this->assertEquals('halifax-tides-fc-signs-annika-leslie-defender', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_tides_article()
    {
        Http::fake(['*' => Http::response($this->stub('tides-article.html'))]);
        $parser = new Tides();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://d2kqzfy3a9h1xl.cloudfront.net/nsl-prod/halifax/Annika-HEADER.jpg" alt="Halifax Tides FC Signs Annika Leslie Defender" /></p>\n
        <p>A proud Haligonian, Annika joins us from West Virginia University, where she served as captain in her senior year and a standout on the backline. She began her career with Canada’s youth national teams at just 15, debuting at the 2018 CONCACAF Championship. Annika went on to earn a bronze medal at the 2022 CONCACAF U-20 Championship in the Dominican Republic, securing Canada’s qualification for the 2022 FIFA U-20 Women’s World Cup in Costa Rica. She also represented Canada at the U-20 World Cup that same year, further showcasing her ability to compete on the world stage.</p>
        <p>As a defender, Annika has thrived in high-pressure environments. She is a Big 12 Champion and has earned numerous academic and athletic honors during her time at West Virginia University.</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Halifax Tides FC Signs Annika Leslie Defender', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('halifax-tides', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Halifax Tides FC', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-03-19', $article->published_at->format('Y-m-d'));
    }
}
