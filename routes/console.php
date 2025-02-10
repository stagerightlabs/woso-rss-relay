<?php

declare(strict_types=1);

use App\Console\Commands\Gather;
use App\Console\Commands\Prune;
use Illuminate\Support\Facades\Schedule;

Schedule::command(Gather::class)
    ->timezone('America/Los_Angeles')
    ->at('04:00');

Schedule::command(Prune::class)
    ->timezone('America/Los_Angeles')
    ->at('05:00');
