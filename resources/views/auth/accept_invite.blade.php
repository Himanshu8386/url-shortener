@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card card-custom shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <span class="brand-badge">URL</span>
                    <h5 class="mb-0 fw-bold">Accept Invitation</h5>
                </div>

                <div class="card-body p-4">
                    <div class="alert alert-info py-2 mb-3">
                        You have been invited to join 
                        <strong>{{ $invitation->company ? $invitation->company->name : 'Platform' }}</strong> 
                        as <span class="badge bg-primary">{{ $invitation->role }}</span>.
                    </div>

                    <form method="POST" action="{{ route('invite.process', $invitation->token) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control bg-light" value="{{ $invitation->email }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Your Full Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $invitation->name) }}" required autofocus
                                   placeholder="e.g. John Doe">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Choose Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password"
                                   placeholder="Minimum 6 characters">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                            <input id="password_confirmation" type="password" class="form-control"
                                   name="password_confirmation" required autocomplete="new-password"
                                   placeholder="Re-enter password">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-brand py-2 fw-semibold">
                                Complete Registration & Access Dashboard
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
