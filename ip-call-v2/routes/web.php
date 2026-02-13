<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\RunningTextController;
use App\Http\Controllers\Api\AdzanController;
use App\Http\Controllers\Api\SoundController;
use App\Http\Controllers\Api\OxiMonitorController;
use App\Http\Controllers\Api\BedController;
use App\Http\Controllers\Api\ToiletController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UtilController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\CallController;

use App\Http\Controllers\Api\PlaylistController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make sure something great!
|
*/

// Redirect root to admin panel
Route::get('/', function () {
    return redirect('/admin');
});

// Legacy API Routes (No Auth, No CSRF via Middleware Exception)
// Note: Laravel deployed at /ip-call/ subdirectory, so '/server' becomes '/ip-call/server'
Route::group(['prefix' => 'server'], function () {
    
    // Exact file paths as routes
    Route::any('device.php', [DeviceController::class, 'index']);
    Route::any('device2w.php', [DeviceController::class, 'index2w']);
    Route::any('running_text.php', [RunningTextController::class, 'index']);
    Route::any('adzan.php', [AdzanController::class, 'index']);
    Route::any('sounds.php', [SoundController::class, 'index']);
    Route::any('music.php', [PlaylistController::class, 'index']); 
    Route::any('utils.php', [UtilController::class, 'index']);
    Route::any('oximonitor.php', [OxiMonitorController::class, 'handle']);
    Route::any('device_by_runningtext.php', [DeviceController::class, 'byRunningText']);

    // Subdirectories
    Route::prefix('bed')->group(function () {
        Route::any('get.php', [BedController::class, 'get']);
        Route::any('get_all.php', [BedController::class, 'getAll']);
        Route::any('get_one.php', [BedController::class, 'getOne']);
        Route::any('set_ip.php', [BedController::class, 'setIp']);
    });

    Route::prefix('toilet')->group(function () {
        Route::any('get.php', [ToiletController::class, 'get']);
        Route::any('get_all.php', [ToiletController::class, 'getAll']);
        Route::any('get_room.php', [ToiletController::class, 'getRoom']);
    });

    Route::prefix('room')->group(function () {
        Route::any('get.php', [RoomController::class, 'get']);
        Route::any('get_one.php', [RoomController::class, 'getOne']);
    });

    Route::prefix('hour')->group(function () {
        Route::any('get.php', [App\Http\Controllers\Api\HourController::class, 'get']);
        Route::any('set.php', [App\Http\Controllers\Api\HourController::class, 'set']);
    });


    Route::any('log/get/index.php', [LogController::class, 'get']);
    Route::any('log/excel.php', [LogController::class, 'excel']);
    Route::any('log/pdf.php', [LogController::class, 'pdf']);
    Route::any('log/{category}/create.php', [LogController::class, 'create'])
        ->where('category', 'darurat|call|blue|infus|assist');
    Route::any('logout.php', [App\Http\Controllers\Admin\AuthController::class, 'logout']);

    // History routes
    Route::prefix('history')->group(function () {
        Route::any('get.php', [App\Http\Controllers\Api\HistoryController::class, 'get']);
        Route::any('create.php', [App\Http\Controllers\Api\HistoryController::class, 'create']);
        Route::any('update.php', [App\Http\Controllers\Api\HistoryController::class, 'update']);
        Route::any('excel.php', [App\Http\Controllers\Api\HistoryController::class, 'excel']);
        Route::any('pdf.php', [App\Http\Controllers\Api\HistoryController::class, 'pdf']);
        Route::any('list_audio.php', [App\Http\Controllers\Api\HistoryController::class, 'list_audio']);
    });

});

// Auth Routes
Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
Route::any('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout'); 

