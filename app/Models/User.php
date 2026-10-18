<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function userRole()
    {
        return $this->hasOne(UserRole::class, 'user_id', 'id');
    }

    public function shortUrls()
    {
        return $this->hasMany(ShortUrl::class, 'user_id');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return $this->roles()->whereIn('name', $roles)->exists();
        }
        return $this->hasRole($roles);
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('SuperAdmin');
    }

    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }

    public function isMember()
    {
        return $this->hasRole('Member');
    }
}
