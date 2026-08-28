<?php

namespace App\Imports;

use App\Models\AssetInventory;
use App\Models\AssetType;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\AirportStation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AssetInventoryImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $successCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // Excel actual row number
            $rowNumber = $index + 2;

            try {

                /*
                |--------------------------------------------------------------------------
                | Get values
                |--------------------------------------------------------------------------
                */

                $assetTypeId = trim((string) ($row['asset_type'] ?? ''));
                $assetModelId = trim((string) ($row['asset_model'] ?? ''));
                $serialNo = trim((string) ($row['asset_serial_no'] ?? ''));
                $tagNo = trim((string) ($row['asset_tag'] ?? ''));
                $locationId = trim((string) ($row['region'] ?? ''));
                // $stationId = trim((string) ($row['airport_station'] ?? ''));
                $stationId = trim((string) ( $row['airport_station'] ?? $row['airport_station_id'] ?? $row['airportstation']?? ''));
                $poNumber = trim((string) ($row['po_no'] ?? ''));
                $installationDate = trim((string) ($row['installation_date'] ?? ''));
                $warrantyYear = trim((string) ($row['warranty_yrs'] ?? ''));
                $warrantyEnd = trim((string) ($row['warranty_end_date'] ?? ''));
                $assetStatus = trim((string) ($row['asset_status'] ?? ''));

                /*
                |--------------------------------------------------------------------------
                | Skip completely empty rows
                |--------------------------------------------------------------------------
                */

                if (
                    $assetTypeId === '' &&
                    $assetModelId === '' &&
                    $serialNo === '' &&
                    $tagNo === ''
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Required fields
                |--------------------------------------------------------------------------
                */

                $required = [
                    'Asset Type' => $assetTypeId,
                    'Asset Model' => $assetModelId,
                    'Asset Serial No.' => $serialNo,
                    'Asset Tag' => $tagNo,
                    'Region' => $locationId,
                    'Airport/Station' => $stationId,
                    'PO NO' => $poNumber,
                    'Installation Date' => $installationDate,
                    'Warranty (Yrs)' => $warrantyYear,
                    'Warranty End Date' => $warrantyEnd,
                    'Asset Status' => $assetStatus,
                ];

                foreach ($required as $field => $value) {

                    if ($value === '') {

                        $this->errors[] =
                            "Row {$rowNumber}: {$field} is required.";

                        continue 2;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Asset Type
                |--------------------------------------------------------------------------
                */

                $assetType = AssetType::where('id', $assetTypeId)
                    ->where('status', 1)
                    ->first();

                if (!$assetType) {

                    $this->errors[] =
                        "Row {$rowNumber}: Invalid Asset Type ID {$assetTypeId}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Asset Model
                |--------------------------------------------------------------------------
                */

                $assetModel = AssetModel::where('id', $assetModelId)
                    ->where('status', 1)
                    ->where('asset_type_id', $assetTypeId)
                    ->first();

                if (!$assetModel) {

                    $this->errors[] =
                        "Row {$rowNumber}: Asset Model ID {$assetModelId} does not belong to Asset Type ID {$assetTypeId}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Location
                |--------------------------------------------------------------------------
                */

                $location = Location::where('id', $locationId)
                    ->where('status', 1)
                    ->first();

                if (!$location) {

                    $this->errors[] =
                        "Row {$rowNumber}: Invalid Region ID {$locationId}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Station
                |--------------------------------------------------------------------------
                */

                $station = AirportStation::where('id', $stationId)
                    ->where('location_id', $locationId)
                    ->where('status', 1)
                    ->first();

                if (!$station) {

                    $this->errors[] =
                        "Row {$rowNumber}: Airport/Station ID {$stationId} does not belong to Region ID {$locationId}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate Tag Check
                |--------------------------------------------------------------------------
                */

                if (AssetInventory::where('tag_no', $tagNo)->exists()) {

                    $this->errors[] =
                        "Row {$rowNumber}: Asset Tag {$tagNo} already exists.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate Tag inside Excel
                |--------------------------------------------------------------------------
                */

                $duplicateInExcel = AssetInventory::where('tag_no', $tagNo)
                    ->exists();

                if ($duplicateInExcel) {

                    $this->errors[] =
                        "Row {$rowNumber}: Asset Tag {$tagNo} is duplicated.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Dates
                |--------------------------------------------------------------------------
                */

                try {

                    $installationDate = Carbon::createFromFormat(
                        'd-m-Y',
                        $installationDate
                    )->format('Y-m-d');

                    $warrantyEnd = Carbon::createFromFormat(
                        'd-m-Y',
                        $warrantyEnd
                    )->format('Y-m-d');

                } catch (\Throwable $e) {

                    $this->errors[] =
                        "Row {$rowNumber}: Date must be in DD-MM-YYYY format.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Warranty
                |--------------------------------------------------------------------------
                */

                if (!is_numeric($warrantyYear) || $warrantyYear < 0) {

                    $this->errors[] =
                        "Row {$rowNumber}: Invalid Warranty (Yrs).";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Asset Status
                |--------------------------------------------------------------------------
                */

                $allowedStatuses = [
                    'Available',
                    'Assigned',
                    'Repair',
                    'Scrapped',
                ];

                if (!in_array($assetStatus, $allowedStatuses)) {

                    $this->errors[] =
                        "Row {$rowNumber}: Invalid Asset Status {$assetStatus}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Store
                |--------------------------------------------------------------------------
                */

                AssetInventory::create([

                    'tag_no'            => $tagNo,
                    'asset_type_id'     => $assetTypeId,
                    'asset_model_id'    => $assetModelId,
                    'location_id'       => $locationId,
                    'station_id'        => $stationId,
                    'po_number'         => $poNumber,
                    'serial_no'         => $serialNo,
                    'installation_date' => $installationDate,
                    'warranty_year'     => $warrantyYear,
                    'warranty_end'      => $warrantyEnd,
                    'asset_status'      => $assetStatus,
                    'created_by'        => Auth::id(),
                    'status'            => 1,

                ]);

                $this->successCount++;

            } catch (\Throwable $e) {

                $this->errors[] =
                    "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}