<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OxiMonitorLog;
use App\Models\OxiMonitorStatus;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OxiMonitorExport;

class OxiMonitorController extends Controller
{
    private function formatIndonesianDate($date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);

        if (! $dateObj) {
            return $date ?: '-';
        }

        $monthName = $dateObj->format('F');

        return $dateObj->format('d') . ' ' . ($months[$monthName] ?? $monthName) . ' ' . $dateObj->format('Y');
    }

    private function getUsageBetween($startDate, $endDate): float
    {
        $range = [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ];

        $firstLog = OxiMonitorLog::whereBetween('created_at', $range)
            ->orderBy('created_at', 'asc')
            ->first();
        $lastLog = OxiMonitorLog::whereBetween('created_at', $range)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $firstLog || ! $lastLog) {
            return 0;
        }

        return floatval($lastLog->volume) - floatval($firstLog->volume);
    }

    public function index()
    {
        return view('admin.oximonitor.index');
    }

    public function standalone()
    {
        return view('admin.oximonitor.index', [
            'layout' => 'layouts.oximonitor',
            'oximonitorUrls' => [
                'metrics' => url('/oximonitor/metrics'),
                'currentFlow' => url('/oximonitor/current-flow'),
                'data' => url('/oximonitor/data'),
                'export' => url('/oximonitor/export'),
            ],
        ]);
    }

    /**
     * Get metrics data for AJAX
     */
    public function metrics()
    {
        // Get current flow rate
        $status = OxiMonitorStatus::first();
        $currentFlow = $status ? floatval($status->flow_rate) : 0;

        $today = now()->format('Y-m-d');
        $threeDaysStart = now()->subDays(2)->format('Y-m-d');
        $sevenDaysStart = now()->subDays(6)->format('Y-m-d');
        $fourteenDaysStart = now()->subDays(13)->format('Y-m-d');
        $thirtyDaysStart = now()->subDays(29)->format('Y-m-d');

        $usageToday = $this->getUsageBetween($today, $today);
        $usage3Days = $this->getUsageBetween($threeDaysStart, $today);
        $usage7Days = $this->getUsageBetween($sevenDaysStart, $today);
        $usage14Days = $this->getUsageBetween($fourteenDaysStart, $today);
        $usage30Days = $this->getUsageBetween($thirtyDaysStart, $today);

        $avg3Days = $usage3Days / 3;
        $avg7Days = $usage7Days / 7;

        // Format numbers (Indonesian format: comma as decimal, dot as thousand separator)
        $fmt = function($num) {
            return number_format($num, 3, ',', '.');
        };

        return response()->json([
            'usage_today' => $fmt($usageToday),
            'usage_3_days' => $fmt($usage3Days),
            'usage_7_days' => $fmt($usage7Days),
            'avg_3_days' => $fmt($avg3Days),
            'avg_7_days' => $fmt($avg7Days),
            'usage_14_days' => $fmt($usage14Days),
            'usage_30_days' => $fmt($usage30Days),
            'raw' => [
                'usage_today' => $usageToday,
                'usage_3_days' => $usage3Days,
                'usage_7_days' => $usage7Days,
                'avg_3_days' => $avg3Days,
                'avg_7_days' => $avg7Days,
                'usage_14_days' => $usage14Days,
                'usage_30_days' => $usage30Days,
            ],
        ]);
    }

    /**
     * Get only current flow rate for high-frequency updates
     */
    public function currentFlow()
    {
        $status = OxiMonitorStatus::first();
        $currentFlow = $status ? floatval($status->flow_rate) : 0;

        return response()->json([
            'current_flow' => number_format($currentFlow, 3, ',', '.')
        ]);
    }

    /**
     * Get log data for DataTables (server-side processing)
     */
    public function getData(Request $request)
    {
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $draw = intval($request->input('draw', 1));
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Base query for total records
        $totalRecords = OxiMonitorLog::selectRaw('COUNT(DISTINCT DATE(created_at)) as total')
            ->value('total') ?? 0;

        // Query builder for filtered records
        $query = OxiMonitorLog::query();
        
        if ($startDate && $endDate) {
            $query->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate]);
        }

        $filteredRecords = $query->selectRaw('COUNT(DISTINCT DATE(created_at)) as total')
            ->value('total') ?? 0;

        // Get distinct dates for pagination
        $datesQuery = OxiMonitorLog::selectRaw('DISTINCT DATE(created_at) as log_date');
        
        if ($startDate && $endDate) {
            $datesQuery->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate]);
        }
        
        $orderDir = 'desc';
        if ($request->has('order')) {
            $order = $request->input('order');
            if (isset($order[0]['column']) && $order[0]['column'] == 1) {
                $orderDir = ($order[0]['dir'] === 'asc') ? 'asc' : 'desc';
            }
        }
        
        $dates = $datesQuery->orderBy('log_date', $orderDir)
            ->offset($start)
            ->limit($limit)
            ->pluck('log_date');

        $data = [];
        $no = $start + 1;

        $summaryStartDate = $startDate;
        $summaryEndDate = $endDate;

        if (! $summaryStartDate || ! $summaryEndDate) {
            $dateBoundsQuery = OxiMonitorLog::selectRaw('MIN(DATE(created_at)) as start_date, MAX(DATE(created_at)) as end_date')
                ->first();

            $summaryStartDate = $summaryStartDate ?: ($dateBoundsQuery->start_date ?? null);
            $summaryEndDate = $summaryEndDate ?: ($dateBoundsQuery->end_date ?? null);
        }

        $summaryUsage = 0;

        if ($summaryStartDate && $summaryEndDate) {
            $summaryUsage = $this->getUsageBetween($summaryStartDate, $summaryEndDate);
        }

        foreach ($dates as $date) {
            $usage = $this->getUsageBetween($date, $date);
            $usageFmt = number_format($usage, 3, ',', '.');

            $data[] = [
                'no' => $no++,
                'date' => $this->formatIndonesianDate($date),
                'usage' => $usageFmt,
                'usage_raw' => $usage,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
            'summary' => [
                'start_date' => $summaryStartDate,
                'end_date' => $summaryEndDate,
                'start_date_label' => $this->formatIndonesianDate($summaryStartDate),
                'end_date_label' => $this->formatIndonesianDate($summaryEndDate),
                'total_usage' => $summaryUsage,
                'days' => $filteredRecords,
            ],
        ]);
    }

    /**
     * Export log data to Excel
     */
    public function export(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $filename = 'oximonitor_logs';
        if ($startDate && $endDate) {
            $filename .= '_' . $startDate . '_to_' . $endDate;
        } else {
            $filename .= '_' . date('Y-m-d');
        }

        return Excel::download(new OxiMonitorExport($startDate, $endDate), $filename . '.xlsx');
    }
}
