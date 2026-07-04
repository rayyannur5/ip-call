<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function get() {
        // Legacy get.php: SELECT * FROM room
        $data = Room::all();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getOne(Request $request) {
        $id = $request->input('id');
        $data = Room::where('id', $id)->get();
        return response()->json(['success' => true, 'data' => $data]);
    }
}
