<!-- /.navbar -->
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Nurse Call Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?= $menu == 'index' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pesan.php" class="nav-link <?= $menu == 'pesan' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-mail-bulk"></i>
                        <p>
                            Log Pesan
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="panggilan.php" class="nav-link <?= $menu == 'panggilan' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-phone"></i>
                        <p>
                            Log Panggilan
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="audio.php" class="nav-link <?= $menu == 'audio' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p>
                            Setting audio
                        </p>
                    </a>
                </li>
                <?php if ($_SESSION["user"] == "teknisi") { ?>
                    <li class="nav-item">
                        <a href="setting.php" class="nav-link <?= $menu == 'setting' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>
                                Setting Ruang
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="setting_umum.php" class="nav-link <?= $menu == 'setting_umum' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>
                                Setting Umum
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="setting_running_text.php" class="nav-link <?= $menu == 'setting_running_text' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>
                                Setting Running Text
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="setting_music.php" class="nav-link <?= $menu == 'setting_music' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>
                                Setting Musik (Murotal)
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="setting_adzan.php" class="nav-link <?= $menu == 'setting_adzan' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>
                                Informasi Adzan
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="function/logout.php" class="nav-link ">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>
                                Logout
                            </p>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($_SESSION["user"] != "teknisi") { ?>
                    <li class="nav-item">
                        <a onclick="closeWindow()" class="nav-link">
                            <i class="nav-icon fas fa-window-close"></i>
                            <p>
                                Close
                            </p>
                        </a>
                    </li>
                    <script type="text/javascript">
                        function closeWindow() {
                            window.open('','_parent','');
                            window.close();
                        }
                    </script>
                <?php } ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
