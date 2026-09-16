<?php

namespace App\Exports;

use App\Models\AssetIssueRegister;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class AssetIssueRegisterExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $request = $this->request;

        $query = AssetIssueRegister::with([
            'assetInventory.assetModel.assetType',
            'assetInventory.location',
            'custodian.designation',
            'custodian.discipline',
            'custodian.section',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Employee ID
        |--------------------------------------------------------------------------
        */

        if ($request->filled('emp_id')) {
            $query->whereHas('custodian', function ($q) use ($request) {
                $q->where(
                    'emp_id',
                    'LIKE',
                    '%' . $request->emp_id . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Custodian Name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('custodian_name')) {
            $query->whereHas('custodian', function ($q) use ($request) {
                $q->where(
                    'custodian_name',
                    'LIKE',
                    '%' . $request->custodian_name . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Asset Tag
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tag_no')) {
            $query->whereHas('assetInventory', function ($q) use ($request) {
                $q->where(
                    'tag_no',
                    'LIKE',
                    '%' . $request->tag_no . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Asset Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('asset_type')) {
            $query->whereHas(
                'assetInventory.assetModel.assetType',
                function ($q) use ($request) {
                    $q->where('id', $request->asset_type);
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Issue Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('issue_status')) {
            $query->where(
                'issue_status',
                $request->issue_status
            );
        }

        return $query
            ->latest()
            ->get()
            ->map(function ($issue) {

                return [
                    'Tag No.' => $issue->assetInventory->tag_no ?? 'N/A',

                    'Asset Type' => ucwords(
                        $issue->assetInventory
                            ->assetModel
                            ->assetType
                            ->name ?? 'N/A'
                    ),

                    'Asset Model' => $issue->assetInventory
                        ->assetModel
                        ->model_name ?? 'N/A',

                    'Custodian' => ucwords(
                        $issue->custodian->custodian_name ?? 'N/A'
                    ),

                    'Employee ID' => $issue->custodian->emp_id ?? 'N/A',

                    'Department' => ucwords(
                        $issue->custodian->discipline->name ?? 'N/A'
                    ),

                    'Section' => ucwords(
                        $issue->custodian->section->name ?? 'N/A'
                    ),

                    'Designation' => ucwords(
                        $issue->custodian->designation->name ?? 'N/A'
                    ),

                    'User Type' => ucfirst(
                        $issue->user_type ?? 'N/A'
                    ),

                    'Issued Date' => $issue->issued_date
                        ? $issue->issued_date->format('d-m-Y')
                        : 'N/A',

                    'Transfer Date' => $issue->transfer_date
                        ? $issue->transfer_date->format('d-m-Y')
                        : '-',

                    'Returned Date' => $issue->returned_date
                        ? $issue->returned_date->format('d-m-Y')
                        : '-',

                    'Retained Date' => $issue->retained_date
                        ? $issue->retained_date->format('d-m-Y')
                        : '-',

                    'Status' => $issue->issue_status ?? 'N/A',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tag No.',
            'Asset Type',
            'Asset Model',
            'Custodian',
            'Employee ID',
            'Department',
            'Section',
            'Designation',
            'User Type',
            'Issued Date',
            'Transfer Date',
            'Returned Date',
            'Retained Date',
            'Status',
        ];
    }
}