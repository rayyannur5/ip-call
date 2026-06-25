<?php

namespace App\Exports;

use App\Models\OxiMonitorLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OxiMonitorExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $start_date;
    protected $end_date;
    protected $no = 1;

    // Conversion factors from resources/views/admin/oximonitor/index.blade.php
    // m3: 1, galon: 0.2982, liter: 1.1288, kg: 1.2876
    protected $units = [
        'm3' => 1,
        'galon' => 0.2982,
        'liter' => 1.1288,
        'kg' => 1.2876
    ];

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
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

    public function collection()
    {
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        if (! $startDate || ! $endDate) {
            $dateBoundsQuery = OxiMonitorLog::selectRaw('MIN(DATE(created_at)) as start_date, MAX(DATE(created_at)) as end_date')
                ->first();

            $startDate = $startDate ?: ($dateBoundsQuery->start_date ?? null);
            $endDate = $endDate ?: ($dateBoundsQuery->end_date ?? null);
        }

        if (! $startDate || ! $endDate) {
            return new Collection([]);
        }

        $dates = OxiMonitorLog::selectRaw('DISTINCT DATE(created_at) as log_date')
            ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate])
            ->orderBy('log_date', 'desc')
            ->pluck('log_date');

        $rows = [];
        foreach ($dates as $date) {
            $usage = $this->getUsageBetween($date, $date);
            $rows[] = (object) [
                'date' => $date,
                'usage_m3' => $usage,
            ];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Pemakaian (m³)',
            'Pemakaian (Galon)',
            'Pemakaian (Liter)',
            'Pemakaian (Kg)',
        ];
    }

    public function map($row): array
    {
        $usage = $row->usage_m3;
        return [
            $this->no++,
            $this->formatIndonesianDate($row->date),
            $usage,
            $usage * $this->units['galon'],
            $usage * $this->units['liter'],
            $usage * $this->units['kg'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
