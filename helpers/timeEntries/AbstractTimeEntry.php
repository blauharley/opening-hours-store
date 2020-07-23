<?php

namespace helpers\timeEntries;

use DateTime;
use entities\Timespan;

abstract class AbstractTimeEntry
{
    /** @var int[] $weekday */
    protected array $weekdays;

    /** @var Timespan[] $timeSpans */
    protected array $timeSpans;

    /**
     * @param int $timestamp
     * @param int $weekday
     * @return DateTime
     */
    protected function getDateTimeOf(int $timestamp, int $weekday): DateTime
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        $dateTime->setISODate(
            $dateTime->format('o'),
            $dateTime->format('W'),
            $weekday
        );

        return $dateTime;
    }

    public function __construct(array $weekdays, array $timeSpans)
    {
        $this->weekdays = $weekdays;
        $this->timeSpans = $timeSpans;
    }

    /**
     * Checks whether DateTime falls basically into of what day an AbstractTimeEntry instance holds
     *
     * @param int $timestamp
     * @param bool $skipTimeCheck
     * @return bool
     */
    abstract public function doDayBoundariesContain(int $timestamp, $skipTimeCheck = true): bool;

    /**
     * Can return a days as numbers beginning with Sunday (0) or as human readable numbers in a month
     *
     * @return int[]
     */
    abstract public function getWeekdays(): array;

    /**
     * @return int[]
     */
    public function convertIntoTimestampsBy(int $requestedTimestamp): array
    {
        $timestamps = [];

        foreach ($this->getWeekdays() as $weekday) {
            foreach ($this->getTimeSpans() as $timespan) {
                /** @var Timespan $timespan */
                $dateTime = $this->getDateTimeOf($requestedTimestamp, $weekday);

                $timestamps[] = $dateTime->setTime($timespan->startHour, $timespan->startMinute)->getTimestamp();
                $timestamps[] = $dateTime->setTime($timespan->endHour, $timespan->endMinute)->getTimestamp();
            }
        }

        return $timestamps;
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function checkTimestamp(int $timestamp): bool
    {
        if ($this->doDayBoundariesContain($timestamp)) {
            foreach ($this->getTimeSpans() as $timespan) {
                $dateTime = new DateTime();
                $dateTime->setTimestamp($timestamp);

                /** @var Timespan $timespan */
                $start = $dateTime->setTime($timespan->startHour, $timespan->startMinute)->getTimestamp();
                $end = $dateTime->setTime($timespan->endHour, $timespan->endMinute)->getTimestamp();

                if ($timestamp >= $start && $timestamp < $end) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return Timespan[]
     */
    public function getTimeSpans(): array
    {
        return $this->timeSpans;
    }
}
