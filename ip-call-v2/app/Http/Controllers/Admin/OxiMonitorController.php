<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OxiMonitorLog;
use App\Models\OxiMonitorStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OxiMonitorController extends Controller
{
    public function index()
    {
        return view('admin.oximonitor.index');
    }

    /**
     * Get metrics data for AJAX
     */
    public function metrics()
    {
        // Get current flow rate
        $status = OxiMonitorStatus::first();
        $currentFlow = $status ? floatval($status->flow_rate) : 0;

        // Helper function to get volume at a specific date
        $getVolumeAtDate = function($date) {
            $log = OxiMonitorLog::whereDate('created_at', '<=', $date)
                ->orderBy('created_at', 'desc')
                ->first();
            return $log ? floatval($log->volume) : 0;
        };

        // Get latest volume
        $latestLog = OxiMonitorLog::orderBy('created_at', 'desc')->first();
        $latestVolume = $latestLog ? floatval($latestLog->volume) : 0;

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $threeDaysAgo = now()->subDays(3)->format('Y-m-d');
        $sevenDaysAgo = now()->subDays(7)->format('Y-m-d');
        $fourteenDaysAgo = now()->subDays(14)->format('Y-m-d');
        $thirtyDaysAgo = now()->subDays(30)->format('Y-m-d');

        $volYesterdayEnd = $getVolumeAtDate($yesterday);
        $usageToday = $latestVolume - $volYesterdayEnd;

        $vol3DaysAgo = $getVolumeAtDate($threeDaysAgo);
        $usage3Days = $latestVolume - $vol3DaysAgo;

        $vol7DaysAgo = $getVolumeAtDate($sevenDaysAgo);
        $usage7Days = $latestVolume - $vol7DaysAgo;

        $vol14DaysAgo = $getVolumeAtDate($fourteenDaysAgo);
        $usage14Days = $latestVolume - $vol14DaysAgo;

        $vol30DaysAgo = $getVolumeAtDate($thirtyDaysAgo);
        $usage30Days = $latestVolume - $vol30DaysAgo;

        $avg3Days = $usage3Days / 3;
        $avg7Days = $usage7Days / 7;

        // Format numbers (Indonesian format: comma as decimal, dot as thousand separator)
        $fmt = function($num) {
            return number_format($num, 2, ',', '.');
        };

        return response()->json([
            'usage_today' => $fmt($usageToday),
            'usage_3_days' => $fmt($usage3Days),
            'usage_7_days' => $fmt($usage7Days),
            'avg_3_days' => $fmt($avg3Days),
            'avg_7_days' => $fmt($avg7Days),
            'usage_14_days' => $fmt($usage14Days),
            'usage_30_days' => $fmt($usage30Days),
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
            'current_flow' => number_format($currentFlow, 2, ',', '.')
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
        
        $dates = $datesQuery->orderBy('log_date', 'desc')
            ->offset($start)
            ->limit($limit)
            ->pluck('log_date');

        $data = [];
        $no = $start + 1;

        // Helper function to get volume at date
        $getVolumeAtDate = function($date) {
            $log = OxiMonitorLog::whereDate('created_at', '<=', $date)
                ->orderBy('created_at', 'desc')
                ->first();
            return $log ? floatval($log->volume) : 0;
        };

        // Indonesian month names
        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];

        foreach ($dates as $date) {
            $prevDate = date('Y-m-d', strtotime($date . ' -1 day'));

            $volToday = $getVolumeAtDate($date);
            $volYesterday = $getVolumeAtDate($prevDate);

            $usage = $volToday - $volYesterday;
            $usageFmt = number_format($usage, 2, ',', '.');

            // Format date to Indonesian
            $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
            $monthName = $dateObj->format('F');
            $dateFmt = $dateObj->format('d') . ' ' . ($months[$monthName] ?? $monthName) . ' ' . $dateObj->format('Y');

            $data[] = [
                $no++,
                $dateFmt,
                $usageFmt
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }
}
