<?php
$menu = 'pesan';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";
$categories = queryArray("SELECT * FROM category_log");

$date_now = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Log Pesan</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- <link rel="stylesheet" href="plugins/datatables/jquery.dataTables.js"> -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
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
                        <h1>Log Pesan</h1>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card p-4">
                        <div class="d-flex justify-content-end">
                            <!-- <a href="" class="btn btn-success mr-2">Unduh Excel</a> -->
                            <input type="date" class="form-control w-25 mr-2" id="date-filter" value="<?= $date_now ?>">
                            <select class="custom-select w-25" id="category-filter">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?= $category["id"] ?>"> <?= $category["name"] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <table id="table" class="table table-striped display" >
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Pesan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/datatables/jquery.dataTables.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>

    <script>
    var table = new DataTable('#table', {
        ajax: {
            "url": "function/pesan_get.php",
            "data": function ( d ) {
                d.date = $('#date-filter').val();
                d.category = $('#category-filter').val();
            }
        },
        processing: true,
        serverSide: true,
        searching: false,
        columns: [
            { "data": "name_category" },
            { "data": "value" },
            { "data": "timestamp" }
        ]
    });

    $('#date-filter').on('change', function() {
            table.draw();
        });
    $('#category-filter').on('change', function() {
            table.draw();
        });
    </script>

</body>

</html>
