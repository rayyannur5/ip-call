<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    /**
     * Get history data by date
     * GET /server/history/get.php?date=YYYY-MM-DD
     */
    public function get(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        
        $data = DB::table('history')
            ->join('category_history', 'history.category_history_id', '=', 'category_history.id')
            ->join('bed', 'bed.id', '=', 'history.bed_id')
            ->whereDate('history.timestamp', $date)
            ->select('history.*', 'bed.username', 'bed.phone', 'category_history.name')
            ->orderBy('history.timestamp', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create new history entry
     * GET /server/history/create.php?category=X&bed_id=Y&duration=Z
     */
    public function create(Request $request)
    {
        $category = $request->input('category');
        $duration = $request->input('duration', '0 detik');
        $bed_id = $request->input('bed_id');
        $timestamp = now();

        DB::table('history')->insert([
            'bed_id' => $bed_id,
            'category_history_id' => $category,
            'duration' => $duration,
            'record' => null,
            'timestamp' => $timestamp,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update last history with record URL
     * GET /server/history/update.php?name=filename
     */
    public function update(Request $request)
    {
        $name = $request->input('name');
        $url = "records/$name.wav";

        $lastHistory = DB::table('history')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastHistory) {
            DB::table('history')
                ->where('id', $lastHistory->id)
                ->update(['record' => $url]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Export history to Excel
     */
    public function excel(Request $request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Not implemented yet']);
    }

    /**
     * Export history to PDF
     */
    public function pdf(Request $request)
    {
        // TODO: Implement PDF export
        return response()->json(['message' => 'Not implemented yet']);
    }
}
