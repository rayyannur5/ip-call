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
            ['type' => 'interval_update_status', 'value' => 35000],
            ['type' => 'one_room_one_device', 'value' => 0],
            ['type' => 'interval_speaks', 'value' => 8000],
            ['type' => 'timeout_call', 'value' => 60000],
            ['type' => 'time_autorefresh', 'value' => 0],
            ['type' => 'timeout_running_text', 'value' => 8500],
            ['type' => 'timeout_time_activity', 'value' => 60000],
            ['type' => 'adzan_volume', 'value' => 10],
            ['type' => 'adzan_auto', 'value' => 0],
            ['type' => 'adzan_latitude', 'value' => -7.288354699999995],
            ['type' => 'adzan_longitude', 'value' => 112.72549628465647],
            ['type' => 'adzan_active', 'value' => 1],
        ]);

        // Users
        // Passwords in SQL dump are already hashed with bcrypt ($2a$12$).
        // Laravel uses bcrypt by default, so we can insert them directly.
        DB::table('users')->insert([
            ['username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin'],
            ['username' => 'user', 'password' => Hash::make('12345678'), 'role' => 'user'],
            ['username' => 'teknisi', 'password' => Hash::make('teknisi123'), 'role' => 'teknisi'],
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
            ['id' => 18, 'name' => '101', 'source' => 'uploads/101.mp3'],
            ['id' => 19, 'name' => 'Tes', 'source' => 'uploads/'],
            ['id' => 20, 'name' => 'Tes 2', 'source' => 'uploads/'],
        ]);
        
        // Rooms can be seeded from dump if needed, but looks like test data.
        DB::table('room')->insert([
             ['id' => 1, 'type' => 'Ruang', 'name' => 'Tes', 'running_text' => '', 'type_bed' => 'numeric', 'bed_separator' => '', 'serial_number' => NULL, 'bypass' => 0],
             ['id' => 2, 'type' => 'Ruang', 'name' => 'Tes 2', 'running_text' => '', 'type_bed' => 'numeric', 'bed_separator' => '', 'serial_number' => NULL, 'bypass' => 0]
        ]);
    }
}
