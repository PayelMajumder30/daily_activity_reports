<?php

namespace App\Exports;

use App\Models\AssetRepairHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetRepairHistoryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $assetId;

    public function __construct($assetId)
    {
        $this->assetId = $assetId;
    }

    public function collection()
    {
        return AssetRepairHistory::with([
                'incidentIssueRegister.supportUser'
            ])
            ->where('asset_inventory_id', $this->assetId)
            ->orderBy('send_date')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                $incident = $item->incidentIssueRegister;

                return [
                    'call_id'       => $incident?->call_id ?? '-',
                    'engineer_name' => ucwords($incident?->supportUser?->name ?? '-'),
                    'vendor_name'   => ucwords($item->vendor_name ?? '-'),
                    'send_date'     => $item->send_date?->format('d-m-Y') ?? '-',
                    'return_date'   => $item->return_date?->format('d-m-Y') ?? '-',
                    'remarks'       => $item->remarks ?? $incident?->remarks ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Call ID',
            'Engineer Name',
            'Vendor Name',
            'Send Date',
            'Return Date',
            'Remarks',
        ];
    }
}