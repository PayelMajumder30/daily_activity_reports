<?php

namespace App\Imports;

use App\Models\AssetInventory;
use App\Models\AssetModel;
use App\Models\AirportStation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class AssetInventoryImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    public array $errors = [];
    public int $successCount = 0;
    private array $excelTags = [];
    private array $excelSerials = [];

    /**
     * Define which sheets to process.
     */
    public function sheets(): array
    {
        return [
            'Asset Inventory' => $this,
        ];
    }

    /**
     * Check if a given value represents "Not Applicable" / "N/A" placeholders.
     */
    private function isNotApplicableValue($value): bool
    {
        if ($value === null) {
            return true;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, [
            '',
            'na',
            'n/a',
            'n.a',
            'n.a.',
            'not applicable',
            'not-applicable',
            'not available',
            'not-available',
            'not found',
            'not-found',
            '-',
        ], true);
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'Excel file does not contain any data in "Asset Inventory" sheet.';
            return;
        }

        $hasData = false;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $assetModelId = trim((string) ($row['asset_model'] ?? ''));
            $serialNo     = trim((string) ($row['asset_serial_no'] ?? ''));
            $tagNo        = trim((string) ($row['asset_tag'] ?? ''));
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

            // --- 1. Required Field Validations ---

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

            // --- 2. Foreign Key Validations ---

            $assetModel = AssetModel::where('id', $assetModelId)
                ->where('status', 1)
                ->first();

            if (!$assetModel) {
                $this->errors[] = "Row {$rowNumber}: Asset Model ID '{$assetModelId}' not found or inactive.";
                continue;
            }

            $station = AirportStation::where('id', $stationId)
                ->where('status', 1)
                ->first();

            if (!$station) {
                $this->errors[] = "Row {$rowNumber}: Airport/Station ID '{$stationId}' not found or inactive.";
                continue;
            }

            // --- 3. Uniqueness Checks (Skip duplicate validation for "N/A" values) ---

            $isTagNA    = $this->isNotApplicableValue($tagNo);
            $isSerialNA = $this->isNotApplicableValue($serialNo);

            // Validate Tag Uniqueness
            if (!$isTagNA) {
                if (AssetInventory::where('tag_no', $tagNo)->exists()) {
                    $this->errors[] = "Row {$rowNumber}: Asset Tag '{$tagNo}' already exists in database.";
                    continue;
                }

                if (in_array(strtolower($tagNo), $this->excelTags, true)) {
                    $this->errors[] = "Row {$rowNumber}: Asset Tag '{$tagNo}' is duplicated in this Excel file.";
                    continue;
                }

                $this->excelTags[] = strtolower($tagNo);
            }

            // Validate Serial No Uniqueness
            if (!$isSerialNA) {
                if (AssetInventory::where('serial_no', $serialNo)->exists()) {
                    $this->errors[] = "Row {$rowNumber}: Serial No. '{$serialNo}' already exists in database.";
                    continue;
                }

                if (in_array(strtolower($serialNo), $this->excelSerials, true)) {
                    $this->errors[] = "Row {$rowNumber}: Serial No. '{$serialNo}' is duplicated in this Excel file.";
                    continue;
                }

                $this->excelSerials[] = strtolower($serialNo);
            }

            // --- 4. Format & Data Validation ---

            if (!is_numeric($warrantyYear) || $warrantyYear < 0) {
                $this->errors[] = "Row {$rowNumber}: Warranty (Yrs) must be a valid number.";
                continue;
            }

            try {
                $installationDateValue = $this->formatExcelDate($installationDate);
                $warrantyEndValue      = $this->formatExcelDate($warrantyEnd);
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: Invalid date format. Use DD-MM-YYYY or Excel Date format.";
                continue;
            }

            $allowedStatuses = ['Available', 'Assigned', 'Repair', 'Damaged'];
            if (empty($assetStatus)) {
                $assetStatus = 'Available';
            }

            if (!in_array($assetStatus, $allowedStatuses, true)) {
                $this->errors[] = "Row {$rowNumber}: Invalid Asset Status '{$assetStatus}'.";
                continue;
            }

            // --- 5. Record Creation ---

            AssetInventory::create([
                'tag_no'            => $tagNo,            // Stores literal Excel value ("N/A", "na", etc.)
                'serial_no'         => $serialNo,         // Stores literal Excel value ("N/A", "na", etc.)
                'po_number'         => $poNumber,
                'asset_model_id'    => $assetModel->id,
                'asset_type_id'     => $assetModel->asset_type_id,
                'station_id'        => $station->id,
                'location_id'       => $station->location_id,
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
                // Keep trying other formats
            }
        }

        throw new \Exception('Invalid date format');
    }
}