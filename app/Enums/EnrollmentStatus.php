<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
