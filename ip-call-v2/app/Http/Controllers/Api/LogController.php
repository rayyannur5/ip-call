<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\CategoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MessagesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LogController extends Controller
{
    // Category mapping: route segment => category_log_id
    private const CATEGORY_MAP = [
        'darurat' => 1,
        'call'    => 2,
        'blue'    => 3,
        'infus'   => 4,
        'assist'  => 5,
    ];

    /**
     * Create a log entry for a specific category.
     * Legacy: server/log/{category}/create.php
     */
    public function create(Request $request, string $category)
    {
        $categoryId = self::CATEGORY_MAP[$category] ?? null;

        if (!$categoryId) {
            return response()->json(['success' => false, 'message' => 'Invalid category'], 400);
        }

        $value = $request->input('value');
        $device_id = $request->input('device_id');
        $time = $request->input('time');
        $nurse_presence = $request->input('nurse_presence');

        DB::table('log')->insert([
            'category_log_id' => $categoryId,
            'value'           => $value,
            'device_id'       => $device_id,
            'time'            => $time,
            'nurse_presence'  => $nurse_presence,
            'timestamp'       => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json(['success' => true]);
    }

    public function get(Request $request) 
    {
        $date = $request->input('date', date('Y-m-d'));

        $logs = DB::table('log')
            ->join('category_log', 'log.category_log_id', '=', 'category_log.id')
            ->leftJoin('bed', 'bed.id', '=', 'log.device_id')
            ->leftJoin('toilet', 'toilet.id', '=', 'log.device_id')
            ->select(
                'log.*',
                'category_log.name',
                DB::raw('coalesce(bed.username, toilet.username) as username')
            )
            ->whereDate('log.timestamp', $date)
            ->orderBy('log.timestamp', 'desc')
            ->get();

        // Convert the result to match the array format if needed, but Eloquent collection (or query builder result) serializes to JSON fine.
        // However, standard DB::select returns stdClass objects. The legacy code returns associative arrays but json_encode treats them similarly.

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    public function excel(Request $request) 
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $category = $request->input('category');
        
        return Excel::download(new MessagesExport($start_date, $end_date, $category), 'messages.xlsx');
    }

    public function pdf(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $category = $request->input('category');
        
        $query = Log::with(['category', 'bed.room']);

        if ($start_date && $end_date) {
            $query->whereBetween('timestamp', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        $category_name = null;
        if ($category) {
            $query->where('category_log_id', $category);
            $category_name = CategoryLog::find($category)?->name;
        }

        $logs = $query->orderBy('timestamp', 'desc')->get();
        $pdf = Pdf::loadView('admin.messages.pdf', compact('logs', 'start_date', 'end_date', 'category_name'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('messages.pdf');
    }
}
