<?php

namespace App\Exceptions;

use RuntimeException;

class MuxVideoException extends RuntimeException
{
    public function __construct(public readonly int $httpStatus = 503, public readonly string $reason = 'service_error')
    {
        parent::__construct('The video service is temporarily unavailable. Please retry shortly.');
    }

    public function render($request)
    {
        return response()->json(['message' => $this->getMessage()], 503);
    }
}
