<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Nurse Call Admin')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: linear-gradient(rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0.7)), url('{{ asset('assets/images/bg.JPEG') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            flex: 1;
        }
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            height: 100vh;
            position: sticky;
            top: 0;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            transition: all 0.3s ease;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            z-index: 100;
            overflow-y: auto;
        }
        /* Custom scrollbar for sidebar */
        #sidebar::-webkit-scrollbar {
            width: 5px;
        }
        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        #sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        #sidebar .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            position: sticky;
            top: 0;
            background: #0f172a;
            z-index: 10;
        }
        .sidebar-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(6, 182, 212, 0.15));
            border: 1px solid rgba(79, 70, 229, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-brand-icon i {
            font-size: 18px;
            color: #06b6d4;
        }
        .brand-text {
            font-size: 1.15rem;
            letter-spacing: -0.3px;
            color: #f8fafc;
        }
        .brand-subtext {
            font-size: 0.725rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        #sidebar ul.components {
            padding: 15px 0;
        }
        #sidebar ul li {
            margin: 4px 12px;
        }
        #sidebar ul li a {
            padding: 12px 16px;
            font-size: 0.925rem;
            font-weight: 550;
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.25s ease;
        }
        #sidebar ul li a:hover {
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }
        #sidebar ul li.active > a {
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }
        @keyframes contentFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        #content {
            width: 100%;
            padding: 24px;
            min-height: 100vh;
            transition: all 0.3s;
            flex-fill: 1;
        }
        .fade-in-active {
            animation: contentFadeIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        .nav-icon {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            margin-right: 12px;
            opacity: 0.85;
        }
        #sidebar ul li a:hover .nav-icon {
            opacity: 1;
            color: #06b6d4;
        }
        #sidebar ul li.active > a .nav-icon {
            opacity: 1;
            color: #fff !important;
        }
        .header-menu {
            color: #64748b !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            letter-spacing: 1px !important;
            text-transform: uppercase;
            padding: 18px 16px 8px 16px !important;
            margin-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background-color: transparent !important;
        }

        /* Floating Glassmorphic App Bar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            border-radius: 16px;
            padding: 10px 20px !important;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
        }

        .btn-sidebar-toggle {
            background: rgba(79, 70, 229, 0.08) !important;
            border: 1px solid rgba(79, 70, 229, 0.2) !important;
            color: #4f46e5 !important;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }

        .btn-sidebar-toggle:hover {
            background: #4f46e5 !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }

        .user-profile-badge {
            background: rgba(79, 70, 229, 0.08) !important;
            border: 1px solid rgba(79, 70, 229, 0.15) !important;
            color: #4f46e5 !important;
            font-weight: 600;
            padding: 8px 16px !important;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .user-profile-badge:hover {
            background: rgba(79, 70, 229, 0.12) !important;
            color: #4338ca !important;
        }

    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header d-flex align-items-center gap-3">
            <div class="sidebar-brand-icon">
                <i class="fas fa-user-nurse"></i>
            </div>
            <div>
                <h4 class="m-0 fw-bold brand-text">Nurse Call</h4>
                <small class="brand-subtext">Administrator</small>
            </div>
        </div>

        <ul class="list-unstyled components">
            {{-- Public Menus - Always Visible --}}
            <li class="{{ request()->is('ip-call/admin') ? 'active' : '' }}">
                <a href="{{ url('/ip-call/admin') }}">
                    <i class="fas fa-home nav-icon"></i> Dashboard
                </a>
            </li>
            <li class="{{ request()->is('ip-call/admin/messages') ? 'active' : '' }}">
                <a href="{{ url('/ip-call/admin/messages') }}">
                    <i class="fas fa-envelope nav-icon"></i> Log Pesan
                </a>
            </li>
            <li class="{{ request()->is('ip-call/admin/calls') ? 'active' : '' }}">
                <a href="{{ url('/ip-call/admin/calls') }}">
                    <i class="fas fa-phone nav-icon"></i> Log Panggilan
                </a>
            </li>
            <li class="{{ request()->is('ip-call/admin/oximonitor') ? 'active' : '' }}">
                <a href="{{ url('/ip-call/admin/oximonitor') }}">
                    <i class="fas fa-heartbeat nav-icon"></i> Oxi-Monitor
                </a>
            </li>
            <li class="{{ request()->is('ip-call/admin/audio') ? 'active' : '' }}">
                <a href="{{ url('/ip-call/admin/audio') }}">
                    <i class="fas fa-volume-up nav-icon"></i> Setting Audio
                </a>
            </li>

            {{-- Teknisi Only Menu - Only visible when logged in as teknisi --}}
            @if (Auth::check() && Auth::user()->username == 'teknisi')
                <li class="header-menu p-2">SETTINGS</li>
                
                <li class="{{ request()->is('ip-call/admin/monitoring') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/monitoring') }}">
                        <i class="fas fa-desktop nav-icon"></i> Monitoring
                    </a>
                </li>
                
                <li class="{{ request()->is('ip-call/admin/rooms') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/rooms') }}">
                        <i class="fas fa-door-open nav-icon"></i> Setting Ruang
                    </a>
                </li>
                <li class="{{ request()->is('ip-call/admin/general') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/general') }}">
                        <i class="fas fa-cogs nav-icon"></i> Setting Umum
                    </a>
                </li>
                <li class="{{ request()->is('ip-call/admin/backup-restore') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/backup-restore') }}">
                        <i class="fas fa-database nav-icon"></i> Backup & Restore
                    </a>
                </li>
                <li class="{{ request()->is('ip-call/admin/running-text') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/running-text') }}">
                        <i class="fas fa-scroll nav-icon"></i> Setting Running Text
                    </a>
                </li>
                <li class="{{ request()->is('ip-call/admin/adzan') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/adzan') }}">
                        <i class="fas fa-mosque nav-icon"></i> Informasi Adzan
                    </a>
                </li>
                <li class="{{ request()->is('ip-call/admin/playlist') ? 'active' : '' }}">
                    <a href="{{ url('/ip-call/admin/playlist') }}">
                        <i class="fas fa-music nav-icon"></i> Setting Playlist
                    </a>
                </li>
            @endif
            
            {{-- Close Tab --}}
            <li>
                <a href="#" id="closeTab">
                    <i class="fas fa-times nav-icon"></i> Tutup Tab
                </a>
            </li>

            {{-- Login / Logout --}}
            @if (Auth::check())
                <li>
                    <a href="{{ url('/ip-call/logout') }}">
                        <i class="fas fa-sign-out-alt nav-icon"></i> Logout
                    </a>
                </li>
            @else
                <li>
                    <a href="{{ url('/ip-call/login') }}">
                        <i class="fas fa-sign-in-alt nav-icon"></i> Login
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid p-0">
                <button type="button" id="sidebarCollapse" class="btn-sidebar-toggle">
                    <i class="fas fa-align-left"></i>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ms-auto">
                        <li class="nav-item">
                            @if (Auth::check())
                                <span class="user-profile-badge">
                                    <i class="fas fa-user-circle"></i>
                                    {{ Auth::user()->username }}
                                </span>
                            @else
                                <a class="user-profile-badge" href="{{ url('/ip-call/login') }}">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            @yield('content')
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="{{ asset('assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<!-- Chart.js -->
<script src="{{ asset('assets/vendor/chartjs/chart.js') }}"></script>

<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            if($('#sidebar').hasClass('active')){
                 $('#sidebar').css('margin-left', '-250px');
            } else {
                 $('#sidebar').css('margin-left', '0');
            }
        });

        $('#closeTab').on('click', function (e) {
            e.preventDefault();

            var closeTabAction = function() {
                // Trik agar browser menganggap tab ini dibuka oleh script
                window.open('', '_self');
                window.close();
                // Fallback jika tetap tidak bisa
                setTimeout(function() {
                    window.location.href = 'about:blank';
                }, 300);
            };

            @if (Auth::check())
            // Logout dulu jika sudah login, baru tutup tab
            $.ajax({
                url: '{{ url("/ip-call/logout") }}',
                type: 'GET',
                success: function() {
                    closeTabAction();
                },
                error: function() {
                    // Tetap tutup tab meskipun logout gagal
                    closeTabAction();
                }
            });
            @else
            closeTabAction();
            @endif
        });
    });
</script>
@yield('scripts')

<!-- SweetAlert2 -->
<script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    // Global SweetAlert2 Toast Mixin
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if(session('success'))
        Toast.fire({
            icon: 'success',
            title: '{{ session('success') }}'
        });
    @endif

    @if(session('error'))
        Toast.fire({
            icon: 'error',
            title: '{{ session('error') }}'
        });
    @endif
</script>
<script>
    // Dynamically trigger content fade-in and clean it up to prevent stacking context (z-index modal) bugs
    (function() {
        const content = document.getElementById('content');
        if (content) {
            content.classList.add('fade-in-active');
            content.addEventListener('animationend', function() {
                content.classList.remove('fade-in-active');
            });
        }
    })();
</script>
</body>
</html>
