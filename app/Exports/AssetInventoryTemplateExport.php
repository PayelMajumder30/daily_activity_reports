<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class AssetInventoryTemplateExport implements FromArray
{
    public function array(): array
    {
        return [

            [
                'SL',
                'Asset Type',
                'Asset Model',
                'Asset Serial No.',
                'Asset Tag',
                'Region',
                'Airport/Station',
                'PO NO',
                'Installation Date',
                'Warranty (Yrs)',
                'Warranty End Date',
                'Asset Status',
            ],

        ];
    }
}