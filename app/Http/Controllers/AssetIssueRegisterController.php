<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\CustodianAssetExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{AssetInventory, Custodian, AssetIssueRegister};

class AssetIssueRegisterController extends Controller
{
    //

    public function index(Request $request){

        $query = AssetIssueRegister::with(['assetInventory.assetModel.assetType', 'assetInventory.location', 
                                        'custodian.designation', 'custodian.discipline', 'custodian.section']);

        /*
        | Search Employee ID
        */
        if ($request->filled('emp_id')) {
            $query->whereHas('custodian', function ($q) use ($request) {
                $q->where('emp_id', 'LIKE', '%' . $request->emp_id . '%');
            });
        }  
        
        /*
        | Search Custodian
        */
        if ($request->filled('custodian_name')) {
            $query->whereHas('custodian', function ($q) use ($request) {
                $q->where('custodian_name', 'LIKE', '%' . $request->custodian_name . '%');
            });
        }

        /*
        | Search Tag
        */
        if ($request->filled('tag_no')) {
            $query->whereHas('assetInventory', function ($q) use ($request) {
                $q->where('tag_no','LIKE','%' . $request->tag_no . '%');
            });
        }

        /*
        | Issue Status
        */
        if ($request->filled('issue_status')) {
            $query->where('issue_status', $request->issue_status);
        }

        $issueRegisters = $query->latest()->get();

         /*
        |--------------------------------------------------------------------------
        | Get Available Issue Statuses From Database
        |--------------------------------------------------------------------------
        */
        $issueStatuses = AssetIssueRegister::query()->whereNotNull('issue_status')->where('issue_status', '!=', '')->distinct()
                                                    ->orderBy('issue_status')->pluck('issue_status');

        return view('asset-issue-register.index', compact('issueRegisters', 'issueStatuses'));
    }

    public function create()
    {
        /*
        |----------------------------------------------------------------------
        | Only Available Assets
        |----------------------------------------------------------------------
        */

        $assets = AssetInventory::with([
            'assetModel.assetType',
            'location'
        ])->where('status', 1)->where('asset_status', 'Available')->orderBy('tag_no')->get();
        
        /*
        |----------------------------------------------------------------------
        | Active Custodians
        |----------------------------------------------------------------------
        */

        $custodians = Custodian::with([
            'designation',
            'discipline',
            'section'
        ])->where('status', 1)->orderBy('custodian_name')->get();
        
        /*
        |----------------------------------------------------------------------
        | Asset Data For JavaScript
        |----------------------------------------------------------------------
        */

        $assetData = $assets->map(function ($asset) {

            return [
                'id'            => $asset->id,
                'tag_no'        => $asset->tag_no,
                'asset_type'    => $asset->assetModel?->assetType?->name,                  
                'asset_model'   => $asset->assetModel?->model_name,                  
            ];

        })->values();


        return view('asset-issue-register.create', compact('assets', 'custodians', 'assetData')
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'custodian_id' => [
                'required',
                'exists:custodians,id'
            ],

            'asset_inventory_ids' => [
                'required',
                'array',
                'min:1'
            ],

            'asset_inventory_ids.*' => [
                'required',
                'distinct',
                'exists:asset_inventories,id'
            ],

            'user_type' => [
                'required',
                'in:self,multiuser,operator'
            ],

            'operator_name' => [
                'nullable',
                'required_if:user_type,operator',
                'string',
                'max:255'
            ],

            'issued_date' => [
                'required',
                'date'
            ],

        ], [

            'custodian_id.required' =>
                'Please select a custodian.',

            'asset_inventory_ids.required' =>
                'Please add at least one asset.',

            'asset_inventory_ids.min' =>
                'Please add at least one asset.',

            'asset_inventory_ids.*.distinct' =>
                'Duplicate asset selected.',

            'user_type.required' =>
                'Please select user type.',

            'operator_name.required_if' =>
                'Operator name is required.',

            'issued_date.required' =>
                'Issue date is required.',

        ]);


        DB::beginTransaction();

