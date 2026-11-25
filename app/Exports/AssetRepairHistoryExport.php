<?php

namespace App\Exports;

use App\Models\{AssetInventory, AssetRepairHistory};
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
        $asset = AssetInventory::with('assetModel')->find($this->assetId);
            
        $tagNo = $asset?->tag_no ?? '-';
        $assetModel = $asset?->assetModel?->model_name ?? '-';

        return AssetRepairHistory::with([
                'incidentIssueRegister.supportUser'
            ])
            ->where('asset_inventory_id', $this->assetId)
            ->orderBy('send_date')
            ->orderBy('id')
            ->get()
            ->map(function ($item) use ($tagNo, $assetModel) {

                $incident = $item->incidentIssueRegister;

                return [
                    'tag_no'       => $tagNo,
                    'asset_model'  => $assetModel,
                    'call_id'      => $incident?->call_id ?? '-',
                    'engineer_name'=> ucwords($incident?->supportUser?->name ?? '-'),
                    'vendor_name'  => ucwords($item->vendor_name ?? '-'),
                    'send_date'    => $item->send_date?->format('d-m-Y') ?? '-',
                    'return_date'  => $item->return_date?->format('d-m-Y') ?? '-',
                    'remarks'      => $item->remarks ?? $incident?->remarks ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Asset Tag No.',
            'Asset Model',
            'Call ID',
            'Engineer Name',
            'Vendor Name',
            'Send Date',
            'Return Date',
            'Remarks',
        ];
    }
}