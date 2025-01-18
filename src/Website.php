<?php

namespace Relay;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $link
 * @property string $category
 * @property ?string $league
 * @property ?Feed $feed
 * @property ?string $rss
 * @property ?string $relay
 */
final class Website extends Model
{
    use \Sushi\Sushi;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'feed' => Feed::class,
        ];
    }

    /**
     * The relay RSS url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Website, never>
     */
    protected function relay(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->feed ? route('feed', $this->feed) : null,
        );
    }

    /**
     * The website data.
     *
     * @var array<int, array<string, \Relay\Feed::NWSL|string>>
     */
    protected array $rows = [
        [
            'name' => 'NWSL Soccer',
            'link' => 'https://www.nwslsoccer.com/',
            'category' => 'news',
            'league' => 'nwsl',
            'feed' => Feed::NWSL,
        ],
    ];
}
