<?php

namespace helpers;

use RuntimeException;

class StateException extends RuntimeException
{
    private bool $state;

    private string $description;

    public function __construct(bool $state, string $description)
    {
        $this->state = $state;
        $this->description = $description;

        $message = sprintf('State "%s" because "%s"', $this->state ? 'open' : 'closed', $this->description);
        parent::__construct($message, 0, null);
    }
}
