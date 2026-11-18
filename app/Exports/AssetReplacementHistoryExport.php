<?php

namespace App\Exports;

use App\Models\AssetReplacementHistory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetReplacementHistoryExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AssetReplacementHistory::query()
            ->with([
                'assetInventory.assetType',
                'assetInventory.location',
                'assetInventory.station',
                'oldAssetModel',
                'newAssetModel',
            ]);

        // Restrict Call Coordinator to permitted stations
        if (auth()->check() && auth()->user()->role == 1) {
            $stationIds = permittedStationIds();

            $query->whereHas('assetInventory', function ($q) use ($stationIds) {
                $q->whereIn('station_id', $stationIds);
            });
        }

        // Filter by Tag No.
        if (!empty($this->filters['tag_no'])) {
            $tagNo = trim($this->filters['tag_no']);

            $query->whereHas('assetInventory', function ($q) use ($tagNo) {
                $q->where('tag_no', 'like', '%' . $tagNo . '%');
            });
        }

        // Filter by Asset Type
        if (!empty($this->filters['asset_type'])) {
            $assetTypeId = $this->filters['asset_type'];

            $query->whereHas('assetInventory', function ($q) use ($assetTypeId) {
                $q->where('asset_type_id', $assetTypeId);
            });
        }

        return $query->orderByDesc('replaced_at');
    }

    public function headings(): array
    {
        return [
            'SL No.',
            'Asset Tag No.',
            'Asset Type',
            'Location',
            'Station',
            'Old Model',
            'New Model',
            'Old Serial No.',
            'New Serial No.',
            'Replacement Date',
            'Remarks',
        ];
    }

    public function map($history): array
    {
        static $sl = 0;
        $sl++;

        return [
            $sl,
            $history->assetInventory->tag_no ?? 'N/A',
            $history->assetInventory->assetType->name ?? 'N/A',
            $history->assetInventory->location->name ?? 'N/A',
            $history->assetInventory->station->station_name ?? 'N/A',
            $history->oldAssetModel->model_name ?? 'N/A',
            $history->newAssetModel->model_name ?? 'N/A',
            $history->old_serial_no ?: 'N/A',
            $history->new_serial_no ?: 'N/A',
            $history->replaced_at
                ? $history->replaced_at->format('d-m-Y')
                : 'N/A',
            $history->remarks ?: 'N/A',
        ];
    }
}
