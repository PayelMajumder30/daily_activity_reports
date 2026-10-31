<?php

namespace App\Exports;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplaintExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Complaint::with('upload');

        if (!empty($this->filters['engineer'])) {
            $query->where('engineer_name', $this->filters['engineer']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['complaint'])) {
            $query->where(
                'complaint_title',
                'LIKE',
                '%' . $this->filters['complaint'] . '%'
            );
        }

        if (!empty($this->filters['from_date'])) {
            $query->whereHas('upload', function ($q) {
                $q->whereDate(
                    'report_date',
                    '>=',
                    $this->filters['from_date']
                );
            });
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereHas('upload', function ($q) {
                $q->whereDate(
                    'report_date',
                    '<=',
                    $this->filters['to_date']
                );
            });
        }

        // return $query->latest()->get();
        return $query->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'SL',
            'Activity Details',
            'Type of Activity',
            'Asset Tag No',
            'Engineer',
            'Status',
            'Activity Duration',
            'Report Date',
        ];
    }

    public function map($row): array
    {
        static $sl = 0;
        $sl++;

        return [
            $sl,
            $row->complaint_title,
            $row->type_of_activity ?: 'NA',
            $row->asset_tag_no ?: 'NA',
            $row->engineer_name,
            $row->status,
            $row->resolution_time ?: 'NA',
            optional($row->upload?->report_date)->format('d-m-Y') ?? '-',
        ];
    }
}
