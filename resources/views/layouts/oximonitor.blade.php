<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Oxi-Monitor')</title>
    <link href="{{ asset('admin_assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin_assets/vendor/fontawesome/all.min.css') }}">

    <style>
        body {
            min-height: 100vh;
            background-image: linear-gradient(rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0.7)), url('{{ asset('admin_assets/images/bg.JPEG') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .oximonitor-shell {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
        }

        .oximonitor-topbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .oximonitor-user {
            color: #475467;
            font-weight: 700;
        }

        .oximonitor-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            font-weight: 800;
        }
    </style>
</head>
<body>
    <main class="oximonitor-shell">
        <div class="container-fluid">
            <div class="oximonitor-topbar">
                @if (Auth::check())
                    <span class="oximonitor-user">
                        <i class="fas fa-user me-1"></i>{{ Auth::user()->username }}
                    </span>
                @endif
                <a href="{{ url('/ip-call/logout') }}" class="btn btn-outline-danger btn-sm oximonitor-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
            @yield('content')
        </div>
    </main>

    <script src="{{ asset('admin_assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin_assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin_assets/vendor/chartjs/chart.js') }}"></script>
    @yield('scripts')

    <script src="{{ asset('admin_assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
</body>
</html>
