<?php

namespace exceptions;

require_once('helpers/timeEntries/AbstractTimeEntry.php');
require_once('helpers/StateException.php');

use DateTime;
use helpers\StateException;
use helpers\timeEntries\AbstractTimeEntry;

class StateExceptionTimeEntry extends AbstractTimeEntry
{
    private int $year;

    private int $month;

    private int $day;

    private StateException $stateException;

    public function __construct(int $year, int $month, int $day, StateException $stateException, array $timeSpans)
    {
        parent::__construct([], $timeSpans);

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->stateException = $stateException;
    }

    public function doDayBoundariesContain(int $timestamp, $skipTimeCheck = true): bool
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);

        return
            +$dateTime->format('Y') === $this->year &&
            +$dateTime->format('m') === $this->month &&
            in_array($dateTime->format('d'), $this->getWeekdays());
    }

    /**
     * @return int[]
     */
    public function getWeekdays(): array
    {
        return [$this->day];
    }

    public function throwException(): void
    {
        throw $this->stateException;
    }
}
