<?php

namespace App\Exports;

use App\Models\History;
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
        $query = History::query();

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
            'ID',
            'Bed ID',
            'Category',
            'Duration',
            'Record',
            'Timestamp',
        ];
    }

    public function map($call): array
    {
        return [
            $call->id,
            $call->bed_id,
            $call->category_history_id, // Ideally should be category name if relation exists
            $call->duration,
            $call->record,
            $call->timestamp,
        ];
    }
}
