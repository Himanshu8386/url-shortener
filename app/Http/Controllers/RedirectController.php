<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShortUrl;
use App\Models\UrlClick;

class RedirectController extends Controller
{
    public function resolve(Request $request, $code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();

        $shortUrl->increment('clicks_count');

        UrlClick::create([
            'short_url_id' => $shortUrl->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->away($shortUrl->original_url);
    }
}
