<?php

namespace App\Exports;

use App\Models\Custodian;
use App\Models\AssetIssueRegister;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustodianAssetExport implements FromArray, WithHeadings
{
    protected $custodianId;

    public function __construct($custodianId)
    {
        $this->custodianId = $custodianId;
    }

    public function headings(): array
    {
        return [
            'Custodian Name',
            'Employee ID',
            'Email',
            'Designation',
            'Department',
            'Section',
            'Custodian Location',

            'Asset Tag No.',
            'Asset Type',
            'Asset Model',
            'Manufacturer',
            'Serial No.',
            'Asset Location',
            'Issued Date',
            'Issue Status',
            'Remarks',
        ];
    }

    public function array(): array
    {
        $custodian = Custodian::with([
            'designation',
            'discipline',
            'section',
            'location',
        ])->findOrFail($this->custodianId);

        $issues = AssetIssueRegister::with([
            'assetInventory.assetModel.assetType',
            'assetInventory.location',
        ])
        ->where('custodian_id', $this->custodianId)
        ->where('issue_status', 'Issued')
        ->orderBy('issued_date', 'desc')
        ->get();

        $rows = [];

        foreach ($issues as $issue) {
            $inventory = $issue->assetInventory;
            $rows[] = [

                // Custodian Details
                $custodian->custodian_name ?? '-',
                $custodian->emp_id ?? '-',
                $custodian->email ?? '-',
                $custodian->designation ? ucwords($custodian->designation->name): '-',                                      
                $custodian->discipline->name ?? '-',
                $custodian->section->section_name ?? 'N/A',
                $custodian->location->name ?? 'N/A',

                // Asset Details
                $inventory->tag_no ?? '-',
                $inventory->assetModel?->assetType?->name ?? '-',
                $inventory->assetModel?->model_name ?? '-',
                $inventory->assetModel?->manufacturer ?? '-',
                $inventory->serial_no ?? '-',
                $inventory->location?->name ?? '-',
                $issue->issued_date? $issue->issued_date->format('d-m-Y'): '-',                                    
                $issue->issue_status ?? '-',
                $issue->remarks ?? '-',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | If no currently issued assets
        |--------------------------------------------------------------------------
        */

        if (empty($rows)) {

            $rows[] = [

                $custodian->custodian_name ?? '-',
                $custodian->emp_id ?? '-',
                $custodian->email ?? '-',
                $custodian->designation
                    ? ucwords($custodian->designation->name)
                    : '-',

                $custodian->discipline->name ?? '-',
                $custodian->section->section_name ?? 'N/A',
                $custodian->location->name ?? 'N/A',

                'No currently issued assets',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
            ];
        }

        return $rows;
    }
}