<?php
$menu = 'setting_running_text';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";
$utils = queryArray("SELECT * FROM running_text");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Setting Umum</title>
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
                        <h1>Setting Umum</h1>
                        <a href="function/running-text-add.php">
                            <button class="btn btn-primary"><i class="fa fa-plus"></i> Tambah</button>
                        </a>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card p-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Topic</th>
                                    <th>Speed</th>
                                    <th>Brightness</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($utils as $key => $util) { ?>
                                    <tr>
                                        <td><?= $util['topic'] ?></td>
                                        <td>
                                            <input class="form-control" id="val-<?= $util['topic'] ?>" type="text" value="<?= $util['speed'] ?>">
                                        </td>
                                        <td>
                                            <input class="form-control" id="val-b-<?= $util['topic'] ?>" type="text" value="<?= $util['brightness'] ?>">
                                        </td>
                                        <td>
                                            <button id="<?= $util['topic'] ?>_button" class="btn btn-warning">Update</button>
                                            <?php if(count($utils) - 1 == $key) { ?>
                                                <a href="function/running-text-delete.php">
                                                    <button class="btn btn-danger">Hapus</button>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>

                                    <script>
                                        document.getElementById("<?= $util['topic'] ?>_button").onclick = () => {
                                            const value = document.getElementById("val-<?= $util['topic'] ?>").value;
                                            const valueb = document.getElementById("val-b-<?= $util['topic'] ?>").value;
                                            window.location.href = `function/running_text_update.php?topic=<?= $util['topic'] ?>&speed=${value}&brightness=${valueb}`
                                        }
                                    </script>

                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
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
