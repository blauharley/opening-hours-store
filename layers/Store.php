<?php

namespace entities;

use exceptions\StateExceptionTimeEntry;

class Store
{
    /** @var ChargingStation[] $stations  */
    private array $stations;

    /** @var StateExceptionTimeEntry[] $exceptions  */
    private array $exceptions;

    public function __construct(array $stations, array $exceptions = [])
    {
        $this->stations = $stations;
        foreach ($this->stations as $station) {
            $station->setStore($this);
        }

        $this->exceptions = $exceptions;
    }

    public function getStationsForTenant(string $relevantTenant): array
    {
        return array_filter($this->stations, function($station) use($relevantTenant) {
            return get_class($station->getTenant()) === $relevantTenant;
        });
    }

    /**
     * @return StateExceptionTimeEntry[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
