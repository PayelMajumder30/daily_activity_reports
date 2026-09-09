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

    /** 
     * Display the Asset Issue Register list with optional search filters. 
     * This function retrieves asset issue history records along with the 
     * related Asset Inventory, Asset Model, Asset Type, Location, Transfer, 
     * and Custodian information. 
     *  The list can be filtered by: 
     *  - Employee ID 
     *  - Custodian Name 
     *  - Asset Tag Number 
     *  - Asset Type 
     *  - Issue Status 
     * 
     * The function also retrieves: 
     *  - Available Issue Status values for the status filter. 
     *  - Active Asset Types for the asset type filter. 
     *  - Active Custodians for the custodian filter. 
     * 
     * Related models are eager loaded to avoid unnecessary database queries 
     *  while displaying the Asset Issue Register list. 
     * 
     * @param Request $request Contains optional search and filter parameters. 
     * @return \Illuminate\View\View Returns the Asset Issue Register listing page. */

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

    /** * Generate a human-readable history description for an asset issue record. 
     * 
     * This private helper function determines the history message based on 
     * the current issue status of the asset. 
     * 
     *  Supported history scenarios include: 
     *  - Asset issued directly by the IT Department. 
     *  - Asset transferred from one custodian to another. 
     *  - Asset returned to the IT Department. 
     * 
     *  For transferred assets, the function checks the Asset Transfer history 
     *  to identify the source and destination custodians. 
     * 
     *  For issued assets, the function checks whether the issue was created 
     *  as a result of an asset transfer or was the first issue from the 
     *  IT Department. 
     * 
     *  @param AssetIssueRegister $issue Asset Issue Register record. 
     *  @return string Human-readable asset history description. */

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
                    return $transfer->from_custodian_id == $issue->custodian_id && $transfer->transfer_date >= $issue->issued_date;                                                               
                })->sortByDesc('id')->first();                          

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

    /** 
     *  Display the form used to issue assets to a custodian. 
     * 
     *  This function retrieves: 
     *  - Active assets whose current status is "Available". 
     *  - Active custodians. 
     *  - Simplified asset data required by JavaScript on the issue form. 
     * 
     *  Only available assets are displayed to prevent users from selecting 
     *  assets that are already assigned, retained, or otherwise unavailable. 
     * 
     *  The asset data is also transformed into a simplified collection 
     *  containing the Asset ID, Tag Number, Asset Type, and Asset Model 
     *  for frontend JavaScript usage. 
     * 
     *  @return \Illuminate\View\View Returns the Asset Issue Register creation page. */

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


    /** 
     *  Issue one or multiple assets to a selected custodian. 
     * 
     *  This function validates the submitted issue information and creates 
     *  an Asset Issue Register record for each selected asset. 
     * 
     * Main operations: 
     *  - Validates the selected custodian. 
     *  - Validates that at least one asset has been selected. 
     *  - Prevents duplicate assets in the same request. 
     *  - Validates the selected user type. 
     *  - Requires an operator name when the user type is "operator". 
     *  - Validates the issue date. 
     * 
     * Each selected asset is locked using lockForUpdate() before processing 
     *  to prevent concurrent requests from issuing the same asset. 
     * 
     *  Before issuing an asset, the function verifies that its current status 
     *  is "Available". If the asset has already been assigned by another 
     *  request, the transaction is stopped and no assets are issued. 
     * 
     *  For every selected asset: 
     * - Creates a new Asset Issue Register history record. 
     *  - Sets the Issue Status to "Issued". 
     *  - Updates the Asset Inventory status to "Assigned". 
     * 
     *  A database transaction is used to ensure that all selected assets are 
     *  issued successfully. If any asset cannot be issued, all changes made 
     *  during the request are rolled back. 
     * 
     *  An event log is created after all assets have been issued successfully. 
     * 
     *  @param Request $request Contains custodian, asset, user type, operator, 
     * and issue date information. 
     *  @return \Illuminate\Http\RedirectResponse Redirects to the Asset Issue 
     *  Register list with success or error message. */

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


    /** 
     *  Return an issued asset to the IT Department. 
     * 
     *  This function marks an existing Asset Issue Register record as returned 
     *  and makes the related Asset Inventory record available for future use. 
     * 
     * Main operations: 
     *  - Decrypts the provided Asset Issue Register ID. 
     *  - Locks the issue record to prevent concurrent return requests. 
     *  - Checks whether the asset has already been returned. 
     *  - Updates the Issue Status to "Returned". 
     *  - Stores the current date as the Returned Date. 
     *  - Updates the related Asset Inventory status to "Available". 
     * 
     *  The row lock prevents multiple users or duplicate requests from 
     *  processing the return operation simultaneously. 
     * 
     *  A database transaction ensures that the Issue Register and Asset 
     *  Inventory records are updated together. If any operation fails, 
     *  all database changes are rolled back. 
     * 
     *  An event log is created after the asset is returned successfully. 
     * 
     *  @param string $id Encrypted Asset Issue Register ID. * @return \Illuminate\Http\JsonResponse Returns the return operation result. */

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


    /** 
     *  Retrieve basic details of an active custodian. 
     * 
     *  This function is generally used through AJAX when a custodian is 
     *  selected on the Asset Issue Register form. 
     * 
     *  The function retrieves the custodian along with related: 
     *  - Designation 
     *  - Discipline/Department 
     *  - Section 
     *  - Location 
     * 
     *  Only custodians with active status are returned. 
     * 
     *  If the requested custodian does not exist or is inactive, a 404 JSON 
     *  response is returned. 
     * 
     *  @param int|string $id Custodian ID. 
     *  @return \Illuminate\Http\JsonResponse Returns custodian details or error message. */

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

    

    /** 
     *  Retrieve complete custodianship details and currently issued assets. 
     * 
     *  This function provides detailed information about a selected custodian 
     *  together with all assets that currently have an Issue Status of "Issued". 
     * 
     *  Main operations: *
     * - Decrypts the Custodian ID. 
     *  - Retrieves custodian profile information. 
     *  - Retrieves currently issued Asset Issue Register records. 
     *  - Loads related Asset Inventory, Asset Model, Asset Type, Location, 
     *  and Station information. 
     *  - Formats the issued asset data for frontend display. 
     * 
     *  The response includes: 
     * 
     *  Custodian Information: 
     *  - Custodian Name 
     *  - Employee ID 
     *  - Email 
     *  - Designation 
     *  - Department/Discipline 
     *  - Section 
     *  - Location 
     *  - Station 
     *  - Active/Inactive Status 
     * 
     *  Issue Summary: 
     *  - Total number of currently issued assets 
     *  - User Type 
     *  - Operator Name 
     *  - First issue date 
     * 
     *  Asset Details: 
     *  - Asset Tag Number 
     *  - Asset Type 
     *  - Asset Model 
     *  - Manufacturer 
     *  - Serial Number 
     *  - Location 
     *  - Station 
     *  - Issued Date 
     *  - Issue Status 
     *  - Remarks 
     * 
     *  @param string $id Encrypted Custodian ID. 
     *  @return \Illuminate\Http\JsonResponse Returns custodian and issued asset details. */

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
    *  Export currently issued asset details of a custodian to an Excel file. 
    * 
    *  This function decrypts the selected Custodian ID and verifies that 
    * the custodian exists before generating the Excel download. 
    * 
    * The exported file is generated using the CustodianAssetExport class. 
    * 
    * The Excel filename is dynamically created using the custodian's name. 
    * Special characters are replaced to create a safe filename. 
    * 
    *  An event log is created to record that custodian asset details were 
    *  downloaded by the currently authenticated user. 
    *  @param string $id Encrypted Custodian ID. 
    *  @return \Symfony\Component\HttpFoundation\BinaryFileResponse| 
    *  \Illuminate\Http\RedirectResponse */

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
    * Retrieve current issue details required before transferring an asset. 
    * 
    * This function retrieves information about an asset that is currently 
    * issued to a custodian. 
    * 
    * Main operations: 
    * - Decrypts the Asset Issue Register ID. 
    * - Verifies that the selected asset currently has "Issued" status. 
    * - Retrieves Asset Inventory details. * - Retrieves the current custodian information. 
    * 
    * The function also retrieves active custodians excluding the current 
    * custodian. These custodians can be used as possible transfer recipients. 
    * 
    * This function is intended to provide the frontend with the information 
    * required to open and populate an Asset Transfer form. 
    * 
    * @param string $id Encrypted Asset Issue Register ID. 
    * @return \Illuminate\Http\JsonResponse Returns current issue details 
    * or an error response. */

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
    * Transfer a currently issued asset from one custodian to another. 
    * 
    * This function completes the asset transfer workflow while preserving 
    * the historical ownership records. 
    * 
    * Main operations: 
    * - Validates the encrypted Issue Register ID. 
    * - Validates the new custodian. 
    * - Validates the transfer date. 
    * - Validates optional transfer remarks. 
    * 
    * The current Asset Issue Register record is locked using lockForUpdate() 
    * to prevent multiple transfer requests from processing the same issue 
    * record simultaneously. 
    * 
    * Business rules enforced: 
    * - Only assets with Issue Status "Issued" can be transferred. 
    * - An asset cannot be transferred to the same custodian. 
    * - The destination custodian must exist and have active status. 
    * 
    * Transfer workflow: 
    * 
    * 1. The existing issue record is updated: 
    * - Issue Status becomes "Transferred". 
    * - Transfer Date is recorded. 
    * - Transfer remarks are stored. 
    * 
    * 2. A permanent Asset Transfer history record is created containing: 
    * - Asset ID 
    * - Previous Custodian 
    * - New Custodian 
    * - Transfer Date 
    * - User who performed the transfer 
    * - Remarks 
    * 
    * 3. A new Asset Issue Register record is created for the destination 
    * custodian with Issue Status "Issued". 
    * 
    * 4. The Asset Inventory status remains "Assigned" because the asset 
    * is still under custodianship and has not been returned. 
    * 
    * A database transaction ensures that all transfer-related database 
    * operations succeed together. If any operation fails, all changes 
    * are rolled back. 
    * 
    * An event log records the asset transfer activity. 
    * 
    * @param Request $request Contains issue ID, destination custodian, 
    * transfer date, and remarks. 
    * @return \Illuminate\Http\JsonResponse Returns transfer success or error details. */

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
                'transfer_date' => $request->transfer_date,
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
                'retained_date'         => null,
                'transfer_date'         => null,                
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


    /** 
    * Export Asset Issue Register records to an Excel file. 
    * 
    * This function creates and downloads an Excel report using the 
    * AssetIssueRegisterExport class. 
    * 
    * The current request is passed to the export class so that the export 
    * can use the same filter parameters submitted by the user, if supported 
    * by the export implementation. 
    * 
    * @param Request $request Contains optional Asset Issue Register filters. 
    * @return \Symfony\Component\HttpFoundation\BinaryFileResponse */

    public function export(Request $request)
    {
        return Excel::download(
            new AssetIssueRegisterExport($request),
            'asset-issue-register.xlsx'
        );
    }


    /** 
    * Mark a currently issued asset as retained by its custodian. 
    * 
    * This function updates both the Asset Issue Register and the related 
    * Asset Inventory record when an asset is retained. 
    * 
    * Main operations: 
    * - Validates the retained date. 
    * - Decrypts the Asset Issue Register ID. 
    * - Retrieves the selected issue record. 
    * - Verifies that the asset is currently in "Issued" status. 
    * 
    * Business rule: 
    * - Only assets that are currently issued can be marked as retained. 
    * 
    * When the retention process succeeds: 
    * - The Asset Issue Register status becomes "Retained". 
    * - The Retained Date is stored. 
    * - The related Asset Inventory status becomes "Retained". 
    * 
    * The updates are performed inside a database transaction to ensure that 
    * the Issue Register and Asset Inventory statuses remain synchronized. 
    * 
    * If any validation or database operation fails, the transaction is 
    * automatically rolled back and an error response is returned. 
    * 
    * @param Request $request Contains the retained date. 
    * @param string $id Encrypted Asset Issue Register ID. 
    * @return \Illuminate\Http\JsonResponse Returns retention success or error details. */
    
    public function retain(Request $request, $id)
    {
        $request->validate([
            'retained_date' => ['required', 'date'],
        ]);

        try {

            $issueId = decryptId($id);

            DB::transaction(function () use ($issueId, $request) {

                $issue = AssetIssueRegister::with('assetInventory')
                    ->findOrFail($issueId);

                if ($issue->issue_status !== 'Issued') {
                    throw new \Exception(
                        'Only currently issued assets can be retained.'
                    );
                }

                $issue->update([
                    'issue_status'  => 'Retained',
                    'retained_date' => $request->retained_date,
                ]);

                if ($issue->assetInventory) {

                    $issue->assetInventory->update([
                        'asset_status' => 'Retained',
                    ]);

                }

            });

            return response()->json([
                'status'  => true,
                'message' => 'Asset successfully retained by the custodian.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 422);

        }
    }
}
