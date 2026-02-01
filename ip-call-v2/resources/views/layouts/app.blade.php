<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Nurse Call Admin')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            flex: 1;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #343a40;
        }
        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }
        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }
        #sidebar ul li a {
            padding: 10px;
            font-size: 1.1em;
            display: block;
            color: #c2c7d0;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #fff;
            background: #495057;
        }
        #sidebar ul li.active > a {
            color: #fff;
            background: #007bff;
        }
        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        .nav-icon {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>Nurse Call</h3>
        </div>

        <ul class="list-unstyled components">
            <li class="{{ request()->is('admin') ? 'active' : '' }}">
                <a href="{{ url('/admin') }}">
                    <i class="fas fa-home nav-icon"></i> Dashboard
                </a>
            </li>
            <li class="{{ request()->is('admin/messages') ? 'active' : '' }}">
                <a href="{{ url('/admin/messages') }}">
                    <i class="fas fa-envelope nav-icon"></i> Log Pesan
                </a>
            </li>
            <li class="{{ request()->is('admin/calls') ? 'active' : '' }}">
                <a href="{{ url('/admin/calls') }}">
                    <i class="fas fa-phone nav-icon"></i> Log Panggilan
                </a>
            </li>
            <li class="{{ request()->is('admin/oximonitor') ? 'active' : '' }}">
                <a href="{{ url('/admin/oximonitor') }}">
                    <i class="fas fa-heartbeat nav-icon"></i> Oxi-Monitor
                </a>
            </li>
            <li class="{{ request()->is('admin/audio') ? 'active' : '' }}">
                <a href="{{ url('/admin/audio') }}">
                    <i class="fas fa-volume-up nav-icon"></i> Setting Audio
                </a>
            </li>

            {{-- Teknisi Only Menu --}}
            {{-- Assuming basic auth check. Update with specific Gate/Policy later --}}
            {{-- For now displaying all or logic based on session role if available --}}
            
            <li class="header-menu p-2 text-muted">SETTINGS</li>
            
            <li class="{{ request()->is('admin/rooms') ? 'active' : '' }}">
                <a href="{{ url('/admin/rooms') }}">
                    <i class="fas fa-door-open nav-icon"></i> Setting Ruang
                </a>
            </li>
            <li class="{{ request()->is('admin/general') ? 'active' : '' }}">
                <a href="{{ url('/admin/general') }}">
                    <i class="fas fa-cogs nav-icon"></i> Setting Umum
                </a>
            </li>
            <li class="{{ request()->is('admin/running-text') ? 'active' : '' }}">
                <a href="{{ url('/admin/running-text') }}">
                    <i class="fas fa-scroll nav-icon"></i> Setting Running Text
                </a>
            </li>
            <li class="{{ request()->is('admin/adzan') ? 'active' : '' }}">
                <a href="{{ url('/admin/adzan') }}">
                    <i class="fas fa-mosque nav-icon"></i> Informasi Adzan
                </a>
            </li>
            <li class="{{ request()->is('admin/playlist') ? 'active' : '' }}">
                <a href="{{ url('/admin/playlist') }}">
                    <i class="fas fa-music nav-icon"></i> Setting Playlist
                </a>
            </li>
             <li>
                <a href="{{ url('/logout') }}"> {{-- Implement logout route --}}
                    <i class="fas fa-sign-out-alt nav-icon"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="fas fa-align-left"></i>
                    <span>Toggle Sidebar</span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#">User</a> {{-- Dynamic user name --}}
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    });
</script>
@yield('scripts')

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
</body>
</html>
