<?php

namespace App\Imports;

use App\Models\{AssetInventory, AssetModel, AirportStation};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AssetInventoryImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $successCount = 0;
    private array $excelTags = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'Excel file does not contain any data.';
            return;
        }

        $hasData = false;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            // Normalize array keys / handle slashes gracefully
            $assetModelId = trim((string) ($row['asset_model'] ?? ''));
            $serialNo     = trim((string) ($row['asset_serial_no'] ?? ''));
            $tagNo        = trim((string) ($row['asset_tag'] ?? ''));
            
            // Checks 'airport_station' or fallback to 'airportstation' if slash was removed
            $stationId    = trim((string) ($row['airport_station'] ?? $row['airportstation'] ?? ''));
            
            $poNumber         = trim((string) ($row['po_no'] ?? ''));
            $installationDate = $row['installation_date'] ?? null;
            $warrantyYear     = trim((string) ($row['warranty_yrs'] ?? ''));
            $warrantyEnd      = $row['warranty_end_date'] ?? null;
            $assetStatus      = trim((string) ($row['asset_status'] ?? ''));

            // Skip completely empty rows
            if (
                empty($assetModelId) && empty($serialNo) && empty($tagNo) &&
                empty($stationId) && empty($poNumber) && empty($installationDate) &&
                empty($warrantyYear) && empty($warrantyEnd) && empty($assetStatus)
            ) {
                continue;
            }

            $hasData = true;

            // Required Validations
            if (empty($assetModelId)) {
                $this->errors[] = "Row {$rowNumber}: Asset Model ID is required.";
                continue;
            }

            if (empty($serialNo)) {
                $this->errors[] = "Row {$rowNumber}: Asset Serial No. is required.";
                continue;
            }

            if (empty($tagNo)) {
                $this->errors[] = "Row {$rowNumber}: Asset Tag is required.";
                continue;
            }

            if (empty($stationId)) {
                $this->errors[] = "Row {$rowNumber}: Airport/Station ID is required.";
                continue;
            }

            if (empty($poNumber)) {
                $this->errors[] = "Row {$rowNumber}: PO NO is required.";
                continue;
            }

            if (empty($installationDate)) {
                $this->errors[] = "Row {$rowNumber}: Installation Date is required.";
                continue;
            }

            if (empty($warrantyYear)) {
                $this->errors[] = "Row {$rowNumber}: Warranty (Yrs) is required.";
                continue;
            }

            if (empty($warrantyEnd)) {
                $this->errors[] = "Row {$rowNumber}: Warranty End Date is required.";
                continue;
            }

            // Validate Model
            $assetModel = AssetModel::where('id', $assetModelId)
                ->where('status', 1)
                ->first();

            if (!$assetModel) {
                $this->errors[] = "Row {$rowNumber}: Asset Model ID '{$assetModelId}' not found or inactive.";
                continue;
            }

            // Validate Station
            $station = AirportStation::where('id', $stationId)
                ->where('status', 1)
                ->first();

            if (!$station) {
                $this->errors[] = "Row {$rowNumber}: Airport/Station ID '{$stationId}' not found or inactive.";
                continue;
            }

            // Database Duplicate Tag Check
            if (AssetInventory::where('tag_no', $tagNo)->exists()) {
                $this->errors[] = "Row {$rowNumber}: Asset Tag '{$tagNo}' already exists in database.";
                continue;
            }

            // Excel Duplicate Tag Check
            if (in_array($tagNo, $this->excelTags)) {
                $this->errors[] = "Row {$rowNumber}: Asset Tag '{$tagNo}' is duplicated in this Excel file.";
                continue;
            }

            $this->excelTags[] = $tagNo;

            // Validate Warranty Years
            if (!is_numeric($warrantyYear) || $warrantyYear < 0) {
                $this->errors[] = "Row {$rowNumber}: Warranty (Yrs) must be a valid number.";
                continue;
            }

            // Date Conversion
            try {
                $installationDateValue = $this->formatExcelDate($installationDate);
                $warrantyEndValue      = $this->formatExcelDate($warrantyEnd);
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: Invalid date format. Use DD-MM-YYYY or Excel Date format.";
                continue;
            }

            // Status Validation
            $allowedStatuses = ['Available', 'Assigned', 'Repair', 'Scrapped'];
            if (empty($assetStatus)) {
                $assetStatus = 'Available';
            }

            if (!in_array($assetStatus, $allowedStatuses)) {
                $this->errors[] = "Row {$rowNumber}: Invalid Asset Status '{$assetStatus}'.";
                continue;
            }

            // Create Inventory
            AssetInventory::create([
                'tag_no'            => $tagNo,
                'serial_no'         => $serialNo,
                'po_number'         => $poNumber,
                'asset_model_id'    => $assetModel->id,
                'asset_type_id'     => $assetModel->asset_type_id, // Derived from AssetModel
                'station_id'        => $station->id,
                'location_id'       => $station->location_id,     // Derived from AirportStation
                'installation_date' => $installationDateValue,
                'warranty_year'     => (int) $warrantyYear,
                'warranty_end'      => $warrantyEndValue,
                'asset_status'      => $assetStatus,
                'created_by'        => auth()->id(),
                'status'            => 1,
            ]);

            $this->successCount++;
        }

        if (!$hasData) {
            $this->errors[] = 'Excel file does not contain any inventory records.';
        }
    }

    private function formatExcelDate($date): string
    {
        if (is_numeric($date)) {
            return ExcelDate::excelToDateTimeObject($date)->format('Y-m-d');
        }

        $date = trim((string) $date);

        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Continue checking remaining formats
            }
        }

        throw new \Exception('Invalid date format');
    }
}