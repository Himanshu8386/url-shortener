<?php

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

function getUserRole($user_id = null)
{
    $userId = $user_id ?: Auth::id();
    if (!$userId) {
        return null;
    }

    $userRole = UserRole::where('user_id', $userId)->first();
    return $userRole ? $userRole->role_id : null;
}

function getUserRoleName($user_id = null)
{
    $userId = $user_id ?: Auth::id();
    if (!$userId) {
        return null;
    }

    $user = User::find($userId);
    if (!$user) {
        return null;
    }

    $role = $user->roles()->first();
    return $role ? $role->name : null;
}

function checkUserRole($roles)
{
    if (!Auth::check()) {
        return false;
    }

    $user = Auth::user();
    if (is_string($roles)) {
        $roles = explode('|', $roles);
    }

    return $user->hasAnyRole($roles);
}

function generateShortCode($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
    } while (ShortUrl::where('short_code', $code)->exists());

    return $code;
}
