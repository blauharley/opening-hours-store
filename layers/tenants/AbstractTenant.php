<?php

namespace entities\tenants;

use exceptions\StateExceptionTimeEntry;

abstract class AbstractTenant
{
    /** @var string $id */
    private string $id;

    /** @var StateExceptionTimeEntry[] $exceptions  */
    private array $exceptions;

    public function __construct(string $id, array $exceptions=[])
    {
        $this->id = $id;
        $this->exceptions = $exceptions;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return StateExceptionTimeEntry[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
