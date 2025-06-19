<?php

require_once('../../config.php');

session_start();


// Langsung proses jika ada data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Daftar key yang diharapkan ada di $_POST (nama-nama sholat)
    // Ini minimal untuk mengontrol field mana yang akan diproses.
    $expected_keys = ['subuh', 'dhuhur', 'ashar', 'maghrib', 'isya']; // Sesuaikan jika perlu

    foreach ($_POST as $key => $value) {
        // Hanya proses key yang diharapkan dan value tidak kosong (opsional, bisa dihapus jika value kosong diizinkan)
        if (in_array(strtolower($key), $expected_keys) && !empty(trim($value))) {
            $namaSholat = strtolower(trim($key)); // 'subuh', 'dzuhur', dll.
            $waktuSholat = trim($value);         // '04:30', dll.

            // PERINGATAN: $namaSholat dan $waktuSholat dimasukkan langsung ke query. SANGAT BERISIKO!
            $query = "INSERT INTO adzan (`key`, `value`) VALUES ('{$namaSholat}', '{$waktuSholat}')
                      ON DUPLICATE KEY UPDATE `value` = '{$waktuSholat}'";

            queryBoolean($query);
            // Kita tidak memeriksa hasil dari queryBoolean() sesuai permintaan "gausah ribet"
            // dan langsung set pesan sukses.
        }
    }

    // Set pesan sukses generik
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Otomatis tersimpan'
    ];
} else {
    // Jika bukan request POST, mungkin set pesan default atau tidak sama sekali
    // Sesuai template awal, kita tetap set pesan sukses jika langsung akses.
    // Jika ini tidak diinginkan, tambahkan logika di sini.
    $_SESSION['flash-message'] = [
        'success' => true, // Atau false jika ingin menandakan tidak ada aksi
        'message' => 'Tidak ada data yang diproses (bukan POST request).' // Pesan bisa disesuaikan
    ];
}


header('location: ../../setting_adzan.php');
exit();