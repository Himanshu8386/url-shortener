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
                                    <td class="text-truncate" style="max-width: 450px;" title="{{ $url->original_url }}">
                                        {{ $url->original_url }}
                                    </td>
                                    <td><span class="badge bg-primary">{{ $url->clicks_count }}</span></td>
                                    <td>{{ $url->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">You haven't generated any short URLs yet.</td>
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
@endsection
