<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Relay\Calendar\Location;
use Tests\TestCase;

class LocationCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_location(): void
    {
        $this->artisan('location:add')
            ->expectsQuestion('Venue name', 'Test Venue')
            ->expectsQuestion('Address', '123 Test St, Test City, Test State 12345, Test Country')
            ->expectsQuestion('Latitude', '40.7128')
            ->expectsQuestion('Longitude', '-74.0060')
            ->assertSuccessful();

        $this->assertDatabaseHas('locations', [
            'venue_name' => 'Test Venue',
            'address' => '123 Test St, Test City, Test State 12345, Test Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);
    }

    public function test_can_add_location_with_options(): void
    {
        $this->artisan('location:add', [
            '--name' => 'Test Venue',
            '--address' => '123 Test St, Test City, Test State 12345, Test Country',
            '--lat' => '40.7128',
            '--lng' => '-74.0060',
        ])->assertSuccessful();

        $this->assertDatabaseHas('locations', [
            'venue_name' => 'Test Venue',
            'address' => '123 Test St, Test City, Test State 12345, Test Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);
    }

    public function test_can_edit_location(): void
    {
        $location = Location::factory()->create([
            'venue_name' => 'Original Venue',
            'address' => '123 Original St, Original City, Original State 12345, Original Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $this->artisan('location:edit', ['id' => $location->id])
            ->expectsQuestion('Venue name', 'Updated Venue')
            ->expectsQuestion('Address', '456 Updated St, Updated City, Updated State 54321, Updated Country')
            ->expectsQuestion('Latitude', '41.7128')
            ->expectsQuestion('Longitude', '-75.0060')
            ->assertSuccessful();

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'venue_name' => 'Updated Venue',
            'address' => '456 Updated St, Updated City, Updated State 54321, Updated Country',
            'latitude' => 41.7128,
            'longitude' => -75.0060,
        ]);
    }

    public function test_can_edit_location_with_options(): void
    {
        $location = Location::factory()->create([
            'venue_name' => 'Original Venue',
            'address' => '123 Original St, Original City, Original State 12345, Original Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $this->artisan('location:edit', [
            'id' => $location->id,
            '--name' => 'Updated Venue',
            '--address' => '456 Updated St, Updated City, Updated State 54321, Updated Country',
            '--lat' => '41.7128',
            '--lng' => '-75.0060',
        ])->assertSuccessful();

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'venue_name' => 'Updated Venue',
            'address' => '456 Updated St, Updated City, Updated State 54321, Updated Country',
            'latitude' => 41.7128,
            'longitude' => -75.0060,
        ]);
    }

    public function test_edit_fails_with_invalid_id(): void
    {
        $this->artisan('location:edit', ['id' => 'invalid-id'])
            ->expectsOutputToContain('Location with ID invalid-id not found.')
            ->assertFailed();
    }

    public function test_add_fails_with_invalid_data(): void
    {
        $this->artisan('location:add')
            ->expectsQuestion('Venue name', '')
            ->expectsQuestion('Address', '')
            ->expectsQuestion('Latitude', 'invalid')
            ->expectsQuestion('Longitude', 'invalid')
            ->assertFailed();
    }

    public function test_can_delete_location(): void
    {
        $location = Location::factory()->create([
            'venue_name' => 'Test Venue',
            'address' => '123 Test St, Test City, Test State 12345, Test Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $this->artisan('location:delete', ['id' => $location->id])
            ->expectsConfirmation('Are you sure you want to delete this location?', 'yes')
            ->expectsOutputToContain('Location deleted successfully.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_can_delete_location_with_force_option(): void
    {
        $location = Location::factory()->create([
            'venue_name' => 'Test Venue',
            'address' => '123 Test St, Test City, Test State 12345, Test Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $this->artisan('location:delete', [
            'id' => $location->id,
            '--force' => true,
        ])
            ->expectsOutputToContain('Location deleted successfully.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_delete_fails_with_invalid_id(): void
    {
        $this->artisan('location:delete', ['id' => 'invalid-id'])
            ->expectsOutputToContain('Location with ID invalid-id not found.')
            ->assertFailed();
    }

    public function test_delete_can_be_cancelled(): void
    {
        $location = Location::factory()->create([
            'venue_name' => 'Test Venue',
            'address' => '123 Test St, Test City, Test State 12345, Test Country',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $this->artisan('location:delete', ['id' => $location->id])
            ->expectsConfirmation('Are you sure you want to delete this location?', 'no')
            ->expectsOutputToContain('Deletion cancelled.')
            ->assertSuccessful();

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }
}
