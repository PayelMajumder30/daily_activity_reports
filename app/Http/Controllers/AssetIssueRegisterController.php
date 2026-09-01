<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\CustodianAssetExport;
use App\Exports\AssetIssueRegisterExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{AssetInventory, Custodian, AssetIssueRegister, AssetTransfer, AssetType};

class AssetIssueRegisterController extends Controller
{
    //

    public function index(Request $request){

        $query = AssetIssueRegister::with(['assetInventory.assetModel.assetType', 'assetInventory.location', 'assetInventory.assetTransfers.fromCustodian',
                                            'assetInventory.assetTransfers.toCustodian', 'custodian.designation', 'custodian.discipline', 'custodian.section']);
                                        
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
        | Search asset type
        */ 

        if($request->filled('asset_type')) {
            $query->whereHas('assetInventory.assetModel.assetType', function($q) use($request) {
                $q->where('id', $request->asset_type);
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

        $assetTypes = AssetType::where('status', 1)->orderBy('name')->get();      
        
        $custodians = Custodian::with(['designation', 'discipline', 'section', 'location'])->where('status', 1)->orderBy('custodian_name')->get();

        return view('asset-issue-register.index', compact('issueRegisters', 'issueStatuses', 'custodians', 'assetTypes'));
    }

    private function getAssetHistory($issue): string
    {
        $asset = $issue->assetInventory;

        if (!$asset) {
            return 'N/A';
        }

        $assetName = $asset->assetModel?->model_name
            ?? $asset->tag_no
            ?? 'Asset';

        $currentCustodian = $issue->custodian?->custodian_name
            ?? 'Unknown User';

        /*
        |--------------------------------------------------------------------------
        | Returned
        |--------------------------------------------------------------------------
        */

        if ($issue->issue_status === 'Returned') {

            return $assetName .
                ' returned to IT Department by ' .
                $currentCustodian;
        }

        /*
        |--------------------------------------------------------------------------
        | Transferred
        |--------------------------------------------------------------------------
        */

        if ($issue->issue_status === 'Transferred') {

            $transfer = $asset->assetTransfers
                ->filter(function ($transfer) use ($issue) {

                    return
                        $transfer->from_custodian_id == $issue->custodian_id
                        &&
                        $transfer->transfer_date >= $issue->issued_date;
                })
                ->sortByDesc('id')
                ->first();

            if ($transfer) {

                $from = $transfer->fromCustodian?->custodian_name
                    ?? 'Unknown User';

                $to = $transfer->toCustodian?->custodian_name
                    ?? 'Unknown User';

                return $assetName .
                    ' transferred from ' .
                    $from .
                    ' to ' .
                    $to;
            }

            return $assetName .
                ' transferred by ' .
                $currentCustodian;
        }

        /*
        |--------------------------------------------------------------------------
        | Issued
        |--------------------------------------------------------------------------
        |
        | Check whether this issue came from a transfer.
        |
        */

        if ($issue->issue_status === 'Issued') {

            $transfer = $asset->assetTransfers
                ->filter(function ($transfer) use ($issue) {

                    return
                        $transfer->to_custodian_id == $issue->custodian_id
                        &&
                        $transfer->transfer_date <= $issue->issued_date;
                })
                ->sortByDesc('id')
                ->first();

            /*
            | Asset came from another user
            */

            if ($transfer) {

                $from = $transfer->fromCustodian?->custodian_name
                    ?? 'Unknown User';

                return $assetName .
                    ' transferred from ' .
                    $from .
                    ' to ' .
                    $currentCustodian;
            }

            /*
            | First issue from IT Department
            */

            return $assetName .
                ' issued to ' .
                $currentCustodian .
                ' by IT Department';
        }

        return $assetName . ' - No history available';
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

            'custodian_id.required'             => 'Please select a custodian.',               
            'asset_inventory_ids.required'      => 'Please add at least one asset.',               
            'asset_inventory_ids.min'           => 'Please add at least one asset.',               
            'asset_inventory_ids.*.distinct'    => 'Duplicate asset selected.',               
            'user_type.required'                => 'Please select user type.',               
            'operator_name.required_if'         => 'Operator name is required.',              
            'issued_date.required'              => 'Issue date is required.',           

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

                    'asset_inventory_id'    => $asset->id,                        
                    'custodian_id'          => $request->custodian_id,                      
                    'user_type'             => $request->user_type,                      
                    'operator_name'         => $request->operator_name,                      
                    'issued_date'           => $request->issued_date,                       
                    'returned_date'         => null,                       
                    'issue_status'          => 'Issued',                       

                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Asset Status
                |--------------------------------------------------------------------------
                */

                $asset->update([
                    'asset_status' => 'Assigned'                     
                ]);                            

            }

            /*
            |--------------------------------------------------------------------------
            | Event Log - Asset Issued
            |--------------------------------------------------------------------------
            */

            eventLog(
                'Issued',
                'Asset Issue Register',
                count($request->asset_inventory_ids) . 'asset(s) issued to custodian ID: ' . $request->custodian_id . 'by user ID: ' . auth()->id()
            );

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
            return back()->withInput()->with('error', $e->getMessage());
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
                'returned_date' => now()->toDateString(),                
                'issue_status' => 'Returned',                  
            ]);

            /*
            | Make asset available
            */

            $issue->assetInventory->update([
                'asset_status' => 'Available',                   
            ]);

            /*
            |--------------------------------------------------------------------------
            | Event Log
            |--------------------------------------------------------------------------
            */

            eventLog('Returned', 'Asset Issue Register', 'Asset tag ' . ($issue->assetInventory?->tag_no ?? '-') . 'returned successfully user ID: ' . auth()->id());

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
            'section',
            'location',
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
                'emp_id'        => $custodian->emp_id ?? '-',                   
                'designation'   => $custodian->designation?->name ?? '-',                  
                'department'    => $custodian->discipline?->name ?? '-',                   
                'section'       => $custodian->section?->section_name ?? '-',                   
                'loaction'      => $custodian->location?->name ?? '-',                   
            ]

        ]);
    }

    // custodian asset details
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
            'station',
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
            'assetInventory.location', 'assetInventory.station',
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
                'issue_id'      => encryptId($issue->id),
                'tag_no'        => $inventory?->tag_no ?? '-',
                'asset_type'    => $inventory?->assetModel?->assetType?->name ?? '-',                
                'asset_model'   => $inventory?->assetModel?->model_name ?? '-',                  
                'manufacturer'  => $inventory?->assetModel?->manufacturer ?? '-',                    
                'serial_no'     => $inventory?->serial_no ?? '-',                  
                'location'      => $inventory?->location?->name ?? '-',                   
                'station'       => $inventory?->station?->station_name ?? '-',                   
                'issued_date'   => $issue->issued_date ? $issue->issued_date->format('d-m-Y') : '-',                                                               
                'issue_status'  => $issue->issue_status ?? '-',                   
                'remarks'       => $issue->remarks ?? '-',
                    
            ]; })->values();
    
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

                'custodian_name'    => $custodian->custodian_name ?? '-',                   
                'emp_id'            => $custodian->emp_id ?? '-',                   
                'email'             => $custodian->email ?? '-',                    
                'designation'       => $custodian->designation?->name ?? '-',                    
                'department'        => $custodian->discipline?->name ?? '-',                   
                'section'           => $custodian->section?->section_name ?? 'N/A',                    
                'location'          => $custodian->location?->name ?? 'N/A',                   
                'station'           => $custodian->station?->station_name ?? 'N/A',                   
                'status'            => $custodian->status == 1 ? 'Active' : 'Inactive',                                                               
            ],

            'issue' => [
                'total_assets'  => $assets->count(),                   
                'user_type'     => $firstIssue?->user_type ?? '-',                   
                'operator_name' => $firstIssue?->operator_name ?? '-',                  
                'issued_date'   => $firstIssue?->issued_date ? $firstIssue->issued_date->format('d-m-Y') : '-',                                                          
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
            return redirect()->back()->with('error', 'Invalid custodian selected.');                          
        }

        $custodian = Custodian::find($custodianId);
        if (!$custodian) {
            return redirect()->back()->with('error', 'Custodian not found.');             
        }

        $fileName = 'Custodian_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $custodian->custodian_name) . '_Assets.xlsx';                    
        eventLog('Downloaded', 'Asset Issue Register', 'Custodian asset details Excel downloaded for custodian: ' 
            . ($custodian->custodian_name ?? '-') . ' by user ID: ' .  auth()->id());

        return Excel::download(new CustodianAssetExport($custodianId), $fileName);                         
    }

    /**
     * Get current custodian details for asset transfer.
     */
    public function transferDetails($id)
    {
        try {

            $issueId = decryptId($id);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Invalid issue selected.'
            ], 400);
        }


        /*
        |--------------------------------------------------------------------------
        | Get currently issued asset
        |--------------------------------------------------------------------------
        */

        $issue = AssetIssueRegister::with([
            'assetInventory',
            'custodian.designation',
            'custodian.discipline',
            'custodian.section',
            'custodian.location',
        ])
        ->where('id', $issueId)
        ->where('issue_status', 'Issued')
        ->first();


        if (!$issue) {

            return response()->json([
                'status'  => false,
                'message' => 'Issued asset not found.'
            ], 404);
        }

         /*
        |--------------------------------------------------------------------------
        | Get active custodians except current custodian
        |--------------------------------------------------------------------------
        */

        $custodians = Custodian::with([
            'designation',
            'discipline',
            'section'
        ])
        ->where('status', 1)
        ->where('id', '!=', $issue->custodian_id)
        ->orderBy('custodian_name')
        ->get();
        /*
        |--------------------------------------------------------------------------
        | Return current custodian details
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'issue' => [
                'issue_id' => encryptId($issue->id),                   
                'asset_tag' => $issue->assetInventory?->tag_no ?? '-',                   
                'custodian_id' => $issue->custodian_id,                   
                'custodian_name' => $issue->custodian?->custodian_name ?? '-',                   
                'emp_id' => $issue->custodian?->emp_id ?? '-',                 
            ],

        ]);
    }

    /**
     * Main transfer function.
     */
    public function transferAsset(Request $request)
    {
        $request->validate([

            'issue_id' => [
                'required'
            ],

            'to_custodian_id' => [
                'required',
                'exists:custodians,id'
            ],

            'transfer_date' => [
                'required',
                'date'
            ],

            'remarks' => [
                'nullable',
                'string'
            ],

        ], [

            'to_custodian_id.required' =>
                'Please select the new custodian.',

            'transfer_date.required' =>
                'Transfer date is required.',

        ]);


        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Get Current Issue
            |--------------------------------------------------------------------------
            */

            $issueId = decryptId($request->issue_id);

            $issue = AssetIssueRegister::with([
                'assetInventory',
                'custodian'
            ])
            ->lockForUpdate()
            ->findOrFail($issueId);


            /*
            |--------------------------------------------------------------------------
            | Make Sure Asset Is Currently Issued
            |--------------------------------------------------------------------------
            */

            if ($issue->issue_status !== 'Issued') {
                throw new \Exception(
                    'Only currently issued assets can be transferred.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Current Custodian
            |--------------------------------------------------------------------------
            */

            $fromCustodianId = $issue->custodian_id;

            /*
            |--------------------------------------------------------------------------
            | Prevent Transfer To Same Custodian
            |--------------------------------------------------------------------------
            */

            if (
                (int) $fromCustodianId ===
                (int) $request->to_custodian_id
            ) {

                throw new \Exception(
                    'Asset cannot be transferred to the same custodian.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Get New Custodian
            |--------------------------------------------------------------------------
            */

            $toCustodian = Custodian::where('id', $request->to_custodian_id)
                ->where('status', 1)
                ->first();

            if (!$toCustodian) {
                throw new \Exception(
                    'Selected custodian is not active.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Old Issue
            |--------------------------------------------------------------------------
            */

            $issue->update([
                'issue_status' => 'Transferred',
                'remarks' => $request->remarks,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Transfer History
            |--------------------------------------------------------------------------
            */

            AssetTransfer::create([

                'asset_inventory_id'    => $issue->asset_inventory_id,               
                'from_custodian_id'     => $fromCustodianId,                
                'to_custodian_id'       => $request->to_custodian_id,                  
                'transfer_date'         => $request->transfer_date,                 
                'created_by'            => auth()->id(),                   
                'remarks'               => $request->remarks,                 
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create New Issue Record
            |--------------------------------------------------------------------------
            */

            AssetIssueRegister::create([

                'asset_inventory_id'    => $issue->asset_inventory_id,                  
                'custodian_id'          => $request->to_custodian_id,                 
                'user_type'             => $issue->user_type,                   
                'operator_name'         => $issue->operator_name,                 
                'issued_date'           => $request->transfer_date,                 
                'returned_date'         => null,                  
                'issue_status'          => 'Issued',                   
                'remarks'               => $request->remarks,                   
            ]);


            /*
            |--------------------------------------------------------------------------
            | Asset Remains Assigned
            |--------------------------------------------------------------------------
            */

            $issue->assetInventory->update([
                'asset_status' => 'Assigned'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Event Log
            |--------------------------------------------------------------------------
            */

            eventLog(
                'Transferred',
                'Asset Issue Register',
                'Asset ' .
                ($issue->assetInventory?->tag_no ?? '-') .
                ' transferred from ' .
                ($issue->custodian?->custodian_name ?? '-') .
                ' to ' .
                ($toCustodian->custodian_name ?? '-') .
                ' by user ID: ' .
                auth()->id()
            );


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset transferred successfully.'                 
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()               
            ], 422);
        }
    }

    public function export(Request $request)
    {
        return Excel::download(
            new AssetIssueRegisterExport($request),
            'asset-issue-register.xlsx'
        );
    }

}
