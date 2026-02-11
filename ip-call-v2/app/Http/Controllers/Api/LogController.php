<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date = $request->input('end_date', date('Y-m-d'));
        
        $filename = "EXCEL_LOG_" . date("Y-m-d H:i:s") . ".xlsx";
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LogExport($start_date, $end_date), $filename);
    }

    public function pdf(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        $start = date("{$start_date} 00:00:00");
        $end = date("{$end_date} 23:59:59");

        $logs = DB::table('log')
            ->join('category_log', 'log.category_log_id', '=', 'category_log.id')
            ->leftJoin('bed', 'bed.id', '=', 'log.device_id')
            ->leftJoin('toilet', 'toilet.id', '=', 'log.device_id')
            ->select(
                'log.id',
                'category_log.name',
                DB::raw('coalesce(bed.username, toilet.username) as username'),
                DB::raw('SEC_TO_TIME(log.time) as time'),
                DB::raw("case when log.nurse_presence = 1 then 'Ya' else 'Tidak' end as presence"),
                'log.timestamp'
            )
            ->whereBetween('log.timestamp', [$start, $end])
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.log', ['logs' => $logs]);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "PDF_LOG_" . date("Y-m-d_H.i.s") . ".pdf";
        return $pdf->stream($filename);
    }
}
