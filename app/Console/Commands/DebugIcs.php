<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Sabre\VObject\Reader;
use Illuminate\Support\Collection;

class DebugIcs extends Command
{
    protected $signature = 'ics:debug
        {source : The ICS file path or URL to debug}
        {--format=table : Output format (table, json)}';

    protected $description = 'Debug an ICS file by displaying its event data';

    public function handle(): int
    {
        $source = $this->argument('source');
        $icsContent = $this->getIcsContent($source);

        if (!$icsContent) {
            $this->error("Could not read ICS content from '{$source}'");
            return Command::FAILURE;
        }

        try {
            $calendar = Reader::read($icsContent);
            /** @var Collection<int, \Sabre\VObject\Component\VEvent> $events */
            $events = collect($calendar->getComponents())->filter(function ($component) {
                return $component instanceof \Sabre\VObject\Component\VEvent;
            });

            if ($events->isEmpty()) {
                $this->info('No events found in the ICS file.');
                return Command::SUCCESS;
            }

            if ($this->option('format') === 'json') {
                $json = json_encode($events->map(function ($event) {
                    return [
                        'summary' => (string) $event->select('SUMMARY')[0],
                        'description' => (string) $event->select('DESCRIPTION')[0],
                        'start' => (string) $event->select('DTSTART')[0],
                        'end' => (string) $event->select('DTEND')[0],
                        'location' => (string) $event->select('LOCATION')[0],
                        'url' => (string) $event->select('URL')[0],
                        'uid' => (string) $event->select('UID')[0],
                        'created' => (string) $event->select('CREATED')[0],
                        'last_modified' => (string) $event->select('LAST-MODIFIED')[0],
                    ];
                })->toArray(), JSON_PRETTY_PRINT);
                if ($json === false) {
                    $this->error('Failed to encode events as JSON.');
                    return Command::FAILURE;
                }
                $this->line($json);
                return Command::SUCCESS;
            }

            $this->info('Calendar Events:');
            $this->table(
                ['Summary', 'Start', 'End', 'Location', 'URL'],
                $events->map(function ($event) {
                    return [
                        'summary' => (string) $event->select('SUMMARY')[0],
                        'start' => (string) $event->select('DTSTART')[0],
                        'end' => (string) $event->select('DTEND')[0],
                        'location' => (string) $event->select('LOCATION')[0],
                        'url' => (string) $event->select('URL')[0],
                    ];
                }),
            );

            $this->info("\nEvent Details:");
            foreach ($events as $event) {
                $this->info("\nEvent: " . $event->select('SUMMARY')[0]);
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['UID', (string) $event->select('UID')[0]],
                        ['Description', (string) $event->select('DESCRIPTION')[0]],
                        ['Created', (string) $event->select('CREATED')[0]],
                        ['Last Modified', (string) $event->select('LAST-MODIFIED')[0]],
                    ],
                );
            }

            return Command::SUCCESS;
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
