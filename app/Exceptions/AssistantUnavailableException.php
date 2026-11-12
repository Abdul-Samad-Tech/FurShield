<?php

namespace App\Exceptions;

use RuntimeException;

class AssistantUnavailableException extends RuntimeException
{
    public function __construct(public readonly ?int $status = null)
    {
        parent::__construct('Assistant unavailable');
    }
}
