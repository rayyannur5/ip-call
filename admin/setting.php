<?php
$menu = 'setting';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";
$rooms = queryArray("SELECT * FROM room");
foreach($rooms as $key => $room) {
    $rooms[$key]['names'] = explode(' ', $room['name']);
    foreach($rooms[$key]['names'] as $key2 => $name) {
        $rooms[$key]['audio'][$key2] = queryArray("SELECT * FROM mastersound WHERE name = '$name'")[0]['source'];
    }
}

$running_texts = queryArray("SELECT * FROM running_text");

//var_dump($rooms);
//die();
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
        <?php include('sidebar.php') ?>



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
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="volume-<?= $bed["id"] ?>">Volume</span>
                                                                        <input type="number" class="form-control" name="vol" value="<?= $bed["vol"] ?>">
                                                                    </div>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="mic-<?= $bed["id"] ?>">Mic</span>
                                                                        <input type="number" class="form-control" name="mic" value="<?= $bed["mic"] ?>">
                                                                    </div>

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
                            <label>ID</label>
                            <input type="text" name="id" value="" class="form-control" />
                        </div>
                        <div class="form-group mb-2">
                            <label>Running Text</label>
                            <select name="running_text" class="form-control" id="">
                                <option value="">Tidak Ada</option>
                                <?php foreach ($running_texts as $text) { ?>
                                    <option value="<?= $text['topic'] ?>"><?= $text['topic'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Jenis Ruang</label>
                            <select name="jenis" class="form-control" id="">
                                <option value="Ruang">Ruang</option>
                                <option value="Kamar">Kamar</option>
                                <option value="">Tidak ada</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Urutan Ruang</label>
                            <select name="type_bed" class="form-control" id="">
                                <option value="numeric">Numeric (1,2,3,..)</option>
                                <option value="abjad">Abjad (A,B,C,..)</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Pemisah Ruang</label>
                            <select name="separator_bed" class="form-control" id="">
                                <option value="">Tidak ada</option>
                                <option value="Bed">Bed</option>
                            </select>
                        </div>
                        <div id="section-tambah-nama-ruang">
                            <div class="form-group" id="kombinasi-1">
                                <label for="tambah-ruang-nama">Nama Ruang 1</label>
                                <input type="text" class="form-control" placeholder="Ketik disini" name="name[]" required>
                                <input type="file" name="audio[]">
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary" onclick="tambahKombinasi()">Tambah Kombinasi</button>
                            <button type="button" class="btn btn-danger" onclick="kurangKombinasi()">Kurang Kombinasi</button>
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
                            <div class="form-group">
                                <label>ID</label>
                                <input type="text" name="last_id" value="<?= $room["id"] ?>" hidden/>
                                <input type="text" name="id" value="<?= $room["id"] ?>" class="form-control" />
                            </div>
                            <div class="form-group mb-2">
                                <label>Running Text</label>
                                <select name="running_text" class="form-control" id="">
                                    <option value="">Tidak Ada</option>
                                    <?php foreach ($running_texts as $text) { ?>
                                        <option value="<?= $text['topic'] ?>" <?= $text['topic'] == $room["running_text"] ? 'selected' : '' ?>><?= $text['topic'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Jenis Ruang</label>
                                <select name="jenis" class="form-control" id="">
                                    <option value="Ruang" <?= $room['type'] == "Ruang" ? 'selected' : '' ?> >Ruang</option>
                                    <option value="Kamar" <?= $room['type'] == "Kamar" ? 'selected' : '' ?> >Kamar</option>
                                    <option value="" <?= $room['type']  == "" ? 'selected' : '' ?> >Tidak ada</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label>Urutan Ruang</label>
                                <select name="type_bed" class="form-control" id="">
                                    <option value="numeric" <?= $room['type_bed'] == 'numeric' ? 'selected' : '' ?> >Numeric (1,2,3,..)</option>
                                    <option value="abjad" <?= $room['type_bed'] == 'abjad' ? 'selected' : '' ?> >Abjad (A,B,C,..)</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label>Pemisah Ruang</label>
                                <select name="separator_bed" class="form-control" id="">
                                    <option value="" <?= $room['bed_separator'] == "" ? 'selected' : '' ?> >Tidak ada</option>
                                    <option value="Bed" <?= $room['bed_separator'] == "Bed" ? 'selected' : '' ?> >Bed</option>
                                </select>
                            </div>

                            <?php foreach ($room['names'] as $key2 => $name) { ?>
                                <div class="form-group">
                                    <label for="input-ruang-nama-<?= $room["id"] ?>">Nama</label>
                                    <input type="text" name="last_name[]" placeholder="Ketik disini" value="<?= $name ?>" hidden>
                                    <input type="text" class="form-control" name="name[]" id="input-ruang-nama-<?= $room["id"] ?>" placeholder="Ketik disini" value="<?= $name ?>">
                                    <input type="file" name="audio[]" id="input-ruang-file<?= $room["id"] ?>">
                                    <audio src="<?= $room["audio"][$key2] ?>" controls class="w-100 mb-2"></audio>
                                </div>
                            <?php } ?>
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

    <script>
        var counterNama = 1
        function tambahKombinasi() {
            counterNama++
            $('#section-tambah-nama-ruang').append(`
                <div class="form-group" id="kombinasi-${counterNama}">
                    <label for="tambah-ruang-nama">Nama Ruang ${counterNama}</label>
                    <input type="text" class="form-control" placeholder="Ketik disini" name="name[]" required>
                    <input type="file" name="audio[]">
                </div>
            `)
        }


        function kurangKombinasi() {
            if(counterNama == 1) return
            $(`#kombinasi-${counterNama}`).remove()
            counterNama--
        }
    </script>
</body>

</html>