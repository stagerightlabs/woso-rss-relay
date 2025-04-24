<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Relay\Calendar\Calendar;
use Relay\Calendar\Event;
use Relay\Calendar\Location;

class ListEvents extends Command
{
    protected $signature = 'calendar:event:list
        {calendar : The slug of the calendar to list events from}
        {--format=table : Output format (table, json)}
        {--start= : Filter events after this date (YYYY-MM-DD)}
        {--end= : Filter events before this date (YYYY-MM-DD)}
        {--location= : Filter events by location ID}
        {--search= : Search events by title or description}
        {--upcoming : Show only upcoming events}
        {--past : Show only past events}
        {--sort=start_time : Sort field (start_time, end_time, title, created_at, updated_at, location_id, url)}
        {--order=asc : Sort order (asc, desc)}
        {--sort2= : Secondary sort field (same options as --sort)}
        {--order2=asc : Secondary sort order (asc, desc)}
        {--sort3= : Tertiary sort field (same options as --sort)}
        {--order3=asc : Tertiary sort order (asc, desc)}';

    protected $description = 'List events in a calendar';

    /**
     * @var \Illuminate\Support\Collection<int, Event>
     */
    protected \Illuminate\Support\Collection $events;

    public function handle(): int
    {
        $calendarSlug = $this->argument('calendar');
        $calendar = Calendar::where('slug', $calendarSlug)->first();

        if (!$calendar) {
            $this->error("Calendar with slug '{$calendarSlug}' not found.");
            return Command::FAILURE;
        }

        $query = $calendar->events()->with('location');

        // Apply date filters
        if ($startDate = $this->option('start')) {
            $query->where('start_time', '>=', $startDate);
        }
        if ($endDate = $this->option('end')) {
            $query->where('end_time', '<=', $endDate);
        }

        // Apply location filter
        if ($locationId = $this->option('location')) {
            if (!Location::find($locationId)) {
                $this->error("Location with ID '{$locationId}' not found.");
                return Command::FAILURE;
            }
            $query->where('location_id', $locationId);
        }

        // Apply search filter
        if ($search = $this->option('search')) {
            $search = strtolower($search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $search . '%']);
            });
        }

        // Apply time-based filters
        $now = Carbon::now();
        if ($this->option('upcoming')) {
            $query->where('start_time', '>=', $now);
        }
        if ($this->option('past')) {
            $query->where('end_time', '<', $now);
        }

        // Apply sorting
        $query = $this->applySorting($query);

        $this->events = $query->get();

        if ($this->events->isEmpty()) {
            $this->info('No events found.');
            return Command::SUCCESS;
        }

        if ($this->option('format') === 'json') {
            $json = json_encode($this->events->toArray(), JSON_PRETTY_PRINT);
            if ($json === false) {
                $this->error('Failed to encode events as JSON.');
                return Command::FAILURE;
            }
            $this->line($json);
            return Command::SUCCESS;
        }

        $eventList = $this->events->reduce(function (array $carry, Event $event, int $index): array {
            $carry[] = [
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time->format('Y-m-d H:i'),
                'end_time' => $event->end_time->format('Y-m-d H:i'),
                'location' => $event->location ? $event->location->venue_name : 'None',
                'url' => $event->url ?: 'None',
                'created' => $event->created_at->format('Y-m-d H:i'),
                'updated' => $event->updated_at->format('Y-m-d H:i'),
            ];
            return $carry;
        }, []);

        $this->table(
            ['ID', 'Title', 'Start Time', 'End Time', 'Location', 'URL', 'Created', 'Updated'],
            $eventList,
        );

        return Command::SUCCESS;
    }

    /**
     * @param HasMany<Event, Calendar> $query
     * @return HasMany<Event, Calendar>
     */
    private function applySorting(HasMany $query): HasMany
    {
        $sortFields = [
            ['field' => $this->option('sort'), 'order' => $this->option('order')],
            ['field' => $this->option('sort2'), 'order' => $this->option('order2')],
            ['field' => $this->option('sort3'), 'order' => $this->option('order3')],
        ];

        foreach ($sortFields as $sort) {
            if ($sort['field'] && $sort['order']) {
                $query->orderBy($sort['field'], $sort['order']);
            }
        }

        return $query;
    }
}
