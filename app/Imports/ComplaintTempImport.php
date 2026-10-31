<?php

namespace App\Imports;

use App\Models\ComplaintTemp;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ComplaintTempImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts  
{
    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function model(array $row)
    {
        if (
            empty($row['complainttitle']) &&
            empty($row['activity_details']) &&
            empty($row['engg_name']) &&
            empty($row['status'])
        ) {
            return null;
        }

        return new ComplaintTemp([
            'upload_id'        => $this->uploadId,

            // Old Excel: ComplaintTitle
            // New Excel: Activity Details
            'complaint_title'  => trim($row['activity_details'] ?? $row['complainttitle'] ?? ''),
            'type_of_activity' => trim($row['type_of_activity'] ?? ''),
            'asset_tag_no'     => trim($row['asset_tag_no'] ?? ''),
            'engineer_name'    => trim($row['engg_name'] ?? ''),
            'status'           => trim($row['status'] ?? ''),
            // Old: Actual Resolution Time
            // New: Activity Duration
            'resolution_time'  => trim($row['activity_duration'] ?? $row['actual_resolution_time'] ?? ''),
        ]);
    }

    // Read 50 rows at a time
    public function chunkSize(): int
    {
        return 50;
    }

    // Insert 50 rows in one query
    public function batchSize(): int
    {
        return 50;
    }
}
