<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
#[Fillable(['name', 'email', 'phone', 'message', 'subject', 'status', 'read_at', 'locale'])]
class Contact extends Model
{
    use Notifiable;
}
