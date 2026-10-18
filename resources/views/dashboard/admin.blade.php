@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-bold text-dark">Generated Short URLs</h5>
                    <button type="button" class="btn btn-sm btn-brand px-3 ms-2" data-bs-toggle="modal" data-bs-target="#generateUrlModal">
                        <i class="bi bi-link-45deg me-1"></i> Generate
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small d-none d-sm-inline">View and Download based on Date interval:</span>
                    <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
                        <select name="interval" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="this_month" {{ $interval == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $interval == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="last_week" {{ $interval == 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="today" {{ $interval == 'today' ? 'selected' : '' }}>Today</option>
                        </select>
                    </form>

                    <a href="{{ route('dashboard.export', ['interval' => $interval]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Short URL</th>
                                <th>Long URL</th>
                                <th>Clicks</th>
                                <th>User Name</th>
                                <th>Created On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($urls as $url)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ $url->short_url }}" target="_blank" class="fw-semibold text-decoration-none">
                                            {{ $url->short_url }}
                                        </a>
                                    </td>
                                    <td class="text-truncate" style="max-width: 350px;" title="{{ $url->original_url }}">
                                        {{ $url->original_url }}
                                    </td>
                                    <td><span class="badge bg-primary">{{ $url->clicks_count }}</span></td>
                                    <td>{{ $url->user ? $url->user->name : 'N/A' }}</td>
                                    <td>{{ $url->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No short URLs generated for this interval.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($urls->hasPages())
                <div class="card-footer bg-white border-top py-2">
                    {{ $urls->appends(['interval' => $interval])->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Team Members</h5>
                <button type="button" class="btn btn-sm btn-brand px-3" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                    <i class="bi bi-person-plus me-1"></i> Invite
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">User Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Total Generated URLs</th>
                                <th>Total URL Clicks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $member)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        <span class="badge {{ $member->isAdmin() ? 'bg-info text-dark' : 'bg-secondary' }}">
                                            {{ $member->roles->first() ? $member->roles->first()->name : 'Member' }}
                                        </span>
                                    </td>
                                    <td>{{ $member->short_urls_count }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $member->shortUrls->sum('clicks_count') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No members found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($members->hasPages())
                <div class="card-footer bg-white border-top py-2">
                    {{ $members->appends(['interval' => $interval])->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="generateUrlModal" tabindex="-1" aria-labelledby="generateUrlModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('urls.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="generateUrlModalLabel">Generate Short URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Long URL</label>
                        <input type="url" name="original_url" class="form-control" required placeholder="e.g. https://domain.com/long-page-path-example">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-labelledby="inviteMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('invitations.member') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="inviteMemberModalLabel">Invite New Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Jane Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="e.g. member@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Member" selected>Member</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Send Invitation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
