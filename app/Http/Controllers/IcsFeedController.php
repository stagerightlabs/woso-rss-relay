<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Relay\Calendar\Calendar;
use Relay\Transform\Ics;

final class IcsFeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $calendar): Response
    {
        $calendar = Calendar::where('slug', $calendar)->first();
        if (!$calendar) {
            abort(404);
        }

        $events = $calendar->events()->with('location')->get();
        $refreshUrl = route('ics', ['calendar' => $calendar->slug]);
        $ics = new Ics($calendar, $events, $refreshUrl);

        return response((string) $ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $calendar->slug . '.ics"',
            'Cache-Control' => 'public, max-age=3600',
            'ETag' => md5((string) $ics),
            'Last-Modified' => now()->toRfc7231String(),
        ]);
    }
}
