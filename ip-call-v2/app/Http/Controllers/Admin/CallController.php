<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryHistory;
use App\Models\History;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CallsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class CallController extends Controller
{
    public function index(Request $request)
    {
        $query = History::with(['category', 'bed']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('timestamp', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->category) {
            $query->where('category_history_id', $request->category);
        }

        $calls = $query->orderBy('timestamp', 'desc')->paginate(10);
        $categories = CategoryHistory::all();

        return view('admin.calls.index', compact('calls', 'categories'));
    }

    public function export(Request $request, $type)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $category = $request->category;

        if ($type == 'excel') {
            return Excel::download(new CallsExport($start_date, $end_date, $category), 'calls.xlsx');
        } elseif ($type == 'pdf') {
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

        return redirect()->back();
    }
}
