<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryHistory;
use App\Models\History;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CallsExport;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $category = $request->input('category');

        return Excel::download(new CallsExport($start_date, $end_date, $category), 'calls.xlsx');
    }

    /**
     * Export history to PDF
     */
    public function pdf(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $category = $request->input('category');

        $query = History::with(['category', 'bed']);

        if ($start_date && $end_date) {
            $query->whereBetween('timestamp', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        $category_name = null;
        if ($category) {
            $query->where('category_history_id', $category);
            $category_name = CategoryHistory::find($category)?->name;
        }

        $calls = $query->orderBy('timestamp', 'desc')->get();
        $pdf = Pdf::loadView('admin.calls.pdf', compact('calls', 'start_date', 'end_date', 'category_name'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('calls.pdf');
    }

    /**
     * Get list of audio filenames for download
     * GET /server/history/list_audio.php?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
     */
    public function list_audio(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = DB::table('history')
            ->whereNotNull('record')
            ->where('record', '!=', '');

        if ($start_date && $end_date) {
            $query->whereBetween('timestamp', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        $records = $query->pluck('record')->toArray();

        // Convert "records/filename.wav" to "filename.wav"
        $filenames = array_map(function($record) {
            return basename($record);
        }, $records);
        
        // Remove empty filenames if any
        $filenames = array_filter($filenames);

        return response()->json(array_values($filenames));
    }
}
