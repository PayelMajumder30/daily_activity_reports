<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return view('asset-issue-register.index', compact('issueRegisters'));
    }

    // public function create(){
    //     /*
    //     | Only Available assets can be issued
    //     */
    //     $assets = AssetInventory::with(['assetModel.assetType', 'location'])->where('status', 1)->where('asset_status', 'Available')->orderBy('tag_no')->get();

    //     /*
    //     | Active custodians
    //     */
    //     $custodians = Custodian::with(['designation', 'discipline', 'section'])->where('status', 1)->orderBy('custodian_name')->get();

    //     return view('asset-issue-register.create', compact('assets', 'custodians'));
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'asset_inventory_id'    => ['required', 'exists:asset_inventories,id'],
    //         'custodian_id'          => ['required', 'exists:custodians,id'],
    //         'user_type'             => ['required', 'in:self,multiuser,operator'],
    //         'operator_name'         => ['nullable', 'string', 'max:255'],
    //         'issued_date'           => [ 'required', 'date'],
    //         'remarks'               => ['nullable', 'string'],
    //     ], [

    //         'asset_inventory_id.required'   => 'Please select an asset.',              
    //         'custodian_id.required'         => 'Please select a custodian.',             
    //         'user_type.required'            => 'User type is required.',               
    //         'issued_date.required'          => 'Issue date is required.',              
    //     ]);

    //     /*
    //     | Operator validation
    //     */

    //     if (
    //         $request->user_type === 'operator'
    //         && !$request->filled('operator_name')
    //     ) {

    //         return back()->withErrors(['operator_name' => 'Operator name is required.'])->withInput();      
    //     }

    //     DB::beginTransaction();

    //     try {

    //         /*
    //         | Lock asset
    //         */

    //         $asset = AssetInventory::lockForUpdate()
    //             ->findOrFail(
    //                 $request->asset_inventory_id
    //             );

    //         /*
    //         | Make sure asset is still available
    //         */

    //         if ($asset->asset_status !== 'Available') {

    //             DB::rollBack();

    //             return back()
    //                 ->withErrors([
    //                     'asset_inventory_id' =>
    //                         'This asset is no longer available.'
    //                 ])
    //                 ->withInput();
    //         }

    //         /*
    //         | Create issue record
    //         */

    //         AssetIssueRegister::create([

    //             'asset_inventory_id' =>
    //                 $asset->id,

    //             'custodian_id' =>
    //                 $request->custodian_id,

    //             'user_type' =>
    //                 $request->user_type,

    //             'operator_name' =>
    //                 $request->user_type === 'operator'
    //                     ? $request->operator_name
    //                     : null,

    //             'issued_date' =>
    //                 $request->issued_date,

    //             'issue_status' =>
    //                 'Issued',

    //             'remarks' =>
    //                 $request->remarks,

    //         ]);

    //         /*
    //         | Change asset status
    //         */

    //         $asset->update([
    //             'asset_status' => 'Assigned'
    //         ]);

    //         DB::commit();

    //         return redirect()
    //             ->route('asset-issue-register.index')
    //             ->with(
    //                 'success',
    //                 'Asset issued successfully.'
    //             );

    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return back()
    //             ->withErrors([
    //                 'error' =>
    //                     'Unable to issue asset.'
    //             ])
    //             ->withInput();
    //     }
    // }
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
        ])
        ->where('id', $id)
        ->where('status', 1)
        ->first();

        if (!$custodian) {

            return response()->json([
                'status' => false,
                'message' => 'Custodian not found.'
            ], 404);
        }


        return response()->json([

            'status' => true,

            'custodian' => [

                'emp_id' =>
                    $custodian->emp_id,

                'designation' =>
                    $custodian->designation?->name,

                'department' =>
                    $custodian->discipline?->name,

                'section' =>
                    $custodian->section?->section_name,

            ]

        ]);
    }
}
