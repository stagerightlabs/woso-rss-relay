<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Sabre\VObject\Reader;
use Relay\Calendar\Calendar;

class ImportIcs extends Command
{
    protected $signature = 'calendar:import-ics
        {calendar : The slug of the calendar to import events into}
        {source : The ICS file path or URL to import from}
        {--dry-run : Show what would be imported without actually importing}
        {--skip-existing : Skip events that already exist (based on external_uid)}';

    protected $description = 'Import events from an ICS file into a calendar';

    public function handle(): int
    {
        $calendarSlug = $this->argument('calendar');
        $source = $this->argument('source');
        $isDryRun = $this->option('dry-run');
        $skipExisting = $this->option('skip-existing');

        $calendar = Calendar::where('slug', $calendarSlug)->first();
        if (!$calendar) {
            $this->error("Calendar with slug '{$calendarSlug}' not found.");
            return Command::FAILURE;
        }

        $icsContent = $this->getIcsContent($source);
        if (!$icsContent) {
            $this->error("Could not read ICS content from '{$source}'");
            return Command::FAILURE;
        }

        try {
            $vcalendar = Reader::read($icsContent);
            /** @var Collection<int, \Sabre\VObject\Component\VEvent> $events */
            $events = collect($vcalendar->getComponents());

            if ($events->isEmpty()) {
                $this->info('No events found in the ICS file.');
                return Command::SUCCESS;
            }

            $this->info("Found {$events->count()} events to import.");
            $imported = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($events as $event) {
                $externalUid = (string) $event->select('UID')[0];
                $title = (string) $event->select('SUMMARY')[0];
                $description = (string) $event->select('DESCRIPTION')[0];
                $start = (string) $event->select('DTSTART')[0];
                $end = (string) $event->select('DTEND')[0];
                $location = (string) $event->select('LOCATION')[0];
                $url = (string) $event->select('URL')[0];

                // Check if event already exists
                if ($skipExisting && $calendar->events()->where('external_uid', $externalUid)->exists()) {
                    $this->line("Skipping existing event: {$title}");
                    $skipped++;
                    continue;
                }

                if ($isDryRun) {
                    $this->line("Would import event: {$title}");
                    $imported++;
                    continue;
                }

                try {
                    $calendar->events()->create([
                        'title' => $title,
                        'description' => $description,
                        'start_time' => $start,
                        'end_time' => $end,
                        'location' => $location,
                        'url' => $url,
                        'external_uid' => $externalUid,
                    ]);
                    $this->line("Imported event: {$title}");
                    $imported++;
                } catch (\Exception $e) {
                    $this->error("Failed to import event {$title}: {$e->getMessage()}");
                    $failed++;
                }
            }

            $this->newLine();
            $this->info('Import Summary:');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Imported', $imported],
                    ['Skipped', $skipped],
                    ['Failed', $failed],
                ],
            );

            return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error parsing ICS file: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function getIcsContent(string $source): ?string
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::get($source);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Exception $e) {
                $this->error("Error fetching URL: {$e->getMessage()}");
                return null;
            }
        }

        if (file_exists($source)) {
            $content = file_get_contents($source);
            return $content === false ? null : $content;
        }

        return null;
    }
}
