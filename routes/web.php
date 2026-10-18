<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UrlController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\InvitationController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::get('/invite/accept/{token}', [InvitationController::class, 'accept'])->name('invite.accept');
Route::post('/invite/accept/{token}', [InvitationController::class, 'processAccept'])->name('invite.process');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'exportCsv'])->name('dashboard.export');

    Route::post('/urls', [UrlController::class, 'store'])->name('urls.store');

    Route::post('/invitations/client', [InvitationController::class, 'inviteClient'])->name('invitations.client');
    Route::post('/invitations/member', [InvitationController::class, 'inviteMember'])->name('invitations.member');
});

Route::get('/{code}', [RedirectController::class, 'resolve'])->name('urls.resolve');
