<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Relay\Calendar\Calendar;

class ListCalendars extends Command
{
    protected $signature = 'calendar:list {--format=table : Output format (table, json)}';
    protected $description = 'List all calendars';

    public function handle(): int
    {
        $calendars = Calendar::all();

        if ($calendars->isEmpty()) {
            $this->info('No calendars found.');
            return Command::SUCCESS;
        }

        if ($this->option('format') === 'json') {
            $json = json_encode($calendars->toArray(), JSON_PRETTY_PRINT);
            if ($json === false) {
                $this->error('Failed to encode calendars as JSON.');
                return Command::FAILURE;
            }
            $this->line($json);
            return Command::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Slug', 'Events', 'Created'],
            $calendars->map(function ($calendar) {
                return [
                    'id' => $calendar['id'],
                    'name' => $calendar['name'],
                    'slug' => $calendar['slug'],
                    'events' => count($calendar['events']),
                    'created' => $calendar['created_at'],
                ];
            }),
        );

        return Command::SUCCESS;
    }
}