        try {

            foreach ($request->asset_inventory_ids as $assetId) {

                $asset = AssetInventory::where('id', $assetId)
                    ->lockForUpdate()
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Check availability
                |--------------------------------------------------------------------------
                */

                if ($asset->asset_status !== 'Available') {

                    throw new \Exception(
                        "Asset {$asset->tag_no} is no longer available."
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Create Issue History
                |--------------------------------------------------------------------------
                */

                AssetIssueRegister::create([

                    'asset_inventory_id' =>
                        $asset->id,

                    'custodian_id' =>
                        $request->custodian_id,

                    'user_type' =>
                        $request->user_type,

                    'operator_name' =>
                        $request->operator_name,

                    'issued_date' =>
                        $request->issued_date,

                    'returned_date' =>
                        null,

                    'issue_status' =>
                        'Issued',

                ]);


                /*
                |--------------------------------------------------------------------------
                | Update Asset Status
                |--------------------------------------------------------------------------
                */

                $asset->update([

                    'asset_status' =>
                        'Assigned'

                ]);

            }


            DB::commit();


            return redirect()
                ->route('asset-issue-register.index')
                ->with(
                    'success',
                    count($request->asset_inventory_ids) .
                    ' asset(s) issued successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );

        }
    }

    public function returnAsset($id)
    {
        DB::beginTransaction();

        try {

            $issue = AssetIssueRegister::with('assetInventory')
                ->lockForUpdate()
                ->findOrFail(decryptId($id));

            /*
            | Already returned
            */

            if ($issue->issue_status === 'Returned') {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'This asset has already been returned.'
                ], 422);
            }

            /*
            | Update issue history
            */

            $issue->update([

                'returned_date' =>
                    now()->toDateString(),

                'issue_status' =>
                    'Returned',

            ]);

            /*
            | Make asset available
            */

            $issue->assetInventory->update([
                'asset_status' => 'Available',                   
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset returned successfully.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to return asset.'
            ], 500);
        }
    }

    public function custodianDetails($id)
    {
        $custodian = Custodian::with([
            'designation',
            'discipline',
            'section'
        ])->where('id', $id)->where('status', 1)->first();

        if (!$custodian) {
            return response()->json([
                'status' => false,
                'message' => 'Custodian not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'custodian' => [
                'emp_id' => $custodian->emp_id,                   
                'designation' => $custodian->designation?->name,                  
                'department' => $custodian->discipline?->name,                   
                'section' => $custodian->section?->section_name,                   
            ]

        ]);
    }

    // custodian asset details
    // public function custodianAssetDetails($id){

    //     try{
    //          /*
    //         |--------------------------------------------------------------------------
    //         | Decrypt Custodian ID
    //         |--------------------------------------------------------------------------
    //         */
    //         $custodianId = decryptId($id);
    //     } catch(\Throwable $e){
    //         return response()->json([
    //             'status'    => false,
    //             'message'   => 'Invalid custodian selected.'
    //         ],400);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Get Custodian
    //     |--------------------------------------------------------------------------
    //     */

    //     $custodian = Custodian::with([
    //         'designation', 'discipline', 'section', 'location',
    //     ])->find($custodianId);

    //     if(!$custodian){
    //         return response()->json([
    //             'status' => false,
    //             'message'   => 'Custodian not found.',
    //         ],404);
    //     }

    //      /*
    //     |--------------------------------------------------------------------------
    //     | Get Currently Issued Assets
    //     |--------------------------------------------------------------------------
    //     |
    //     | We only show assets whose issue_status is "Issued".
    //     |
    //     */

    //     $isuues = AssetIssueRegister::with(['assetInventory.assetModel.assetType', 'assetInventory.location',])
    //                                     ->where('custodian_id', $custodianId)
    //                                     ->where('issue_status', 'issued')
    //                                     ->where('issued_date', 'desc')->get();

    //      /*
    //     |--------------------------------------------------------------------------
    //     | Prepare Asset Data
    //     |--------------------------------------------------------------------------
    //     */

    //     $assets = $issues->map(function($issue) {
    //         $inventory = $issue->assetInventory;

    //         return [
    //             'issue_id'      => encryptId($issue->id),
    //             'tag_no'        => $inventory?->tag_no ?? '-',
    //             'asset_type'    => $inventory?->assetModel?->assetType?->name ?? '-',
    //             'model'         => $inventory?->assetModel?->model_name ?? '-',
    //             'manufacturer'  => $inventory?->assetModel?->manufacturer ?? '-',
    //             'serialo_no'    => $inventory?->serialo_no ?? '-',
    //             'location'      => $inventory?->location?->name ?? '-',
    //             'issued_date'   => $issue->issued_date ? $issue->issued_date->format('d-m-Y') : '-',
    //             'issue_status'  => $issue->issue_status ?? '-',
    //             'remarks'       => $issue->remarks ?? '-',
    //         ];
    //     })->value();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Get Issue Information
    //     |--------------------------------------------------------------------------
    //     |
    //     | Usually all assets belonging to one issue transaction have
    //     | the same user_type/operator/issue date.
    //     |
    //     */

    //     $firstIssue = $issue->first();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Return Response
    //     |--------------------------------------------------------------------------
    //     */ 

