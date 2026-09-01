<?php

namespace App\Enums;

enum EnrollmentSourceType: string
{
    case OneTime = 'one_time';
    case Subscription = 'subscription';
    case Free = 'free';
    case Admin = 'admin';
    case Order = 'order';
}
