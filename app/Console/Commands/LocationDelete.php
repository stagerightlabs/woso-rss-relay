<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Relay\Calendar\Location;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\confirm;

class LocationDelete extends Command
{
    protected $signature = 'location:delete
        {id : The ID of the location to delete}
        {--force : Skip confirmation}';

    protected $description = 'Delete a venue location';

    public function handle(): int
    {
        $id = $this->argument('id');
        $location = Location::find($id);

        if (!$location) {
            error("Location with ID {$id} not found.");
            return Command::FAILURE;
        }

        info('Location to be deleted:');
        table(
            ['Field', 'Value'],
            [
                ['Venue Name', $location->venue_name],
                ['Address', $location->address],
                ['Coordinates', "{$location->latitude}, {$location->longitude}"],
            ],
        );

        if (!$this->option('force') && !confirm('Are you sure you want to delete this location?')) {
            info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        if ($location->delete()) {
            info('Location deleted successfully.');
            return Command::SUCCESS;
        }

        error('Failed to delete location.');
        return Command::FAILURE;
    }
}
