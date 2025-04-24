<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Relay\Calendar\Location;
use Symfony\Component\Uid\Ulid;

class LocationFromMapUrl extends Command
{
    protected $signature = 'location:url {url : The Google Maps URL}';

    protected $description = 'Create a new location from a Google Maps URL';

    public function handle(): int
    {
        $url = $this->argument('url');

        // Parse the URL
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            $this->error('Invalid URL provided');
            return Command::FAILURE;
        }

        // Extract coordinates or place ID from URL
        $params = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $params);
        }

        // Convert params to string values and ensure string keys
        $stringParams = [];
        foreach ($params as $key => $value) {
            $stringKey = (string) $key;
            if (is_array($value)) {
                $stringParams[$stringKey] = implode(',', $value);
            } else {
                $stringParams[$stringKey] = (string) $value;
            }
        }

        // Try to extract coordinates
        $coordinates = $this->extractCoordinates($url, $stringParams);
        if (!$coordinates) {
            $this->error('Could not extract location information from URL');
            return Command::FAILURE;
        }

        // Ask for venue name since it's not always possible to extract it reliably from URL
        $venueName = $this->ask('Please enter the venue name');
        if (!$venueName) {
            $this->error('Venue name is required');
            return Command::FAILURE;
        }

        // Create the location
        $location = Location::create([
            'id' => (string) new Ulid(),
            'venue_name' => $venueName,
            'address' => $coordinates['formatted_address'] ?? 'Unknown address',
            'latitude' => $coordinates['lat'],
            'longitude' => $coordinates['lng'],
        ]);

        $this->info('Location created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Venue Name', $location->venue_name],
                ['Address', $location->address],
                ['Coordinates', "{$location->latitude}, {$location->longitude}"],
            ],
        );

        return Command::SUCCESS;
    }

    /**
     * Extract coordinates from various Google Maps URL formats
     *
     * @param string $url
     * @param array<string, string> $params
     * @return array{lat: float, lng: float, formatted_address?: string}|null
     */
    private function extractCoordinates(string $url, array $params): ?array
    {
        // Format 1: Direct coordinates in URL
        // Example: https://www.google.com/maps?q=40.7128,-74.0060
        if (isset($params['q']) && preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/', $params['q'], $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        // Format 2: Coordinates in @format
        // Example: https://www.google.com/maps/@40.7128,-74.0060,15z
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*),/', $url, $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        // Format 3: Place coordinates in URL
        // Example: https://www.google.com/maps/place/New+York,+NY/@40.7128,-74.0060,15z
        if (preg_match('/place\/[^@]*@(-?\d+\.?\d*),(-?\d+\.?\d*),/', $url, $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        return null;
    }
}
