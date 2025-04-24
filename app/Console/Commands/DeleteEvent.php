<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Relay\Calendar\Calendar;

class DeleteEvent extends Command
{
    protected $signature = 'calendar:event:delete
        {calendar : The slug of the calendar containing the event}
        {id : The ID of the event to delete}
        {--force : Skip confirmation}';

    protected $description = 'Delete an event from a calendar';

    public function handle(): int
    {
        $calendarSlug = $this->argument('calendar');
        $eventId = $this->argument('id');
        $calendar = Calendar::where('slug', $calendarSlug)->first();

        if (!$calendar) {
            $this->error("Calendar with slug '{$calendarSlug}' not found.");
            return Command::FAILURE;
        }

        $event = $calendar->events()->find($eventId);
        if (!$event) {
            $this->error("Event with ID '{$eventId}' not found in calendar '{$calendarSlug}'.");
            return Command::FAILURE;
        }

        $this->info('Event to be deleted:');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $event->id],
                ['Title', $event->title],
                ['Description', $event->description],
                ['Start Time', $event->start_time->format('Y-m-d H:i')],
                ['End Time', $event->end_time->format('Y-m-d H:i')],
                ['Location', $event->location ? $event->location->venue_name : 'None'],
                ['URL', $event->url ?: 'None'],
                ['Created', $event->created_at->format('Y-m-d H:i')],
            ],
        );

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete this event?')) {
            $this->info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        if ($event->delete()) {
            $this->info('Event deleted successfully.');
            return Command::SUCCESS;
        }

        $this->error('Failed to delete event.');
        return Command::FAILURE;
    }
}
