<?php
$menu = 'setting_adzan';
session_start();

if (!isset($_SESSION["user"])) {
    if ($_SESSION["user"] != "admin" || $_SESSION["user"] != "teknisi") {
        header("location: http://localhost/ip-call/auth/login.php");
    }
}
require_once "config.php";

// Ganti dengan path file Liquidsoap kamu yang sebenarnya
$filePath = '/opt/lampp/htdocs/ip-call/liquidsoap/radio.liq'; // Contoh path, sesuaikan!
// Atau jika file ada di direktori yang sama dengan script PHP:
// $filePath = 'radio.liq';

$jadwalSholat = [];

$active = queryArray("SELECT * FROM utils WHERE type = 'adzan_active'")[0]['value'];
$auto = queryArray("SELECT * FROM utils WHERE type = 'adzan_auto'")[0]['value'];

if($auto == "0") {
    $adzan = queryArray("SELECT * FROM adzan ORDER BY value");
    foreach ($adzan as $key => $value) {
        $namaSholat = ucfirst($value['key']);
        $jadwalSholat[] = ['nama' => $namaSholat, 'waktu' => $value['value']];
    }
} else {
    // Cek apakah file ada dan bisa dibaca
    if (file_exists($filePath) && is_readable($filePath)) {
        // Baca seluruh isi file ke dalam string
        $string = file_get_contents($filePath);

        if ($string === false) {
            echo "<p>Gagal membaca isi file: " . htmlspecialchars($filePath) . "</p>";
            die();
        } else {
            // Ambil bagian di dalam #=# ... #=#
            preg_match('/#=#(.*?)#=#/s', $string, $matches);

            if (isset($matches[1])) {
                $blokJadwal = trim($matches[1]);
                // Pecah per baris
                $barisJadwal = explode("\n", $blokJadwal);

                foreach ($barisJadwal as $baris) {
                    // Hapus komentar baris jika ada (misalnya yang diawali dengan # selain #subuh, #dhuhur, dll.)
                    // Ini opsional, tergantung seberapa bersih format di dalam #=# ... #=# kamu
                    // $baris = preg_replace('/^\s*#(?!(subuh|dhuhur|ashar|maghrib|isya)\b).*/', '', $baris);
                    // $baris = trim($baris);
                    // if (empty($baris)) {
                    //     continue;
                    // }

                    // Cocokkan waktu dan nama sholat
                    // Pola regex: ({ XXhYYm } ..., #nama_sholat)
                    preg_match('/\(\{\s*(\d{2}h\d{2}m)\s*\}\s*,\s*amplify\(.*?\)\),\s*#(\w+)/', trim($baris), $detail);
                    if (count($detail) === 3) {
                        $waktu = str_replace(['h', 'm'], [':', ''], $detail[1]);
                        $namaSholat = ucfirst($detail[2]); // Kapitalisasi huruf pertama
                        $jadwalSholat[] = ['nama' => $namaSholat, 'waktu' => $waktu];
                    }
                }
            }
        }
    } else {
        echo "<p>File tidak ditemukan atau tidak dapat dibaca di path: " . htmlspecialchars($filePath) . "</p>";
        die();
    }

}



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
                    <h1>Informasi Adzan</h1>
                    <div class="d-flex align-items-center" style="gap: 50px">
                        <div class="custom-control custom-switch custom-switch-off-disabled custom-switch-on-primary ">
                            <input type="checkbox" class="custom-control-input" name="tw" id="adzan-aktif" onchange="location.href = 'function/adzan/active.php'" <?= $active == 1 ? 'checked' : ''  ?>>
                            <label class="custom-control-label" for="adzan-aktif">Aktif/Tidak</label>
                        </div>
                        <div class="custom-control custom-switch custom-switch-off-disabled custom-switch-on-primary ">
                            <input type="checkbox" class="custom-control-input" name="tw" id="switch-otomatis" onchange="location.href = 'function/adzan/update_auto.php'" <?= $auto == 1 ? 'checked' : ''  ?>>
                            <label class="custom-control-label" for="switch-otomatis">Otomatis</label>
                        </div>
                        <button class="btn btn-primary" onclick="simpan()">Simpan</button>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <section class="content">
            <form id="waktu-sholat" class="container-fluid">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Sholat</th>
                        <th>Waktu</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($jadwalSholat as $j) { ?>
                    <tr>
                        <td><?= $j['nama'] ?></td>
                        <td>
                            <input type="time" name="<?= strtolower($j['nama']) ?>" class="form-control" value="<?= $j['waktu'] ?>">
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </form>
        </section>
    </div>
</div>




<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

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
    function simpan() {
        auto = <?= $auto ?>;

        const data = $('#waktu-sholat').serializeArray()

        console.log(data)

        $.ajax({
            url: 'function/adzan/update.php',
            data: data,
            method: 'POST',
            success: data => {
                Swal.fire({
                    icon: 'success',
                    text: 'Data tersimpan'
                })
            }, error: data => Swal.fire({
                icon: 'error',
                html: data.responseJSON ? data.responseJSON : data.responseText
            })
        })

    }
</script>

</body>

</html>
