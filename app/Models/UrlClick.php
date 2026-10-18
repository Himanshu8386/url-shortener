<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_url_id',
        'ip_address',
        'user_agent',
    ];

    public function shortUrl()
    {
        return $this->belongsTo(ShortUrl::class, 'short_url_id');
    }
}
