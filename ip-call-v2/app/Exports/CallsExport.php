<?php

namespace App\Exports;

use App\Models\History;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CallsExport implements FromCollection, WithHeadings, WithMapping
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
        $query = History::with(['category', 'bed.room']);

        if ($this->start_date && $this->end_date) {
            $query->whereBetween('timestamp', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59']);
        }

        if ($this->category) {
            $query->where('category_history_id', $this->category);
        }

        return $query->orderBy('timestamp', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Timestamp',
            'Nama Ruang',
            'Kategori',
            'Duration',
            'Record',
        ];
    }

    public function map($call): array
    {
        return [
            Carbon::parse($call->timestamp)->timezone('Asia/Jakarta')->format('d M Y H:i:s'),
            $call->bed->username ?? '-',
            $call->category->name ?? '-',
            $call->duration,
            $call->record,
        ];
    }
}
