<?php

declare(strict_types=1);

namespace Relay\Transform;

use Illuminate\Support\Collection;
use Relay\Calendar\Calendar;
use Relay\Calendar\Event;
use Relay\Calendar\Location;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;

final class Ics
{
    /**
     * @param Collection<int,Event> $events
     */
    public function __construct(
        private Calendar $calendar,
        private Collection $events,
        private string $refreshUrl,
    ) {}

    /**
     * Generate the ICS file content.
     */
    public function __toString(): string
    {
        $calendar = new VCalendar([
            'VERSION' => '2.0',
            'PRODID' => '-//RSS Relay Service//NONSGML v1.0//EN',
            'CALSCALE' => 'GREGORIAN',
            'METHOD' => 'PUBLISH',
            'X-WR-CALNAME' => $this->calendar->name,
            'X-WR-TIMEZONE' => 'UTC',
            'REFRESH-INTERVAL;VALUE=DURATION' => 'P1D',
            'X-PUBLISHED-TTL' => 'P1D',
            'URL' => $this->refreshUrl,
        ]);

        // Add events
        foreach ($this->events as $event) {
            $this->addEvent($calendar, $event);
        }

        return $calendar->serialize();
    }

    /**
     * Add an event to the calendar.
     */
    private function addEvent(VCalendar $calendar, Event $event): void
    {
        // Convert times to UTC
        $start = $event->start_time->setTimezone('UTC');
        $end = $event->end_time->setTimezone('UTC');
        $lastModified = $event->updated_at->setTimezone('UTC');

        $vEvent = new VEvent($calendar, 'VEVENT', [
            'DTSTART' => $start->format('Ymd\THis\Z'),
            'DTEND' => $end->format('Ymd\THis\Z'),
            'DTSTAMP' => $start->format('Ymd\THis\Z'),
            'LAST-MODIFIED' => $lastModified->format('Ymd\THis\Z'),
            'UID' => $event->external_uid ?? "urn:relay:{$this->calendar->slug}:{$event->id}",
            'SUMMARY' => $event->title,
            'DESCRIPTION' => $event->description,
            'URL' => $event->url,
            'SEQUENCE' => '0',
        ]);

        // Add location if available
        if ($event->location instanceof Location) {
            $location = $this->formatLocation($event->location);
            if ($location) {
                $vEvent->add('LOCATION', $location);
            }
        }
    }

    /**
     * Format location data according to ICS specification.
     */
    private function formatLocation(Location $location): ?string
    {
        $parts = [];

        if (!empty($location->venue_name)) {
            $parts[] = $location->venue_name;
        }

        if (!empty($location->address)) {
            $parts[] = $location->address;
        }

        if (!empty($location->latitude) && !empty($location->longitude)) {
            $parts[] = "({$location->latitude},{$location->longitude})";
        }

        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
