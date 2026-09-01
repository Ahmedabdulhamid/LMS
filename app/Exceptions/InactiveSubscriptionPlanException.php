<?php

namespace App\Exceptions;

use DomainException;

class InactiveSubscriptionPlanException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Inactive subscription plans cannot be selected for purchase.');
    }
}
