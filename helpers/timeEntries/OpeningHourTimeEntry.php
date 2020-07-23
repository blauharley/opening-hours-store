<?php

namespace entities;

require_once('helpers/timeEntries/AbstractTimeEntry.php');

use DateTime;
use helpers\timeEntries\AbstractTimeEntry;

class OpeningHourTimeEntry extends AbstractTimeEntry
{
    private function getMaxDayTimeBoundaryBy(int $timestamp): int
    {
        $max = -1;
        foreach ($this->getWeekdays() as $weekday) {
            foreach ($this->getTimeSpans() as $timeSpan) {
                /** @var Timespan $timeSpan */
                $timeSpanBoundary = $this->getDateTimeOf($timestamp, $weekday);
                $timeSpanBoundary->setTime($timeSpan->endHour, $timeSpan->endMinute);

                if($timeSpanBoundary->getTimestamp() > $max) {
                    $max = $timeSpanBoundary->getTimestamp();
                }
            }
        }

        return $max;
    }

    public function __construct(array $weekdays, array $timeSpans)
    {
        parent::__construct($weekdays, $timeSpans);
    }

    public function doDayBoundariesContain(int $timestamp, $skipTimeCheck = true): bool
    {
        $timestampDateTime = new DateTime();
        $timestampDateTime->setTimestamp($timestamp);

        $currWeekday = $timestampDateTime->format('w');

        $boundaryReached =  $timestampDateTime->getTimestamp() >= $this->getMaxDayTimeBoundaryBy($timestamp);
        return in_array($currWeekday, $this->getWeekdays()) && ($skipTimeCheck || (!$skipTimeCheck && !$boundaryReached));
    }

    /**
     * @return int[]
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }
}
