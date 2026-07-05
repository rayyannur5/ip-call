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
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            $request->merge([
                'start_date' => $request->input('start_date') ?: now()->startOfMonth()->toDateString(),
                'end_date' => $request->input('end_date') ?: now()->endOfMonth()->toDateString(),
            ]);
        }

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
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            $request->merge([
                'start_date' => $request->input('start_date') ?: now()->startOfMonth()->toDateString(),
                'end_date' => $request->input('end_date') ?: now()->endOfMonth()->toDateString(),
            ]);
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $category = $request->category;

        $filename = 'calls';
        if ($start_date && $end_date) {
            $filename .= '_' . $start_date . '_to_' . $end_date;
        } else {
            $filename .= '_' . date('Y-m-d');
        }

        if ($type == 'excel') {
            return Excel::download(new CallsExport($start_date, $end_date, $category), $filename . '.xlsx');
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
            return $pdf->download($filename . '.pdf');
        }

        return redirect()->back();
    }

    public function exportZip(Request $request)
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

        // 1. Get calls matching the filter criteria
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

        if ($calls->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data panggilan untuk diekspor.');
        }

        // 2. Create temporary path for compiling zip contents
        $tempDirName = 'export_zip_' . date('Ymd_His') . '_' . uniqid();
        $tempPath = storage_path('app/' . $tempDirName);
        \Illuminate\Support\Facades\File::makeDirectory($tempPath, 0777, true, true);

        // 3. Generate Excel and save to temp path
        $excelFilename = 'calls.xlsx';
        Excel::store(new CallsExport($start_date, $end_date, $category), $tempDirName . '/' . $excelFilename, 'local');

        // 4. Generate PDF and save to temp path
        $pdfFilename = 'calls.pdf';
        $pdfPath = $tempPath . '/' . $pdfFilename;
        $pdf = Pdf::loadView('admin.calls.pdf', compact('calls', 'start_date', 'end_date', 'category_name'))
            ->setPaper('a4', 'landscape');
        $pdf->save($pdfPath);

        // 5. Create records folder and copy audio files
        $recordsFolder = $tempPath . '/records';
        \Illuminate\Support\Facades\File::makeDirectory($recordsFolder, 0777, true, true);

        $hasRecords = false;
        foreach ($calls as $call) {
            if ($call->record) {
                $recordRelativePath = $call->record;
                $sourcePath = public_path($recordRelativePath);
                
                if (!\Illuminate\Support\Facades\File::exists($sourcePath)) {
                    $sourcePath = public_path('records/' . basename($recordRelativePath));
                }

                if (\Illuminate\Support\Facades\File::exists($sourcePath) && \Illuminate\Support\Facades\File::isFile($sourcePath)) {
                    $fileName = basename($sourcePath);
                    \Illuminate\Support\Facades\File::copy($sourcePath, $recordsFolder . '/' . $fileName);
                    $hasRecords = true;
                }
            }
        }

        // 6. Zip everything together
        $zipFilename = 'calls_export_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/temp/' . $zipFilename);
        
        \Illuminate\Support\Facades\File::makeDirectory(storage_path('app/temp'), 0777, true, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // Add Excel and PDF files
            $zip->addFile($tempPath . '/' . $excelFilename, $excelFilename);
            $zip->addFile($pdfPath, $pdfFilename);

            // Add records if they exist
            if ($hasRecords) {
                $files = \Illuminate\Support\Facades\File::files($recordsFolder);
                foreach ($files as $file) {
                    $zip->addFile($file->getPathname(), 'records/' . $file->getFilename());
                }
            }

            $zip->close();
        }

        // 7. Clean up the temporary folder
        \Illuminate\Support\Facades\File::deleteDirectory($tempPath);

        // 8. Download ZIP and delete after sending
        if (\Illuminate\Support\Facades\File::exists($zipFilePath)) {
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }
}
