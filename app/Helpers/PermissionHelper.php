<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    public static function check($module, $action)
    {
        if (!Auth::check()) return false;

        /** @var User $user */
        $user = Auth::user();

        return $user->hasPermission($module, $action);
    }
}