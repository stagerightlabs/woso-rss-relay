<?php

declare(strict_types=1);

namespace Tests\Relay\Parsing;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Relay\Article;
use Relay\Parsing\UnitedStates;
use Tests\TestCase;

class UnitedStatesTest extends TestCase
{
    #[Test]
    public function it_can_parse_uswnt_stories()
    {
        Http::fake(['*' => Http::response($this->stub('united-states.html'))]);
        $parser = new UnitedStates();
        $response = Http::get($parser->target());
        $entries = $parser->entries($response);

        $this->assertCount(24, $entries);
        $this->assertEquals('https://www.ussoccer.com/stories/2025/08/uswnt-sam-coffey-portland-thorns-ally-sentnor-debuts-kansas-city-current-nwsl', $entries->first()->url);
        $this->assertEquals('uswnt-sam-coffey-portland-thorns-ally-sentnor-debuts-kansas-city-current-nwsl', $entries->first()->key);
    }

    #[Test]
    public function it_can_parse_a_uswnt_article()
    {
        Http::fake(['*' => Http::response($this->stub('united-states-article.html'))]);
        $parser = new UnitedStates();
        $response = Http::get('example.com');
        $article = $parser->article($response, [
            'key' => 'this-is-a-key',
            'url' => 'http://example.com/path/to/article',
        ]);
        $expected = <<<HTML
        <p><img src="https://cdn.sanity.io/images/oyf3dba6/production/1d5bf22ec7a774de09d8d87476c0a5cd9367f853-800x450.png?w=1062&fit=max&auto=format" alt="USWNT Rewind: Sam Coffey Finds the Net, Ally Sentnor Debuts for Kansas City Current" /></p>\n
        <p>The NWSL season rolls on as Matchday 15 is in the books. More than halfway through the 2025 regular season, the race to the playoffs heats up. U.S. Women’s National Team players were, as always, right in the thick of it.</p>
        <p>Meanwhile, the European leagues are set to begin later this fall, with club friendlies already kicking off.</p>
        <p>Let’s rewind some of the USWNT's top moments at their respective clubs from this weekend:</p>\n
        HTML;

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('USWNT Rewind: Sam Coffey Finds the Net, Ally Sentnor Debuts for Kansas City Current', $article->title);
        $this->assertEquals('this-is-a-key', $article->key);
        $this->assertEquals('us-soccer', $article->site);
        $this->assertEquals($expected, $article->summary);
        $this->assertEquals('US Soccer', $article->author);
        $this->assertEquals('http://example.com/path/to/article', $article->link);
        $this->assertEquals('2025-08-11', $article->published_at->format('Y-m-d'));
    }
}
