<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Relay\Calendar\Calendar;

class CreateCalendar extends Command
{
    protected $signature = 'calendar:create
        {--name= : The name of the calendar}
        {--description= : A description of the calendar}
        {--slug= : The URL-friendly slug (optional)}';

    protected $description = 'Create a new calendar';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Calendar name');
        $description = $this->option('description') ?? $this->ask('Calendar description');
        $slug = $this->option('slug') ?? Str::slug($name);

        $validator = Validator::make([
            'name' => $name,
            'description' => $description,
            'slug' => $slug,
        ], [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        // Validate slug uniqueness
        if (Calendar::where('slug', $slug)->exists()) {
            $this->error("A calendar with slug '{$slug}' already exists.");
            return Command::FAILURE;
        }

        $calendar = Calendar::create([
            'name' => $name,
            'description' => $description,
            'slug' => $slug,
        ]);

        $this->info('Calendar created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $calendar['id']],
                ['Name', $calendar['name']],
                ['Slug', $calendar['slug']],
                ['Description', $calendar['description']],
                ['Created', $calendar['created_at']],
            ],
        );

        return Command::SUCCESS;
    }
}
