<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'URL Shortener') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f7f9fc;
            color: #334155;
        }
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 2px solid #ea580c;
        }
        .brand-badge {
            background-color: #f97316;
            color: #ffffff;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-right: 8px;
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background-color: #ffffff;
        }
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 2px solid #e2e8f0;
        }
        .btn-brand {
            background-color: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }
        .btn-brand:hover {
            background-color: #0369a1;
            color: #ffffff;
        }
        .btn-orange {
            background-color: #f97316;
            color: #ffffff;
            border-color: #f97316;
        }
        .btn-orange:hover {
            background-color: #ea580c;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-custom shadow-sm">
            <div class="container-fluid px-4">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="{{ url('/dashboard') }}">
                    <span class="brand-badge">URL</span>
                    <span>Dashboard</span>
                </a>

                <div class="ms-auto d-flex align-items-center">
                    @auth
                        @if(Auth::user()->company)
                            <span class="text-muted me-3 d-none d-md-inline">
                                <strong>Client:</strong> {{ Auth::user()->company->name }}
                            </span>
                        @endif
                        <span class="badge bg-secondary me-3">{{ getUserRoleName() }}</span>
                        <span class="me-3 fw-medium text-dark">{{ Auth::user()->name }}</span>

                        <a class="btn btn-sm btn-outline-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout <i class="bi bi-box-arrow-right"></i>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container-fluid px-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
