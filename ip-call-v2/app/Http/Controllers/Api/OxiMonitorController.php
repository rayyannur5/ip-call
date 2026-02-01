<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OxiMonitorLog;
use App\Models\OxiMonitorStatus;
use Illuminate\Http\Request;

class OxiMonitorController extends Controller
{
    public function handle(Request $request)
    {
        $flow = $request->input('flow');
        $volume = $request->input('volume');

        $response = [
            "success" => true,
            "message" => "No data received"
        ];

        if ($flow !== null) {
            try {
                // Upsert id=1
                OxiMonitorStatus::updateOrInsert(
                    ['id' => 1],
                    ['flow_rate' => floatval($flow), 'updated_at' => now()]
                );
                $response["flow_update"] = "Success";
            } catch (\Exception $e) {
                $response["success"] = false;
                $response["flow_error"] = $e->getMessage();
            }
        }

        if ($volume !== null) {
            try {
                OxiMonitorLog::create([
                    'volume' => floatval($volume),
                    'created_at' => now()
                ]);
                $response["volume_insert"] = "Success";
            } catch (\Exception $e) {
                $response["success"] = false;
                $response["volume_error"] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
