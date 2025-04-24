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

class LocationAdd extends Command
{
    protected $signature = 'location:add
        {--name= : The name of the venue}
        {--address= : The full address}
        {--lat= : The latitude}
        {--lng= : The longitude}';

    protected $description = 'Add a new venue location';

    public function handle(): int
    {
        $name = $this->option('name') ?? text('Venue name');
        $address = $this->option('address') ?? text('Address');
        $lat = $this->option('lat') ?? text('Latitude');
        $lng = $this->option('lng') ?? text('Longitude');

        $data = [
            'venue_name' => $name,
            'address' => $address,
            'latitude' => $lat,
            'longitude' => $lng,
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

        $location = Location::create($data);

        info('Location added successfully!');
        table(
            ['Field', 'Value'],
            [
                ['Venue Name', $location->venue_name],
                ['Address', $location->address],
                ['Coordinates', "{$location->latitude}, {$location->longitude}"],
            ],
        );

        return Command::SUCCESS;
    }
}
