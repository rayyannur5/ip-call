<?php
$menu = 'index';
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
    <title>Dashboard</title>
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
        <?php include('sidebar.php') ?>


        <div class="content-wrapper px-4 py-2">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between">
                        <h1>Dashboard</h1>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <select class="custom-select w-25" id="message-filter">
                                <option value="month">30 Hari Terakhir</option>
                                <option value="week">7 Minggu Terakhir</option>
                                <option value="day">Hari ini</option>
                            </select>
                            <canvas id="message-log"></canvas>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <select class="custom-select w-25" id="call-filter">
                                <option value="month">30 Hari Terakhir</option>
                                <option value="week">7 Minggu Terakhir</option>
                                <option value="day">Hari ini</option>
                            </select>
                            <canvas id="call-log"></canvas>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/chart.js/chart.js"></script>

    <script>
        let chartMessage;
        let chartCall;

      const messageLog = document.getElementById('message-log');
      const callLog = document.getElementById('call-log');

   
        async function createChartMessage() {

            const filter = document.getElementById('message-filter').value;

            const response = await fetch(`function/pesan_chart.php?filter=${filter}`);
            const data = await response.json();

            const labels = data.map(item => item.name);
            const datasets = data.map(item => item.count);

            if (chartMessage) {
                chartMessage.destroy();
            }
        
            chartMessage = new Chart(messageLog, {
              type: 'bar',
      
              data: {
                labels: labels,
                datasets: [{
                  data: datasets,
                  borderWidth: 1
                }]
              },
              options: {
                scales: {
                  y: {
                    beginAtZero: true
                  }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Log Pesan',
                        font: {
                            size: 24
                        }
                    },
                    legend: {
                        display: false // Menyembunyikan legenda
                    }
                }
              }
            });
        }

        async function createChartCall() {

            const filter = document.getElementById('call-filter').value;

            const response = await fetch(`function/panggilan_chart.php?filter=${filter}`);
            const data = await response.json();

            const labels = data.map(item => item.name);
            const datasets = data.map(item => item.count);

            if (chartCall) {
                chartCall.destroy();
            }
        
            chartCall = new Chart(callLog, {
              type: 'bar',
      
              data: {
                labels: labels,
                datasets: [{
                  data: datasets,
                  borderWidth: 1
                }]
              },
              options: {
                scales: {
                  y: {
                    beginAtZero: true
                  }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Log Panggilan',
                        font: {
                            size: 24
                        }
                    },
                    legend: {
                        display: false // Menyembunyikan legenda
                    }
                }
              }
            });
        }


    createChartMessage()
    $('#message-filter').on('change', function() {
        createChartMessage()
    });

    createChartCall()
    $('#call-filter').on('change', function() {
        createChartCall()
    });

    </script>

</body>

</html>
