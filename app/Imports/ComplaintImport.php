<?php

namespace App\Imports;

use App\Models\Complaint;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class ComplaintImport implements ToCollection, WithHeadingRow
// {
//     protected $uploadId;

//     public function __construct($uploadId)
//     {
//         $this->uploadId = $uploadId;
//     }

//     public function collection(Collection $rows)
//     {
//         foreach ($rows as $row) {

//             Complaint::create([

//                 'upload_id' => $this->uploadId,

//                 'complaint_title' => trim($row['complainttitle']),

//                 'engineer_name' => trim($row['engg_name']),

//                 'status' => trim($row['status']),

//                 'resolution_time' => trim($row['actual_resolution_time'])

//             ]);

//         }
//     }
// }
class ComplaintImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts  
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
            empty($row['engg_name']) &&
            empty($row['status'])
        ) {
            return null;
        }

        return new Complaint([
            'upload_id'        => $this->uploadId,
            'complaint_title'  => trim($row['complainttitle'] ?? ''),
            'engineer_name'    => trim($row['engg_name'] ?? ''),
            'status'           => trim($row['status'] ?? ''),
            'resolution_time'  => trim($row['actual_resolution_time'] ?? ''),
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