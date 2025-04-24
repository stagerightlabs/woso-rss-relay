<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Relay\Calendar\Location;
use Relay\Calendar\Calendar;

class EditEvent extends Command
{
    protected $signature = 'calendar:event:edit
        {calendar : The slug of the calendar containing the event}
        {id : The ID of the event to edit}
        {--title= : The title of the event}
        {--description= : A description of the event}
        {--start= : The start time (YYYY-MM-DD HH:mm)}
        {--end= : The end time (YYYY-MM-DD HH:mm)}
        {--location= : The ID of the location (optional)}
        {--url= : A URL for the event (optional)}';

    protected $description = 'Edit an existing event';

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

        $this->info('Current event details:');
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

        $this->info("\nEnter new values (press Enter to keep current value):");

        $data = [
            'title' => $this->option('title') ?? $this->ask('Event title', $event->title),
            'description' => $this->option('description') ?? $this->ask('Event description', $event->description),
            'start_time' => $this->option('start') ?? $this->ask('Start time (YYYY-MM-DD HH:mm)', $event->start_time->format('Y-m-d H:i')),
            'end_time' => $this->option('end') ?? $this->ask('End time (YYYY-MM-DD HH:mm)', $event->end_time->format('Y-m-d H:i')),
            'location_id' => $this->option('location') ?? $this->ask('Location ID (optional)', $event->location_id ?: ''),
            'url' => $this->option('url') ?? $this->ask('Event URL (optional)', $event->url ?: ''),
        ];

        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i|after:start_time',
            'location_id' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        // Validate location if provided
        if ($data['location_id'] && !Location::find($data['location_id'])) {
            $this->error("Location with ID '{$data['location_id']}' not found.");
            return Command::FAILURE;
        }

        $updated = $event->update($data);
        $event = $event->refresh();

        if (!$updated) {
            $this->error('Failed to update event.');
            return Command::FAILURE;
        }

        $this->info('Event updated successfully!');
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
                ['Updated', $event->updated_at->format('Y-m-d H:i')],
            ],
        );

        return Command::SUCCESS;
    }
}
