<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryLog;
use App\Models\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MessagesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with(['category', 'bed.room']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('timestamp', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->category) {
            $query->where('category_log_id', $request->category);
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(10);
        $categories = CategoryLog::all();

        return view('admin.messages.index', compact('logs', 'categories'));
    }

    public function export(Request $request, $type)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $category = $request->category;

        if ($type == 'excel') {
            return Excel::download(new MessagesExport($start_date, $end_date, $category), 'messages.xlsx');
        } elseif ($type == 'pdf') {
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

        return redirect()->back();
    }
}
