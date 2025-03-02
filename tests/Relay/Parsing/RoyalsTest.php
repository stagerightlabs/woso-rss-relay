<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\Royals;
use Tests\TestCase;

class RoyalsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_parse_the_royals_index()
    {
        Http::fake(['*' => Http::response($this->stub('royals.html'))]);
        $parser = new Royals();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(24, $entries);
        $this->assertEquals('https://www.rsl.com/utahroyals/news/urfc-acquires-international-spot-in-trade-with-kc-current', $entries->first()->url);
        $this->assertEquals('urfc-acquires-international-spot-in-trade-with-kc-current', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_royals_article()
    {
        Http::fake(['*' => Http::response($this->stub('royals-article.html'))]);
        $parser = new Royals();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://images.mlssoccer.com/image/private/t_editorial_landscape_8_desktop_mobile/f_png/mls-rsl/hotkjfhpqbaiv3k7apzg.png" alt="URFC Acquires International Spot In Trade With KC Current" /></p>\n
        <p>HERRIMAN, Utah (Friday Feb 28, 2025) - Utah Royals FC has acquired a 2025 international spot from Kansas City Current, the clubs announce today. In exchange URFC sends $50,000 of allocation money to Kansas City.</p>
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('URFC Acquires International Spot In Trade With KC Current', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('utah-royals', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('Utah Royals', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-02-28', $article->published_at->format('Y-m-d'));
    }
}
