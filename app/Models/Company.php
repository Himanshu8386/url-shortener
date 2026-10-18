<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function shortUrls()
    {
        return $this->hasMany(ShortUrl::class, 'company_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'company_id');
    }

    public function getTotalClicksAttribute()
    {
        return $this->shortUrls()->sum('clicks_count');
    }
}
