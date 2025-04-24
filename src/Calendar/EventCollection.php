<?php

declare(strict_types=1);

namespace Relay\Calendar;

use Illuminate\Database\Eloquent\Collection;

/**
 * @template TKey of array-key
 * @template TValue of Event
 * @extends Collection<TKey, TValue>
 */
class EventCollection extends Collection {}
