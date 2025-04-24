<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Relay\Calendar\Location;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class LocationList extends Command
{
    protected $signature = 'location:list {--format=table : Output format (table, json)}';
    protected $description = 'List all venue locations';

    public function handle(): int
    {
        $locations = Location::all();

        if ($locations->isEmpty()) {
            info('No locations found.');
            return Command::SUCCESS;
        }

        if ($this->option('format') === 'json') {
            $json = json_encode($locations->toArray(), JSON_PRETTY_PRINT);
            if ($json === false) {
                error('Failed to encode locations as JSON.');
                return Command::FAILURE;
            }
            info($json);
            return Command::SUCCESS;
        }

        table(
            ['ID', 'Venue Name', 'Address', 'Coordinates'],
            $locations->map(function (Location $location) {
                return [
                    $location->id,
                    $location->venue_name,
                    $location->address,
                    "{$location->latitude}, {$location->longitude}",
                ];
            })->all(),
        );

        return Command::SUCCESS;
    }
}
