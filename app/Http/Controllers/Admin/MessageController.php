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
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            $request->merge([
                'start_date' => $request->input('start_date') ?: now()->startOfMonth()->toDateString(),
                'end_date' => $request->input('end_date') ?: now()->endOfMonth()->toDateString(),
            ]);
        }

        $query = Log::with(['category', 'bed.room']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('timestamp', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->category) {
            $query->where('category_log_id', $request->category);
        }

        if ($request->filled('nurse_presence')) {
            if ($request->nurse_presence == '1') {
                $query->where('nurse_presence', 1);
            } else {
                $query->where(function($q) {
                    $q->where('nurse_presence', '!=', 1)
                      ->orWhereNull('nurse_presence');
                });
            }
        }

        // Calculate average response time for logs with nurse presence
        $averageResponseTime = (clone $query)->where('nurse_presence', 1)
            ->whereNotNull('time')
            ->avg('time');

        // Count messages with and without response times
        $hasResponseCount = (clone $query)->where('nurse_presence', 1)->count();
        $noResponseCount = (clone $query)->where(function($q) {
            $q->where('nurse_presence', '!=', 1)
              ->orWhereNull('nurse_presence');
        })->count();

        $logs = $query->orderBy('timestamp', 'desc')->paginate(10);
        $categories = CategoryLog::all();

        return view('admin.messages.index', compact(
            'logs', 
            'categories', 
            'averageResponseTime', 
            'hasResponseCount', 
            'noResponseCount'
        ));
    }

    public function export(Request $request, $type)
    {
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            $request->merge([
                'start_date' => $request->input('start_date') ?: now()->startOfMonth()->toDateString(),
                'end_date' => $request->input('end_date') ?: now()->endOfMonth()->toDateString(),
            ]);
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $category = $request->category;
        $nurse_presence = $request->nurse_presence;

        $filename = 'messages';
        if ($start_date && $end_date) {
            $filename .= '_' . $start_date . '_to_' . $end_date;
        } else {
            $filename .= '_' . date('Y-m-d');
        }

        if ($type == 'excel') {
            return Excel::download(new MessagesExport($start_date, $end_date, $category, $nurse_presence), $filename . '.xlsx');
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

            if ($request->filled('nurse_presence')) {
                if ($request->nurse_presence == '1') {
                    $query->where('nurse_presence', 1);
                } else {
                    $query->where(function($q) {
                        $q->where('nurse_presence', '!=', 1)
                          ->orWhereNull('nurse_presence');
                    });
                }
            }

            $logs = $query->orderBy('timestamp', 'desc')->get();
            $pdf = Pdf::loadView('admin.messages.pdf', compact('logs', 'start_date', 'end_date', 'category_name'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        return redirect()->back();
    }
}