    //     return response()->json([
    //         'status'    => true,
    //         'custodian' => [
    //             'name'      => $custodian->custodian_name ?? '-',
    //             'emp_id'    => $custodian->emp_id ?? '-',
    //             'email'    => $custodian->email ?? '-',
    //             'designation'    => $custodian->designation?->name ?? '-',
    //             'department'    => $custodian->department?->name ?? '-',
    //             'section'    => $custodian->section?->section_name ?? '-',
    //             'location'    => $custodian->location ?? '-',
    //             'status'    => $custodian->status == 1 ? 'Active' : 'Inactive',
    //         ],
    //         'issue' => [
    //             'total_assets'  => $assets->count(),
    //             'user_type'     => $firstIssue?->user_type ?? '-',
    //             'operator_name' => $firstIssue?->operator_name ?? '-',
    //             'issued_date'   => $firstIssue?->issued_date ? $firstIssue->issued_date->format('d-m-Y') : '-',
    //         ],
    //         'assets' => $assets,
    //     ]);
    // }

    public function custodianAssetDetails($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Decrypt Custodian ID
        |--------------------------------------------------------------------------
        */

        try {

            $custodianId = decryptId($id);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Invalid custodian selected.'
            ], 400);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Custodian
        |--------------------------------------------------------------------------
        */

        $custodian = Custodian::with([
            'designation',
            'discipline',
            'section',
            'location',
        ])->find($custodianId);


        if (!$custodian) {

            return response()->json([
                'status'  => false,
                'message' => 'Custodian not found.'
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Currently Issued Assets
        |--------------------------------------------------------------------------
        */

        $issues = AssetIssueRegister::with([
            'assetInventory.assetModel.assetType',
            'assetInventory.location',
        ])
        ->where('custodian_id', $custodianId)
        ->where('issue_status', 'Issued')
        ->orderBy('issued_date', 'desc')
        ->get();


        /*
        |--------------------------------------------------------------------------
        | Prepare Asset Data
        |--------------------------------------------------------------------------
        */

        $assets = $issues->map(function ($issue) {

            $inventory = $issue->assetInventory;

            return [

                'issue_id' => encryptId($issue->id),

                'tag_no' => $inventory?->tag_no ?? '-',

                'asset_type' =>
                    $inventory?->assetModel?->assetType?->name ?? '-',

                'asset_model' =>
                    $inventory?->assetModel?->model_name ?? '-',

                'manufacturer' =>
                    $inventory?->assetModel?->manufacturer ?? '-',

                'serial_no' =>
                    $inventory?->serial_no ?? '-',

                'location' =>
                    $inventory?->location?->name ?? '-',

                'issued_date' =>
                    $issue->issued_date
                        ? $issue->issued_date->format('d-m-Y')
                        : '-',

                'issue_status' =>
                    $issue->issue_status ?? '-',

                'remarks' =>
                    $issue->remarks ?? '-',
            ];

        })->values();


        /*
        |--------------------------------------------------------------------------
        | First Issue Information
        |--------------------------------------------------------------------------
        */

        $firstIssue = $issues->first();


        /*
        |--------------------------------------------------------------------------
        | Return JSON Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'status' => true,

            'custodian' => [

                'custodian_name' =>
                    $custodian->custodian_name ?? '-',

                'emp_id' =>
                    $custodian->emp_id ?? '-',

                'email' =>
                    $custodian->email ?? '-',

                'designation' =>
                    $custodian->designation?->name ?? '-',

                'department' =>
                    $custodian->discipline?->name ?? '-',

                'section' =>
                    $custodian->section?->section_name ?? 'N/A',

                'location' =>
                    $custodian->location?->name ?? 'N/A',

                'status' =>
                    $custodian->status == 1
                        ? 'Active'
                        : 'Inactive',
            ],

            'issue' => [

                'total_assets' =>
                    $assets->count(),

                'user_type' =>
                    $firstIssue?->user_type ?? '-',

                'operator_name' =>
                    $firstIssue?->operator_name ?? '-',

                'issued_date' =>
                    $firstIssue?->issued_date
                        ? $firstIssue->issued_date->format('d-m-Y')
                        : '-',
            ],

            'assets' => $assets,
        ]);
    }

    /**
     * Download custodian issued asset details as Excel.
     */
    public function custodianExport($id)
    {
        try {

            $custodianId = decryptId($id);

        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->with('error', 'Invalid custodian selected.');
        }

        $custodian = Custodian::find($custodianId);
        if (!$custodian) {
            return redirect()
                ->back()
                ->with('error', 'Custodian not found.');
        }

        $fileName = 'Custodian_' .
            preg_replace('/[^A-Za-z0-9_-]/', '_', $custodian->custodian_name) .
            '_Assets.xlsx';

        return Excel::download(
            new CustodianAssetExport($custodianId),
            $fileName
        );
    }

}
