<?php

namespace App\Exports;

use App\Models\AssetOutstationHistory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class OutstationHistoryExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AssetOutstationHistory::query()
            ->with([
                'assetInventory.assetType',
                'assetInventory.assetModel',
                'fromLocation',
                'fromStation',
                'toLocation',
                'toStation',
            ]);

        // Call Coordinator: restrict to permitted stations
        if (auth()->user()->role == 1) {
            $stationIds = permittedStationIds();

            $query->where(function ($q) use ($stationIds) {
                $q->whereIn('from_station_id', $stationIds)
                  ->orWhereIn('to_station_id', $stationIds);
            });
        }

        if (!empty($this->filters['tag_no'])) {
            $query->whereHas('assetInventory', function ($q) {
                $q->where(
                    'tag_no',
                    'LIKE',
                    '%' . $this->filters['tag_no'] . '%'
                );
            });
        }

        if (!empty($this->filters['serial_no'])) {
            $query->whereHas('assetInventory', function ($q) {
                $q->where(
                    'serial_no',
                    'LIKE',
                    '%' . $this->filters['serial_no'] . '%'
                );
            });
        }

        if (!empty($this->filters['asset_type'])) {
            $query->whereHas('assetInventory', function ($q) {
                $q->where('asset_type_id', $this->filters['asset_type']);
            });
        }

        if (!empty($this->filters['asset_model'])) {
            $query->whereHas('assetInventory', function ($q) {
                $q->where('asset_model_id', $this->filters['asset_model']);
            });
        }

        return $query
            ->orderByDesc('outstation_date')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'SL No.',
            'Asset Type',
            'Asset Model',
            'Asset Serial No.',
            'Asset Tag No.',
            'From Region',
            'From Airport/Station',
            'To Region',
            'To Airport/Station',
            'Outstation Date',
        ];
    }

    public function map($history): array
    {
        static $serial = 0;
        $serial++;

        return [
            $serial,
            $history->assetInventory->assetType->name ?? 'N/A',
            $history->assetInventory->assetModel->model_name ?? 'N/A',
            $history->assetInventory->serial_no ?? 'N/A',
            $history->assetInventory->tag_no ?? 'N/A',
            $history->fromLocation->name ?? 'N/A',
            $history->fromStation->station_name ?? 'N/A',
            $history->toLocation->name ?? 'N/A',
            $history->toStation->station_name ?? 'N/A',
            $history->outstation_date
                ? $history->outstation_date->format('d-m-Y')
                : 'N/A',
        ];
    }
}
