<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'attempts',
        'last_attempt',
        'locked_until',
    ];
}