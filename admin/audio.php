<?php
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";

$list = queryArray("SELECT * FROM list_hour_audio");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>List Audio</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">


</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <h4>
                        <?php echo $_SESSION["user"]; ?>
                    </h4>
                </li>
            </ul>

        </nav>
        <!-- /.navbar -->
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index3.html" class="brand-link">
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
                            <a href="index.php" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pesan.php" class="nav-link">
                                <i class="nav-icon fas fa-mail-bulk"></i>
                                <p>
                                    Log Pesan
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="panggilan.php" class="nav-link">
                                <i class="nav-icon fas fa-phone"></i>
                                <p>
                                    Log Panggilan
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="audio.php" class="nav-link active">
                                <i class="nav-icon fas fa-sliders-h"></i>
                                <p>
                                    Setting audio
                                </p>
                            </a>
                        </li>
                        <?php if ($_SESSION["user"] == "teknisi") { ?>
                            <li class="nav-item">
                                <a href="setting.php" class="nav-link">
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <p>
                                        Setting Ruang
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="setting_umum.php" class="nav-link">
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <p>
                                        Setting Umum
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="function/logout.php" class="nav-link">
                                    <i class="nav-icon fas fa-sign-out-alt"></i>
                                    <p>
                                        Logout
                                    </p>
                                </a>
                            </li>
                        <?php } else { ?>
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



        <div class="content-wrapper px-4 py-2">
            <section class="content-header">
                <div class="container-fluid">
                <div class="d-flex justify-content-between">
                        <h1>Setting Audio</h1>
                        <div class="d-flex">
                            <button class="btn btn-primary d-flex align-items-center" data-toggle="modal" data-target="#modal-tambah-list">
                                <i class="fas fa-plus"></i>
                                <div class="ml-2">Tambah List</div>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card p-4">
                        <table class="table table-striped display" >
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Volume</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($list as $item) { ?>
                                    <tr>
                                        <td><?= $item['time'] ?></td>
                                        <td><?= $item['vol'] ?></td>
                                        <td>
                                            <a href="function/list-audio-delete.php?time=<?= $item['time'] ?>">
                                                <button class="btn btn-danger btn-sm">Delete</button>    
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>


    <div class="modal fade" id="modal-tambah-list">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="function/list-audio-add.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah List</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label >Waktu</label>
                            <input type="time" class="form-control" name="time" required>
                        </div>
                        <div class="form-group">
                            <label >Volume</label>
                            <input type="number" class="form-control" name="vol" required>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>

    <?php if (isset($_SESSION["flash-message"])) { ?>
        <script>
            $(document).Toasts('create', {
                title: 'Notifikasi',
                autohide: true,
                delay: 5000,
                position: 'bottomRight',
                class: '<?= $_SESSION["flash-message"]["success"] ? "bg-success mr-5 mb-5" : "bg-danger mr-5 mb-5" ?>',
                body: '<?= $_SESSION["flash-message"]["message"] ?>'
            })
        </script>
    <?php unset($_SESSION["flash-message"]);
    } ?>

</body>

</html>
