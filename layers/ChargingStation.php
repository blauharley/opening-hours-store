<?php

namespace entities;

require_once('layers/tenants/AbstractTenant.php');
require_once('layers/Store.php');

use entities\tenants\AbstractTenant;
use exceptions\StateExceptionTimeEntry;
use DateTime;
use DateInterval;

class ChargingStation
{
    /** @var AbstractTenant $relevantTenant */
    private AbstractTenant $relevantTenant;

    /** @var Store $store */
    private Store $store;

    /** @var OpeningHourTimeEntry[] $openingHours */
    private array $openingHours;

    /** @var StateExceptionTimeEntry[] $exceptions  */
    private array $exceptions;

    /**
     * @return array
     */
    private function getOrderedTimeEntries(): array
    {
        $stationExceptions = $this->exceptions;
        $storeExceptions = $this->store->getExceptions();
        $tenantExceptions = $this->getTenant()->getExceptions();

        return array_merge($stationExceptions, $storeExceptions, $tenantExceptions, $this->openingHours);
    }

    /**
     * @param int $requestedTimestamp
     * @return bool
     * @throws StateExceptionTimeEntry
     */
    private function checkTimeEntriesBy(int $requestedTimestamp): bool
    {
        foreach ($this->getOrderedTimeEntries() as $entry) {
            /** @var OpeningHourTimeEntry|StateExceptionTimeEntry $entry */
            if($entry->checkTimestamp($requestedTimestamp)){
                if($entry instanceof StateExceptionTimeEntry) {
                    $entry->throwException();
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $requestedTimestamp
     * @return array
     */
    private function getSortedTimestampBoundariesOfTimeEntriesBy(int $requestedTimestamp): array
    {
        $timestamps = [];

        foreach ($this->getOrderedTimeEntries() as $entry) {
            /** @var OpeningHourTimeEntry $entry */
            $timestamps = array_merge($timestamps, $entry->convertIntoTimestampsBy($requestedTimestamp));
        }

        sort($timestamps);

        return $timestamps;
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    private function isTimestampInTimeEntries(int $timestamp): bool
    {
        foreach ($this->getOrderedTimeEntries() as $entry) {
            /** @var OpeningHourTimeEntry $entry */
            if ($entry->doDayBoundariesContain($timestamp, false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ChargingStation constructor.
     * @param AbstractTenant $relevantTenant
     * @param array $openingHours
     * @param array $exceptions
     */
    public function __construct(AbstractTenant $relevantTenant, array $openingHours, array $exceptions=[])
    {
        $this->relevantTenant = $relevantTenant;
        $this->openingHours = $openingHours;
        $this->exceptions = $exceptions;
    }

    public function getTenant(): AbstractTenant
    {
        return $this->relevantTenant;
    }

    /**
     * @param Store $store
     */
    public function setStore(Store $store): void
    {
        $this->store = $store;
    }

    /**
     * RELEVANT IMPLEMENTATION 1
     *
     * @param int $requestedTimestamp
     * @return bool
     * @throws StateExceptionTimeEntry
     */
    public function isOpenAt(int $requestedTimestamp): bool
    {
        return $this->checkTimeEntriesBy($requestedTimestamp);
    }

    /**
     * RELEVANT IMPLEMENTATION 2
     *
     * @param int $requestedTimestamp
     * @return int
     * @throws StateExceptionTimeEntry
     */
    public function getTimestampOfNextStateChangeStartingAt(int $requestedTimestamp): int
    {
        $this->checkTimeEntriesBy($requestedTimestamp);

        if(!$this->isTimestampInTimeEntries($requestedTimestamp)) {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($requestedTimestamp);
            $dateTime->add(new DateInterval('P1D'));
            $dateTime->setTime(0, 0);
            $requestedTimestamp = $dateTime->getTimestamp();

            return $this->getTimestampOfNextStateChangeStartingAt($requestedTimestamp);
        }

        $timestamps = $this->getSortedTimestampBoundariesOfTimeEntriesBy($requestedTimestamp);

        for ($index = 0; $index < count($timestamps) - 1; $index++) {
            $startTimestamp = $timestamps[$index];
            $endTimestamp = $timestamps[$index + 1];

            if($requestedTimestamp >= $startTimestamp && $requestedTimestamp < $endTimestamp) {
                return $endTimestamp;
            }
        }

        return $timestamps[0];
    }
}
