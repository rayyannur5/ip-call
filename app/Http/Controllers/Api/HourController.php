<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ListHourAudio;
use App\Models\Bed;

class HourController extends Controller
{
    public function get(Request $request)
    {
        $data = ListHourAudio::all();
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function set(Request $request)
    {
        $vol = $request->query('vol');

        if ($vol === null) {
            return response()->json([
                'success' => false,
                'message' => 'Missing vol parameter'
            ], 400);
        }

        // Update all beds with the new volume
        Bed::query()->update(['vol' => $vol]);

        return response()->json([
            'success' => true,
            'message' => 'Volume updated successfully'
        ]);
    }
}
