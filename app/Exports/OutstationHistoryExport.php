<?php

namespace App\Exports;

use App\Models\AssetOutstationHistory;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OutstationHistoryExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected array $filters;

    protected int $sl = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = AssetOutstationHistory::with([
            'assetInventory.assetType',
            'assetInventory.assetModel',
            'fromLocation',
            'fromStation',
            'toLocation',
            'toStation',
            'createdBy',
        ]);

        // Call Coordinator: restrict history to permitted stations
        if (auth()->user()->role == 1) {
            $stationIds = permittedStationIds();

            $query->where(function ($q) use ($stationIds) {
                $q->whereIn('from_station_id', $stationIds)
                    ->orWhereIn('to_station_id', $stationIds);
            });
        }

        // Search by Tag No.
        if (!empty($this->filters['tag_no'])) {
            $tagNo = trim($this->filters['tag_no']);

            $query->whereHas('assetInventory', function ($q) use ($tagNo) {
                $q->where('tag_no', 'LIKE', "%{$tagNo}%");
            });
        }

        // Search by Serial No.
        if (!empty($this->filters['serial_no'])) {
            $serialNo = trim($this->filters['serial_no']);

            $query->whereHas('assetInventory', function ($q) use ($serialNo) {
                $q->where('serial_no', 'LIKE', "%{$serialNo}%");
            });
        }

        // Filter by Asset Type
        if (!empty($this->filters['asset_type'])) {
            $assetType = $this->filters['asset_type'];

            $query->whereHas('assetInventory', function ($q) use ($assetType) {
                $q->where('asset_type_id', $assetType);
            });
        }

        // Filter by Asset Model
        if (!empty($this->filters['asset_model'])) {
            $assetModel = $this->filters['asset_model'];

            $query->whereHas('assetInventory', function ($q) use ($assetModel) {
                $q->where('asset_model_id', $assetModel);
            });
        }

        // Filter by From Location
        if (!empty($this->filters['from_location_id'])) {
            $query->where(
                'from_location_id',
                $this->filters['from_location_id']
            );
        }

        // Filter by From Station
        if (!empty($this->filters['from_station_id'])) {
            $query->where(
                'from_station_id',
                $this->filters['from_station_id']
            );
        }

        // Filter by To Location
        if (!empty($this->filters['to_location_id'])) {
            $query->where(
                'to_location_id',
                $this->filters['to_location_id']
            );
        }

        // Filter by To Station
        if (!empty($this->filters['to_station_id'])) {
            $query->where(
                'to_station_id',
                $this->filters['to_station_id']
            );
        }

        return $query
            ->orderBy('outstation_date')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'SL',
            'Asset Tag',
            'Serial No',
            'Asset Type',
            'Asset Model',
            'Outstation Date',
            'From Region',
            'From Station',
            'To Region',
            'To Station',
        ];
    }

    public function map($item): array
    {
        return [
            ++$this->sl,
            $item->assetInventory?->tag_no ?? '-',
            $item->assetInventory?->serial_no ?? '-',
            $item->assetInventory?->assetType?->name ?? '-',
            $item->assetInventory?->assetModel?->model_name ?? '-',
            $item->outstation_date?->format('d-m-Y') ?? '-',
            $item->fromLocation?->name ?? '-',
            $item->fromStation?->station_name ?? '-',
            $item->toLocation?->name ?? '-',
            $item->toStation?->station_name ?? '-',
        ];
    }
}