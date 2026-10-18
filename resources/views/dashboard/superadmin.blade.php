@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Clients</h5>
                <button type="button" class="btn btn-sm btn-brand px-3" data-bs-toggle="modal" data-bs-target="#inviteClientModal">
                    <i class="bi bi-plus-lg me-1"></i> Invite
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Client Name</th>
                                <th>Users</th>
                                <th>Total Generated URLs</th>
                                <th>Total URL Clicks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clients as $client)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        {{ $client->name }}
                                        @if($client->email)
                                            <div class="text-muted small fw-normal">{{ $client->email }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $client->users_count }}</td>
                                    <td>{{ $client->short_urls_count }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $client->total_clicks }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No clients registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($clients->hasPages())
                <div class="card-footer bg-white border-top py-2">
                    {{ $clients->appends(['interval' => $interval])->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold text-dark">Generated Short URLs</h5>

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
                                <th>Client Name</th>
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
                                    <td class="text-truncate" style="max-width: 320px;" title="{{ $url->original_url }}">
                                        {{ $url->original_url }}
                                    </td>
                                    <td><span class="badge bg-primary">{{ $url->clicks_count }}</span></td>
                                    <td>{{ $url->company ? $url->company->name : 'Platform' }}</td>
                                    <td>{{ $url->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No short URLs found for this interval.</td>
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
</div>

<div class="modal fade" id="inviteClientModal" tabindex="-1" aria-labelledby="inviteClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('invitations.client') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="inviteClientModalLabel">Invite New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Client Name</label>
                        <input type="text" name="client_name" class="form-control" required placeholder="e.g. ASP Corp.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="e.g. admin@aspcorp.com">
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
