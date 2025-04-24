<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Relay\Calendar\Location;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class LocationEdit extends Command
{
    protected $signature = 'location:edit
        {id : The ID of the location to edit}
        {--name= : The name of the venue}
        {--address= : The full address}
        {--lat= : The latitude}
        {--lng= : The longitude}';

    protected $description = 'Edit an existing venue location';

    public function handle(): int
    {
        $id = $this->argument('id');
        $location = Location::find($id);

        if (!$location) {
            error("Location with ID {$id} not found.");
            return Command::FAILURE;
        }

        info('Current location details:');
        table(
            ['Field', 'Value'],
            [
                ['Venue Name', $location['venue_name']],
                ['Address', $location['address']],
                ['Coordinates', "{$location['latitude']}, {$location['longitude']}"],
            ],
        );

        info("\nEnter new values (press Enter to keep current value):");

        $data = [
            'venue_name' => $this->option('name') ?? text('Venue name', $location['venue_name']),
            'address' => $this->option('address') ?? text('Address', $location['address']),
            'latitude' => $this->option('lat') ?? text('Latitude', (string) $location['latitude']),
            'longitude' => $this->option('lng') ?? text('Longitude', (string) $location['longitude']),
        ];

        $validator = Validator::make($data, [
            'venue_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                error($error);
            }
            return Command::FAILURE;
        }

        $location->update($data);
        $location->refresh();

        info('Location updated successfully!');
        table(
            ['Field', 'Value'],
            [
                ['Venue Name', $location['venue_name']],
                ['Address', $location['address']],
                ['Coordinates', "{$location['latitude']}, {$location['longitude']}"],
            ],
        );

        return Command::SUCCESS;
    }
}
