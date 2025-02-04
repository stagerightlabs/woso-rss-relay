<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('gather')
    ->dailyAt('04:00')
    ->timezone('America/Los_Angeles');

Schedule::command('prune')
    ->dailyAt('05:00')
    ->timezone('America/Los_Angeles');
