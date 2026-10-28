<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') · æ-WASH</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/aewash.css') }}" rel="stylesheet">
    <link href="{{ asset('css/orders.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navbar --}}
    <nav class="glass glass-nav d-flex align-items-center justify-content-between flex-wrap gap-2">
        <a href="{{ url('/') }}" class="brand-mark">
            æ-WASH
            <small>I Wish You Wash Here</small>
        </a>

        @auth
            <div class="d-flex align-items-center flex-wrap gap-1">
                @if (auth()->user()->isAdmin())
                    <a class="nav-link-glass" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a class="nav-link-glass" href="{{ route('admin.services.index') }}"><i class="bi bi-tags"></i> Layanan</a>
                    <a class="nav-link-glass" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> User</a>
                @endif
                @if (auth()->user()->hasRole('admin', 'staff'))
                    <a class="nav-link-glass" href="{{ route('staff.dashboard') }}"><i class="bi bi-basket3"></i> Staff</a>
                    <a class="nav-link-glass" href="{{ route('orders.index') }}"><i class="bi bi-receipt"></i> Order</a>
                    <a class="nav-link-glass" href="{{ route('orders.create') }}"><i class="bi bi-plus-circle"></i> Order Baru</a>
                    <a class="nav-link-glass" href="{{ route('customers.index') }}"><i class="bi bi-person-lines-fill"></i> Customer</a>
                @endif
                @if (auth()->user()->hasRole('customer'))
                    <a class="nav-link-glass" href="{{ route('customer.orders') }}"><i class="bi bi-clock-history"></i> Riwayat</a>
                @endif

                <span class="badge badge-role ms-2">{{ ucfirst(auth()->user()->role) }}</span>
                <span class="small ms-1 d-none d-md-inline">{{ auth()->user()->name }}</span>

                <form action="{{ route('logout') }}" method="POST" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-glass btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</button>
                </form>
            </div>
        @endauth
    </nav>

    {{-- Konten --}}
    <main class="container py-4 flex-grow-1">
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i> <div>{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer identitas mahasiswa (ubah di config/student.php) --}}
    <footer class="glass glass-footer">
        &copy; {{ date('Y') }} æ-WASH (Automated Efficient Washing System) &middot;
        {{ config('student.name') }} &middot; {{ config('student.nim') }} &middot;
        {{ config('student.major') }} &middot; {{ config('student.campus') }}
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
