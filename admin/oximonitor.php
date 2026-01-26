<?php
$menu = 'oximonitor';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Oxi-Monitor</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <style>
        .small-box {
            border-radius: 10px;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            background: white;
            color: #333;
            padding: 20px;
            margin-bottom: 20px;
        }
        .small-box h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
            padding: 0;
        }
        .small-box p {
            font-size: 1rem;
            margin-bottom: 5px;
            font-weight: 600;
            color: #666;
        }
        .unit {
            font-size: 0.9rem;
            float: right;
            color: #888;
        }
        .header-blue {
            background-color: #4B39C6; 
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .live-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            margin-right: 5px;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            50% { opacity: 0; }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
        </nav>

        <?php include('sidebar.php') ?>

        <div class="content-wrapper px-4 py-2">
            <div class="header-blue">
                <h3 class="m-0">Oxi-Monitor</h3>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Row 1 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box">
                                <span class="unit">L/min</span>
                                <p><span class="live-indicator"></span>Aktual</p>
                                <h3 id="current_flow">0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>Hari ini</p>
                                <h3 id="usage_today">0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>3 Hari Terakhir</p>
                                <h3 id="usage_3_days">0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>7 Hari Terakhir</p>
                                <h3 id="usage_7_days">0,00</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>Rata-rata 3 Hari</p>
                                <h3 id="avg_3_days">0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>Rata-rata 7 Hari</p>
                                <h3 id="avg_7_days">0,00</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>14 Hari Terakhir</p>
                                <h3 id="usage_14_days">0,00</h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small-box">
                                <span class="unit">m³</span>
                                <p>1 Bulan Terakhir</p>
                                <h3 id="usage_30_days">0,00</h3>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-4 mb-3">Log Data</h4>
                    <div class="card p-4">
                        <div class="d-flex justify-content-end mb-3">
                             <div class="input-group" style="width: 250px;">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                  </span>
                                </div>
                                <input type="text" class="form-control float-right" id="daterange">
                              </div>
                        </div>
                        <table id="table" class="table table-striped display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Total (m³)</th>
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
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>

    <script>
    // Initialize Date Range Picker
    $('#daterange').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        locale: {
            format: 'YYYY-MM-DD'
        }
    });

    var table = new DataTable('#table', {
        ajax: {
            "url": "function/oximonitor_get.php",
            "type": "POST",
            "data": function(d) {
                var drp = $('#daterange').data('daterangepicker');
                d.startDate = drp.startDate.format('YYYY-MM-DD');
                d.endDate = drp.endDate.format('YYYY-MM-DD');
            }
        },
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false, 
        columns: [
            { "data": 0 }, 
            { "data": 1 }, 
            { "data": 2 }  
        ]
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        table.draw();
    });

    // Real-time Update
    function updateMetrics() {
        $.ajax({
            url: 'function/oximonitor_metrics.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#current_flow').text(data.current_flow);
                $('#usage_today').text(data.usage_today);
                $('#usage_3_days').text(data.usage_3_days);
                $('#usage_7_days').text(data.usage_7_days);
                $('#avg_3_days').text(data.avg_3_days);
                $('#avg_7_days').text(data.avg_7_days);
                $('#usage_14_days').text(data.usage_14_days);
                $('#usage_30_days').text(data.usage_30_days);
            }
        });
    }

    // Initial load
    updateMetrics();

    // Poll every 2 seconds
    setInterval(updateMetrics, 2000);

    </script>
</body>
</html>
