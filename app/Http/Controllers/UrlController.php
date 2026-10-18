<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShortUrl;

class UrlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            abort(403, 'SuperAdmin cannot create short URLs.');
        }

        $request->validate([
            'original_url' => 'required|url|max:2048',
        ]);

        $code = generateShortCode(6);

        $shortUrl = ShortUrl::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'original_url' => $request->original_url,
            'short_code' => $code,
            'clicks_count' => 0,
        ]);

        return redirect()->back()->with('success', 'Short URL created successfully: ' . url('/' . $code));
    }
}
