<?php
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";
$rooms = queryArray("SELECT * FROM room");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Setting Ruang</title>
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
                            <a href="audio.php" class="nav-link">
                                <i class="nav-icon fas fa-sliders-h"></i>
                                <p>
                                    Setting audio
                                </p>
                            </a>
                        </li>
                        <?php if ($_SESSION["user"] == "teknisi") { ?>
                            <li class="nav-item">
                                <a href="setting.php" class="nav-link active">
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
                                <button onclick="closeWindow()" class="nav-link">
                                    <i class="nav-icon fas "></i>
                                    <p>
                                        Close
                                    </p>
                                </button>
                            </li>
                            <script type="text/javascript">
                                window.open('','_parent',''); 
                                window.close();
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
                        <h1>Setting Ruang</h1>
                        <div class="d-flex">
                            <button class="btn btn-primary d-flex align-items-center" data-toggle="modal" data-target="#modal-tambah-ruang">
                                <i class="fas fa-plus"></i>
                                <div class="ml-2">Tambah Ruang</div>
                            </button>
                            <button class="btn btn-danger ml-2" data-toggle="modal" data-target="#modal-ask-reboot">Update & Reboot</button>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <?php foreach ($rooms as $room) {
                            $room_id = $room["id"]; ?>
                            <div class="col-3">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title"><?= $room["name"] ?></h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-toggle="modal" data-target="#modal-ubah-ruang-<?= $room_id ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="text-secondary">Bed</h5>
                                            <form action="function/bed-add.php" method="post">
                                                <input type="text" name="room_id" value="<?= $room_id ?>" hidden />
                                                <button class="btn" type="submit">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <hr class="hr mt-0" />
                                        <div class="d-flex flex-column mb-3">
                                            <?php
                                            $beds = queryArray("SELECT * FROM bed WHERE room_id = $room_id");
                                            foreach ($beds as $index => $bed) { ?>
                                                <div class="bg-info rounded-lg p-3 mb-2 d-flex align-items-center justify-content-between">
                                                    <div><?= $bed["id"] ?></div>
                                                    <div><?= $bed["username"] ?></div>
                                                    <button class="btn m-0 p-0 text-white" data-toggle="modal" data-target="#modal-ubah-bed-<?= $bed["id"] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="modal-ubah-bed-<?= $bed["id"] ?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="function/bed-update.php" method="post">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">Ubah <?= $bed["username"] ?></h4>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <a href="http://<?= $bed["ip"] ?>:5000" target="_blank">
                                                                        <p><?= $bed["ip"] ?></p>
                                                                    </a>
                                                                    <input type="text" value="<?= $bed["id"] ?>" name="id" hidden />
                                                                    <div class="form-group">
                                                                        <div class="custom-control custom-switch custom-switch-off-disabled custom-switch-on-primary ">
                                                                            <input type="checkbox" class="custom-control-input" name="tw" id="switch-tw-<?= $bed["id"] ?>" <?= $bed["tw"] ? "checked" : "" ?>>
                                                                            <label class="custom-control-label" for="switch-tw-<?= $bed["id"] ?>">Two ways</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="switch-mode-<?= $bed["id"] ?>">Mode</span>
                                                                        <input type="number" class="form-control" name="mode" aria-describedby="switch-mode-<?= $bed["id"] ?>" value="<?= $bed["mode"] ?>">
                                                                    </div>
                                                                    <label class="form-label" id="label-volume-range-<?= $bed["id"] ?>" for="ubah-volume-range-<?= $bed["id"] ?>">Volume : <?= $bed["vol"] ?></label>
                                                                    <div class="range">
                                                                        <input type="range" class="form-range w-100" name="vol" value="<?= $bed["vol"] ?>" id="ubah-volume-range-<?= $bed["id"] ?>" />
                                                                    </div>
                                                                    <label class="form-label" id="label-mic-range-<?= $bed["id"] ?>" for="ubah-mic-range-<?= $bed["id"] ?>">Mic : <?= $bed["mic"] ?></label>
                                                                    <div class="range">
                                                                        <input type="range" class="form-range w-100" name="mic" value="<?= $bed["mic"] ?>" id="ubah-mic-range-<?= $bed["id"] ?>" />
                                                                    </div>

                                                                    <script>
                                                                        document.getElementById('ubah-volume-range-<?= $bed["id"] ?>').oninput = (val) => {
                                                                            document.getElementById('label-volume-range-<?= $bed["id"] ?>').innerHTML = `Volume : ${val.target.value}`
                                                                        }
                                                                    </script>

                                                                    <script>
                                                                        document.getElementById('ubah-mic-range-<?= $bed["id"] ?>').oninput = (val) => {
                                                                            document.getElementById('label-mic-range-<?= $bed["id"] ?>').innerHTML = `Mic : ${val.target.value}`
                                                                        }
                                                                    </script>

                                                                </div>
                                                                <div class="modal-footer justify-content-between">
                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                                    <div class="d-flex">
                                                                        <?php if (count($beds) == $index + 1) { ?>
                                                                            <a href="function/bed-delete.php?id=<?= $bed["id"] ?>">
                                                                                <button type="button" class="btn btn-danger mr-2">Hapus</button>
                                                                            </a>
                                                                        <?php } ?>
                                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                                    </div>

                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }
                                            ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="text-secondary">Toilet</h5>
                                            <form action="function/toilet-add.php" method="post">
                                                <input type="text" name="room_id" value="<?= $room_id ?>" hidden />
                                                <button class="btn" type="submit">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <hr class="hr mt-0" />
                                        <div class="d-flex flex-column mb-3">
                                            <?php
                                            $toilets = queryArray("SELECT * FROM toilet WHERE room_id = $room_id");
                                            foreach ($toilets as $index => $toilet) { ?>
                                                <div class="bg-info rounded-lg p-3 mb-2 d-flex align-items-center justify-content-between">
                                                    <div><?= $toilet["id"] ?></div>
                                                    <div><?= $toilet["username"] ?></div>
                                                    <?php if (count($toilets) == $index + 1) { ?>
                                                        <a href="function/toilet-delete.php?id=<?= $toilet["id"] ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php } else { ?>
                                                        <div style="width: 16px;"></div>
                                                    <?php } ?>
                                                </div>
                                            <?php }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        } ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="modal-ask-reboot">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="function/update-reboot.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title">Pastikan Perubahan Data Benar!</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">Reboot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-tambah-ruang">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="function/room-add.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Ruang</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tambah-ruang-nama">Nama Ruang</label>
                            <input type="text" class="form-control" id="tambah-ruang-nama" placeholder="Ketik disini" name="name" required>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="audio" id="tambah-ruang-file" accept=".ogg" required>
                            <label class="custom-file-label" id="tambah-label-ruang-file" for="tambah-ruang-file">Choose file</label>
                            <script>
                                document.getElementById("tambah-ruang-file").onchange = (event) => {
                                    document.getElementById("tambah-label-ruang-file").innerHTML = event.target.files[0].name
                                }
                            </script>
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

    <?php foreach ($rooms as $room) { ?>
        <div class="modal fade text-dark" id="modal-ubah-ruang-<?= $room["id"] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="function/room-update.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h4 class="modal-title">Ubah Ruang</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="id" value="<?= $room["id"] ?>" hidden />
                            <div class="form-group">
                                <label for="input-ruang-nama-<?= $room["id"] ?>">Nama</label>
                                <input type="text" class="form-control" name="name" id="input-ruang-nama-<?= $room["id"] ?>" placeholder="Ketik disini" value="<?= $room["name"] ?>">
                            </div>
                            <div class="d-flex flex-column">
                                <label>Audio</label>
                                <audio src="<?= $room["audio"] ?>" controls class="w-100 mb-2"></audio>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="audio" id="input-ruang-file<?= $room["id"] ?>" accept=".ogg">
                                <label class="custom-file-label" id="label-ruang-file<?= $room["id"] ?>" for="input-ruang-file<?= $room["id"] ?>">Choose file</label>
                            </div>
                            <script>
                                document.getElementById("input-ruang-file<?= $room["id"] ?>").onchange = (event) => {
                                    document.getElementById("label-ruang-file<?= $room["id"] ?>").innerHTML = event.target.files[0].name
                                }
                            </script>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                            <div class="d-flex">
                                <a href="function/room-delete.php?id=<?= $room["id"] ?>">
                                    <button type="button" class="btn btn-danger mr-2">Hapus</button>
                                </a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    <?php } ?>
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