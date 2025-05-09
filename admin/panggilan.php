<?php
$menu = 'panggilan';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";
$categories = queryArray("SELECT * FROM category_history");

$date_now = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Log Panggilan</title>
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

        <!-- /.navbar -->
        <?php include('sidebar.php') ?>



        <div class="content-wrapper px-4 py-2">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between">
                        <h1>Log Panggilan</h1>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card p-4">
                        <div class="d-flex justify-content-end">
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
                                <th>Bed</th>
                                <th>Kategori</th>
                                <th>Durasi</th>
                                <th>Rekaman</th>
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
            "url": "function/panggilan_get.php",
            "data": function ( d ) {
                d.category = $('#category-filter').val();
                d.date = $('#date-filter').val();
            }
        },
        processing: true,
        serverSide: true,
        searching: false,
        columns: [
            { "data": "name_bed" },
            { "data": "name_category" },
            { "data": "duration" },
            { 
                "data": "record",
                "render" : function (data, type, row) {
                    if(data == null) return "";
                    return `<audio src="http://localhost/${data}" controls style="height: 20px;"></audio>`;
                },
                 "type": "html"
            },
            { "data": "timestamp" }
        ]
    });

    $('#category-filter').on('change', function() {
        table.draw();
    });

    $('#date-filter').on('change', function() {
        table.draw();
    });
    </script>

</body>

</html>
