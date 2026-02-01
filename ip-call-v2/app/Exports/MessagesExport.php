<?php

namespace App\Exports;

use App\Models\Log;
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
        $query = Log::query();

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
            'ID',
            'Category',
            'Value',
            'Device ID',
            'Time',
            'Nurse Presence',
            'Timestamp',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->category_log_id, // Ideally should be category name
            $log->value,
            $log->device_id,
            $log->time,
            $log->nurse_presence ? 'Yes' : 'No',
            $log->timestamp,
        ];
    }
}