// Static files routes (legacy compatibility)
Route::get('/admin/static/{file}', function ($file) {
    $path = public_path("assets/static/$file");
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('file', '.*');

Route::get('/admin/uploads/{file}', function ($file) {
    $path = public_path("assets/uploads/$file");
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('file', '.*');

// Admin Routes (Public - No Auth Required)
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('/messages', [App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/export/{type}', [App\Http\Controllers\Admin\MessageController::class, 'export'])->name('messages.export');
    
    Route::get('/calls', [App\Http\Controllers\Admin\CallController::class, 'index'])->name('calls.index');
    Route::get('/calls/export/{type}', [App\Http\Controllers\Admin\CallController::class, 'export'])->name('calls.export');

    // OxiMonitor (public view)
    Route::get('/oximonitor', [App\Http\Controllers\Admin\OxiMonitorController::class, 'index']);
    Route::get('/oximonitor/metrics', [App\Http\Controllers\Admin\OxiMonitorController::class, 'metrics']);
    Route::post('/oximonitor/data', [App\Http\Controllers\Admin\OxiMonitorController::class, 'getData']);

    // Audio (public view)
    Route::get('/audio', [App\Http\Controllers\Admin\AudioController::class, 'index']);
});

// Admin Routes (Protected - Teknisi Only)
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    // Audio management
    Route::post('/audio/store', [App\Http\Controllers\Admin\AudioController::class, 'store']);
    Route::get('/audio/destroy/{id}', [App\Http\Controllers\Admin\AudioController::class, 'destroy']);

    // General Settings
    Route::get('/general', [App\Http\Controllers\Admin\GeneralController::class, 'index']);
    Route::post('/general/update', [App\Http\Controllers\Admin\GeneralController::class, 'update']);
    
    // Rooms
    Route::get('/rooms/bulk-mode', [App\Http\Controllers\Admin\RoomController::class, 'bulkUpdateMode'])->name('rooms.bulk_mode');
    Route::get('/rooms/bulk-tw', [App\Http\Controllers\Admin\RoomController::class, 'bulkUpdateTw'])->name('rooms.bulk_tw');
    Route::get('/rooms/bulk-cable', [App\Http\Controllers\Admin\RoomController::class, 'bulkUpdateCable'])->name('rooms.bulk_cable');
    Route::get('/rooms', [App\Http\Controllers\Admin\RoomController::class, 'index']);
    Route::post('/rooms/store', [App\Http\Controllers\Admin\RoomController::class, 'store'])->name('rooms.store');
    Route::post('/rooms/update', [App\Http\Controllers\Admin\RoomController::class, 'update'])->name('rooms.update');
    Route::get('/rooms/destroy', [App\Http\Controllers\Admin\RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::get('/rooms/bypass', [App\Http\Controllers\Admin\RoomController::class, 'bypass'])->name('rooms.bypass');
    Route::post('/rooms/reboot', [App\Http\Controllers\Admin\RoomController::class, 'reboot'])->name('rooms.reboot');
    
    // Room sub-actions (Beds/Toilets)
    Route::post('/beds/store', [App\Http\Controllers\Admin\RoomController::class, 'storeBed'])->name('beds.store');
    Route::post('/beds/update', [App\Http\Controllers\Admin\RoomController::class, 'updateBed'])->name('beds.update');
    Route::get('/beds/destroy', [App\Http\Controllers\Admin\RoomController::class, 'destroyBed'])->name('beds.destroy');
    
    Route::post('/toilets/store', [App\Http\Controllers\Admin\RoomController::class, 'storeToilet'])->name('toilets.store');
    Route::get('/toilets/destroy', [App\Http\Controllers\Admin\RoomController::class, 'destroyToilet'])->name('toilets.destroy');

    // Running Text
    Route::get('/running-text', [App\Http\Controllers\Admin\RunningTextController::class, 'index']);
    Route::post('/running-text/store', [App\Http\Controllers\Admin\RunningTextController::class, 'store']);
    Route::post('/running-text/update/{topic}', [App\Http\Controllers\Admin\RunningTextController::class, 'update']);
    Route::get('/running-text/destroy/{topic}', [App\Http\Controllers\Admin\RunningTextController::class, 'destroy']);

    // Adzan
    Route::get('/adzan', [App\Http\Controllers\Admin\AdzanController::class, 'index']);
    Route::post('/adzan/update', [App\Http\Controllers\Admin\AdzanController::class, 'update']);
    
    // Playlist
    Route::get('/playlist', [App\Http\Controllers\Admin\PlaylistController::class, 'index']);
    Route::post('/playlist/store', [App\Http\Controllers\Admin\PlaylistController::class, 'store']);
    Route::post('/playlist/update', [App\Http\Controllers\Admin\PlaylistController::class, 'update']);
    Route::get('/playlist/destroy/{id}', [App\Http\Controllers\Admin\PlaylistController::class, 'destroy']);
    Route::post('/playlist/item/store', [App\Http\Controllers\Admin\PlaylistController::class, 'storeItem']);
    Route::get('/playlist/item/destroy/{playlist_id}/{ord}', [App\Http\Controllers\Admin\PlaylistController::class, 'destroyItem']);
    Route::get('/playlist/write-config', [App\Http\Controllers\Admin\PlaylistController::class, 'writeConfig']);
    
    // Monitoring System
    Route::get('/monitoring', function () {
        return view('admin.monitoring');
    });
});
