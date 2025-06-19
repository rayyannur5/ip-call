<?php
$menu = 'setting_music';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";

$playlists = queryArray("SELECT * FROM playlist");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Setting Musik (Murotal)</title>
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
                        <h1>Setting Musik (Murotal)</h1>
                        <div>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah-playlist"><i class="fa fa-plus"></i> Tambah</button>
                            <a href="function/playlist/write.php" class="btn btn-danger"><i class="fa fa-check"></i> Update</a>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <?php foreach($playlists as $playlist) { ?>
                            <div class="col-4">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <div class="card-title"><?= $playlist['name'] ?> (<?= $playlist['start_time'] ?> => <?= $playlist['end_time'] ?>)</div>
                                        <div class="card-tools">
                                            <button class="btn btn-tool" data-toggle="modal" data-target="#modal-tambah-playlist-item-<?= $playlist['id'] ?>"><i class="fas fa-plus"></i></button>
                                            <button type="button" class="btn btn-tool" data-toggle="modal" data-target="#modal-ubah-playlist-<?= $playlist['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column mb-3">
                                            <?php $id = $playlist['id']; $items = queryArray("SELECT * FROM playlist_item where id = $id"); ?>
                                            <?php foreach($items as $item) { ?>
                                            <div class="bg-info rounded-lg p-3 mb-2 d-flex align-items-center justify-content-between" style="gap: 30px">
                                                <div style="min-width: max-content"><?= $item["path"] ?></div>
                                                <audio src="/ip-call/playlist/music/<?= $item['path'] ?>" controls class="w-100 mb-2"></audio>
                                                <a href="function/playlist/item/delete.php?id=<?= $item['id'] ?>&ord=<?= $item['ord'] ?>" class="btn m-0 p-0 text-white">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                    </div>
                </div>
            </section>
        </div>
    </div>


    <div class="modal fade" id="modal-tambah-playlist">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="function/playlist/add.php" method="post">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Playlist</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label>Nama Playlist</label>
                            <input type="text" name="name" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>Volume</label>
                            <input type="number" name="volume" min="0" max="100" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>Waktu Mulai</label>
                            <input type="time" name="start" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>Waktu Selesai</label>
                            <input type="time" name="end" class="form-control">
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

    <?php foreach($playlists as $playlist) { ?>

    <div class="modal fade" id="modal-ubah-playlist-<?= $playlist['id'] ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="function/playlist/update.php" method="post">
                    <div class="modal-header">
                        <h4 class="modal-title">Ubah Playlist</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $playlist['id'] ?>">
                        <div class="form-group mb-2">
                            <label>Nama Playlist</label>
                            <input type="text" name="name" value="<?= $playlist['name'] ?>" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>Volume</label>
                            <input type="number" name="volume" value="<?= $playlist['volume'] ?>" min="0" max="100" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>Waktu Mulai</label>
                            <input type="time" name="start" value="<?= $playlist['start_time'] ?>" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>Waktu Selesai</label>
                            <input type="time" name="end" value="<?= $playlist['end_time'] ?>" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        <div>
                            <a href="function/playlist/delete.php?id=<?= $playlist['id'] ?>" type="button" class="btn btn-danger">Hapus</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-tambah-playlist-item-<?= $playlist['id'] ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="function/playlist/item/add.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Playlist Item</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label>File</label>
                            <input type="file" name="file" class="form-control-file">
                            <input type="hidden" name="playlist_id" value="<?= $playlist['id'] ?>">
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
