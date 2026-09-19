<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Validasi Susenas') }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">

            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                Validasi Susenas
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a href="{{ route('petugas.index') }}"
                            class="nav-link {{ request()->routeIs('petugas.*') ? 'active' : '' }}">
                            Petugas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}"
                            class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            Report
                        </a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>

    <main class="container py-4">
        <x-alert />
        @yield('content')
    </main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')

</body>

</html>
