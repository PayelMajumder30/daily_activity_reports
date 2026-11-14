<?php

namespace App\Exports;

use App\Models\AssetIssueRegister;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetIssueHistoryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $assetId;

    public function __construct($assetId)
    {
        $this->assetId = $assetId;
    }

    public function collection()
    {
        return AssetIssueRegister::with('custodian')
            ->where('asset_inventory_id', $this->assetId)
            ->orderBy('issued_date')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return [
                    'custodian_name' => $item->custodian?->custodian_name ?? '-',
                    'issue_status'   => ucwords($item->issue_status ?? '-'),
                    'issued_date'    => $item->issued_date?->format('d-m-Y') ?? '-',
                    'transfer_date'  => $item->transfer_date?->format('d-m-Y') ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Custodian Name',
            'Issue Status',
            'Issued Date',
            'Transfer Date',
        ];
    }
}