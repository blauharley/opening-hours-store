<?php

namespace entities;

class Timespan
{
    /** @var int $startHour */
    public int $startHour;

    /** @var int $endHour */
    public int $endHour;

    /** @var int $startMinute */
    public int $startMinute;

    /** @var int $endMinute */
    public int $endMinute;

    public function __construct(int $startHour, int $startMinute, int $endHour, int $endMinute)
    {
        $this->startHour = $startHour;
        $this->endHour = $endHour;
        $this->startMinute = $startMinute;
        $this->endMinute = $endMinute;
    }
}
