<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Relay\Calendar\Calendar;

class DeleteCalendar extends Command
{
    protected $signature = 'calendar:delete
        {slug : The slug of the calendar to delete}
        {--force : Skip confirmation}';

    protected $description = 'Delete a calendar';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $calendar = Calendar::where('slug', $slug)->first();

        if (!$calendar) {
            $this->error("Calendar with slug '{$slug}' not found.");
            return Command::FAILURE;
        }

        $this->info('Calendar to be deleted:');
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

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete this calendar? This will also delete all its events.')) {
            $this->info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        if ($calendar->delete()) {
            $this->info('Calendar deleted successfully.');
            return Command::SUCCESS;
        }

        $this->error('Failed to delete calendar.');
        return Command::FAILURE;
    }
}
