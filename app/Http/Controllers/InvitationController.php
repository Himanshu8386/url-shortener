<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Invitation;

class InvitationController extends Controller
{
    public function inviteClient(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can invite a new company.');
        }

        $request->validate([
            'client_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $company = Company::create([
            'name' => $request->client_name,
            'email' => $request->email,
            'status' => 1,
        ]);

        $token = Str::random(40);

        $invitation = Invitation::create([
            'company_id' => $company->id,
            'invited_by' => $user->id,
            'email' => $request->email,
            'role' => 'Admin',
            'token' => $token,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $inviteUrl = url('/invite/accept/' . $token);

        return redirect()->back()->with('success', 'Invitation created for ' . $request->client_name . '. Invite Link: ' . $inviteUrl);
    }

    public function inviteMember(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only company Admins can invite team members.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:Admin,Member',
        ]);

        $token = Str::random(40);

        $invitation = Invitation::create([
            'company_id' => $user->company_id,
            'invited_by' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'token' => $token,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $inviteUrl = url('/invite/accept/' . $token);

        return redirect()->back()->with('success', 'Invitation sent to ' . $request->email . '. Invite Link: ' . $inviteUrl);
    }

    public function accept($token)
    {
        $invitation = Invitation::with('company')
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        return view('auth.accept_invite', compact('invitation'));
    }

    public function processAccept(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'company_id' => $invitation->company_id,
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'status' => 1,
        ]);

        $role = Role::where('name', $invitation->role)->first();
        if ($role) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }

        $invitation->update([
            'status' => 'accepted',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been setup successfully.');
    }
}
