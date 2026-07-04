<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LogExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $start = date("{$this->start_date} 00:00:00");
        $end = date("{$this->end_date} 23:59:59");

        return DB::table('log')
            ->join('category_log', 'log.category_log_id', '=', 'category_log.id')
            ->leftJoin('bed', 'bed.id', '=', 'log.device_id')
            ->leftJoin('toilet', 'toilet.id', '=', 'log.device_id')
            ->select(
                'log.id',
                'category_log.name',
                DB::raw('coalesce(bed.username, toilet.username) as username'),
                'log.time',
                'log.nurse_presence',
                'log.timestamp'
            )
            ->whereBetween('log.timestamp', [$start, $end])
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'Kategori',
            'Ruang',
            'Waktu',
            'Kehadiran',
            'timestamp',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->name,
            $log->username,
            gmdate('H:i:s', $log->time), // Equivalent to sec_to_time
            $log->nurse_presence == 1 ? 'Ya' : 'Tidak',
            $log->timestamp,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
