<?php

declare(strict_types=1);

namespace Relay\Calendar;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property CarbonImmutable $start_time
 * @property CarbonImmutable $end_time
 * @property string $location_id
 * @property string $url
 * @property ?string $external_uid
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class Event extends Model
{
    use HasUlids;

    /**
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'immutable_datetime',
        'end_time' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<Calendar, $this>
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param array<array-key, Event> $models
     * @return \Illuminate\Support\Collection<array-key, Event>
     */
    public function newCollection(array $models = [])
    {
        return new EventCollection($models);
    }
}
