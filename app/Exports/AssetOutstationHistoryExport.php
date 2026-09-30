<?php

namespace App\Exports;

use App\Models\AssetOutstationHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetOutstationHistoryExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
    {
        protected int $assetInventoryId;

        public function __construct(int $assetInventoryId)
        {
            $this->assetInventoryId = $assetInventoryId;
        }

        public function collection(): Collection
        {
            $history = AssetOutstationHistory::with([
                'assetInventory.assetModel.assetType',
                'fromLocation',
                'fromStation',
                'toLocation',
                'toStation',
            ])
            ->where(
                'asset_inventory_id',
                $this->assetInventoryId
            )
            ->orderBy('outstation_date')
            ->orderBy('id')
            ->get();

            return $history->map(function ($item, $index) {

                return [

                    'SL' => $index + 1,
                    'Asset Tag' => $item->assetInventory?->tag_no ?? '-',                   
                    'Serial No' => $item->assetInventory?->serial_no ?? '-',                  
                      'Asset Type' =>
                    $item->assetInventory
                        ?->assetModel
                        ?->assetType
                        ?->name ?? '-',

                'Asset Model' =>
                    $item->assetInventory
                        ?->assetModel
                        ?->model_name ?? '-',

                'Outstation Date'   => $item->outstation_date?->format('d-m-Y') ?? '-',                   
                'From Region'       => $item->fromLocation?->name ?? '-',                 
                'From Station'      => $item->fromStation?->station_name ?? '-',                  
                'To Region'         => $item->toLocation?->name ?? '-',                  
                'To Station'        => $item->toStation?->station_name ?? '-',                                                                             
                ];
            });
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
    }