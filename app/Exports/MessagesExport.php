<?php

namespace App\Exports;

use App\Models\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MessagesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $start_date;
    protected $end_date;
    protected $category;

    public function __construct($start_date, $end_date, $category)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->category = $category;
    }

    public function collection()
    {
        $query = Log::with(['category', 'bed.room']);

        if ($this->start_date && $this->end_date) {
            $query->whereBetween('timestamp', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59']);
        }

        if ($this->category) {
            $query->where('category_log_id', $this->category);
        }

        return $query->orderBy('timestamp', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Nama Ruang',
            'Time',
            'Nurse Presence',
        ];
    }

    public function map($log): array
    {
        return [
            Carbon::parse($log->timestamp)->timezone('Asia/Jakarta')->format('d M Y H:i:s'),
            $log->category->name ?? '-',
            $log->bed->username ?? '-',
            $log->nurse_presence ? \Carbon\CarbonInterval::seconds($log->time)->cascade()->locale('id')->forHumans() : '0 detik',
            $log->nurse_presence ? 'Yes' : 'No',
        ];
    }
}
