<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toilet;
use Illuminate\Http\Request;

class ToiletController extends Controller
{
    public function get(Request $request) {
        $id = $request->input('id');
        // Legacy get.php: SELECT * FROM toilet WHERE id = '$id'
        $data = Toilet::where('id', $id)->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getAll() {
        $data = Toilet::all();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getRoom(Request $request) {
        $id = $request->input('id'); 
        // Assuming get_room.php takes id (room_id)
        $data = Toilet::where('room_id', $id)->get();
        return response()->json(['success' => true, 'data' => $data]);
    }
}
