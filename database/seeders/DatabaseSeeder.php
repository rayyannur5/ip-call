<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utils
        DB::table('utils')->insert([
            ['type' => 'interval_update_status', 'value' => 120000, 'description' => '[APP] Menentukan berapa lama status perangkat ditampilkan sebagai "aktif" sebelum kembali ke status offline jika tidak ada sinyal baru.'],
            ['type' => 'one_room_one_device', 'value' => 0, 'description' => '[SERVER] Program lama, jika ada 1 ruang 1 device'],
            ['type' => 'interval_speaks', 'value' => 8000, 'description' => '[APP] Interval waktu antar pengucapan pesan suara (Text-to-Speech)'],
            ['type' => 'timeout_call', 'value' => 60000, 'description' => '[APP] Batas waktu (timeout) untuk panggilan telepon sebelum dianggap tidak terjawab.'],
            ['type' => 'time_autorefresh', 'value' => 0, 'description' => '[APP] Waktu jeda sebelum halaman web memuat ulang (refresh) secara otomatis.'],
            ['type' => 'timeout_running_text', 'value' => 8500, 'description' => '[PYTHON] Waktu jeda sebelum menampilkan teks berikutnya pada running text.'],
            ['type' => 'timeout_time_activity', 'value' => 60000, 'description' => '[DEVICE2W] Waktu setelah ada aktifitas tombol untuk memutar lagu playlist lagi'],
            ['type' => 'adzan_volume', 'value' => 10, 'description' => '[PYTHON] Volume adzan'],
            ['type' => 'adzan_auto', 'value' => 0, 'description' => '[PYTHON] Mengaktifkan jadwal adzan otomatis'],
            ['type' => 'adzan_latitude', 'value' => -7.288354699999995, 'description' => '[PYTHON] Latitude adzan'],
            ['type' => 'adzan_longitude', 'value' => 112.72549628465647, 'description' => '[PYTHON] Longitude adzan'],
            ['type' => 'adzan_active', 'value' => 1, 'description' => '[PYTHON] Mengaktifkan adzan'],
        ]);

        // Users
        // Passwords in SQL dump are already hashed with bcrypt ($2a$12$).
        // Laravel uses bcrypt by default, so we can insert them directly.
        DB::table('users')->insert([
            ['username' => 'teknisi', 'password' => Hash::make('12orangepi12'), 'role' => 'teknisi'],
        ]);

        // Adzan
        DB::table('adzan')->insert([
            ['key' => 'ashar', 'value' => '14:48:00'],
            ['key' => 'dhuhur', 'value' => '11:27:00'],
            ['key' => 'isya', 'value' => '23:05:00'],
            ['key' => 'maghrib', 'value' => '17:18:00'],
            ['key' => 'subuh', 'value' => '04:12:00'],
        ]);

        // Category History
        DB::table('category_history')->insert([
            ['id' => 1, 'name' => 'PANGGILAN MASUK'],
            ['id' => 2, 'name' => 'PANGGILAN TIDAK TERJAWAB'],
            ['id' => 3, 'name' => 'PANGGILAN KELUAR'],
        ]);

        // Category Log
        DB::table('category_log')->insert([
            ['id' => 1, 'name' => 'DARURAT'],
            ['id' => 2, 'name' => 'TELEPON'],
            ['id' => 3, 'name' => 'CODE BLUE'],
            ['id' => 4, 'name' => 'INFUS'],
            ['id' => 5, 'name' => 'PERAWAT'],
        ]);

        // Master Sound
        DB::table('mastersound')->insert([
            ['id' => 1, 'name' => 'Ruang', 'source' => 'static/ruang.mp3'],
            ['id' => 2, 'name' => 'Kamar', 'source' => 'static/kamar.mp3'],
            ['id' => 3, 'name' => 'Toilet', 'source' => 'static/toilet.mp3'],
            ['id' => 4, 'name' => 'Bed', 'source' => 'static/Bed.mp3'],
        ]);
        
    }
}
