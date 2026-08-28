<?php

namespace App\Exports;

use App\Models\AssetInventory;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetInventoryExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query for Excel export
     */
    public function query()
    {
        return AssetInventory::with([
            'assetModel.assetType',
            'location',
            'station'
        ])
        ->when(
            $this->filters['tag_no'] ?? null,
            function (Builder $query, $tagNo) {
                $query->where(
                    'tag_no',
                    'LIKE',
                    '%' . $tagNo . '%'
                );
            }
        )
        ->when(
            $this->filters['po_number'] ?? null,
            function (Builder $query, $poNumber) {
                $query->where(
                    'po_number',
                    'LIKE',
                    '%' . $poNumber . '%'
                );
            }
        )
        ->when(
            $this->filters['serial_no'] ?? null,
            function (Builder $query, $serialNo) {
                $query->where(
                    'serial_no',
                    'LIKE',
                    '%' . $serialNo . '%'
                );
            }
        )
        ->when(
            $this->filters['asset_type'] ?? null,
            function (Builder $query, $assetType) {
                $query->whereHas(
                    'assetModel',
                    function ($q) use ($assetType) {
                        $q->where(
                            'asset_type_id',
                            $assetType
                        );
                    }
                );
            }
        )
        ->when(
            $this->filters['asset_model'] ?? null,
            function (Builder $query, $assetModel) {
                $query->where(
                    'asset_model_id',
                    $assetModel
                );
            }
        )
        ->when(
            $this->filters['installation_date'] ?? null,
            function (Builder $query, $installationDate) {

                $query->whereDate(
                    'installation_date',
                    $this->convertDate($installationDate)
                );

            }
        )
        ->latest('id');
    }

    /**
     * Excel headings
     */
    public function headings(): array
    {
        return [
            'SL',
            'Tag No.',
            'PO No.',
            'Asset Type',
            'Asset Model',
            'Serial No.',
            'Region',
            'Airport/Station',
            'Installation Date',
            'Warranty (Yrs)',
            'Warranty End Date',
            'Asset Status',
        ];
    }

    /**
     * Excel row data
     */
    public function map($item): array
    {
        return [
            $item->id,

            $item->tag_no ?? '-',

            $item->po_number ?? '-',

            // Asset Type
            ucwords(
                $item->assetModel?->assetType?->name ?? 'N/A'
            ),

             // Asset Model
            $item->assetModel?->model_name ?? 'N/A',

            // Serial No.
            $item->serial_no ?? 'N/A',

             // Region
            ucwords(
                $item->location?->name ?? 'N/A'
            ),

            // Airport / Station
            $item->station ? ucwords($item->station->station_name) : 'N/A',

            // Installation Date
            $item->installation_date
                ? $item->installation_date->format('d-m-Y')
                : 'N/A',

            // Warranty Years
            $item->warranty_year ?? 'N/A',

             // Warranty End Date
            $item->warranty_end
                ? $item->warranty_end->format('d-m-Y')
                : 'N/A',

            $item->asset_status ?? 'N/A',
        ];
    }

    /**
     * Convert DD-MM-YYYY to YYYY-MM-DD
     */
    private function convertDate($date)
    {
        try {

            return \Carbon\Carbon::createFromFormat(
                'd-m-Y',
                $date
            )->format('Y-m-d');

        } catch (\Throwable $e) {

            return $date;

        }
    }
}