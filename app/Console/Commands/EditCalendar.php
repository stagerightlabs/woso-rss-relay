<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Relay\Calendar\Calendar;

class EditCalendar extends Command
{
    protected $signature = 'calendar:edit
        {slug : The slug of the calendar to edit}
        {--name= : The name of the calendar}
        {--description= : A description of the calendar}
        {--new-slug= : A new URL-friendly slug}';

    protected $description = 'Edit an existing calendar';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $calendar = Calendar::where('slug', $slug)->first();

        if (!$calendar) {
            $this->error("Calendar with slug '{$slug}' not found.");
            return Command::FAILURE;
        }

        $this->info('Current calendar details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $calendar['id']],
                ['Name', $calendar['name']],
                ['Slug', $calendar['slug']],
                ['Description', $calendar['description']],
                ['Events', count($calendar['events'])],
                ['Created', $calendar['created_at']],
            ],
        );

        $this->info("\nEnter new values (press Enter to keep current value):");

        $data = [
            'name' => $this->option('name') ?? $this->ask('Calendar name', $calendar['name']),
            'description' => $this->option('description') ?? $this->ask('Calendar description', $calendar['description']),
        ];

        // Handle new slug separately to avoid conflicts
        $newSlug = $this->option('new-slug') ?? $this->ask('New slug (optional)', $calendar['slug']);
        if ($newSlug !== $calendar['slug']) {
            if (Calendar::where('slug', $newSlug)->exists()) {
                $this->error("A calendar with slug '{$newSlug}' already exists.");
                return Command::FAILURE;
            }
            $data['slug'] = $newSlug;
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'slug' => 'sometimes|required|string|max:255|regex:/^[a-z0-9-]+$/',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        $updated = $calendar->update($data);
        $calendar = $calendar->refresh();
        if (!$updated) {
            $this->error('Failed to update calendar.');
            return Command::FAILURE;
        }

        $this->info('Calendar updated successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $calendar['id']],
                ['Name', $calendar['name']],
                ['Slug', $calendar['slug']],
                ['Description', $calendar['description']],
                ['Events', count($calendar['events'])],
                ['Updated', $calendar['updated_at']],
            ],
        );

        return Command::SUCCESS;
    }
}
