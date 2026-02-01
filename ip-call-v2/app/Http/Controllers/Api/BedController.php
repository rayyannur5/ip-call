<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function setIp(Request $request)
    {
        $id = $request->input('id');
        $ip = $request->input('ip');

        if (!$id || !$ip) {
             return response()->json(0); // Legacy returned queryBoolean result, which is affected rows (int).
        }

        $affected = Bed::where('id', $id)->update(['ip' => $ip]);
        
        return response()->json($affected); 
    }

    public function get(Request $request) {
        $id = $request->input('id');
        $data = Bed::where('room_id', $id)->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getAll() {
        $data = Bed::all();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getOne(Request $request) {
        $id = $request->input('id');
        $data = Bed::where('id', $id)->first();
        // Legacy get_one.php logic likely returns single object or array of 1.
        // Assuming array of 1 based on queryArray logic usually returning list.
        // But if it's get_one, maybe object directly?
        // queryArray returns array of arrays. 
        // If legacy used queryArray and echoed json_encode([success, data]), data would be [row].
        $data = $data ? [$data] : [];
        return response()->json(['success' => true, 'data' => $data]);
    }
}
