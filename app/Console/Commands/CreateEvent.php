<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Relay\Calendar\Calendar;
use Relay\Calendar\Location;

class CreateEvent extends Command
{
    protected $signature = 'calendar:event:create
        {calendar : The slug of the calendar to add the event to}
        {--title= : The title of the event}
        {--description= : A description of the event}
        {--start= : The start time (YYYY-MM-DD HH:mm)}
        {--end= : The end time (YYYY-MM-DD HH:mm)}
        {--location= : The ID of the location (optional)}
        {--url= : A URL for the event (optional)}';

    protected $description = 'Create a new event in a calendar';

    public function handle(): int
    {
        $calendarSlug = $this->argument('calendar');
        $calendar = Calendar::where('slug', $calendarSlug)->first();

        if (!$calendar) {
            $this->error("Calendar with slug '{$calendarSlug}' not found.");
            return Command::FAILURE;
        }

        $title = $this->option('title') ?? $this->ask('Event title');
        $description = $this->option('description') ?? $this->ask('Event description');
        $start = $this->option('start') ?? $this->ask('Start time (YYYY-MM-DD HH:mm)');
        $end = $this->option('end') ?? $this->ask('End time (YYYY-MM-DD HH:mm)');
        $locationId = $this->option('location') ?? $this->ask('Location ID (optional)', '');
        $url = $this->option('url') ?? $this->ask('Event URL (optional)', '');

        $validator = Validator::make([
            'title' => $title,
            'description' => $description,
            'start_time' => $start,
            'end_time' => $end,
            'location_id' => $locationId,
            'url' => $url,
        ], [
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
        if ($locationId && !Location::find($locationId)) {
            $this->error("Location with ID '{$locationId}' not found.");
            return Command::FAILURE;
        }

        $event = $calendar->events()->create([
            'title' => $title,
            'description' => $description,
            'start_time' => $start,
            'end_time' => $end,
            'location_id' => $locationId ?: null,
            'url' => $url ?: null,
        ]);

        $this->info('Event created successfully!');
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

        return Command::SUCCESS;
    }
}
