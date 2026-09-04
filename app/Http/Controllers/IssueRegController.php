<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{IssueRegister, AssetInventory, Designation, Discipline, DeptSection};

class IssueRegController extends Controller
{
    //

    public function index(Request $request){

        $query = IssueRegister::with(['designation', 'discipline', 'deptSection', 'assetInventory']);

           // custodian name
        if ($request->filled('custodian_name')) {
            $query->where('custodian_name', 'LIKE','%' . $request->custodian_name . '%');
        }

        // Employee Id
        if ($request->filled('emp_id')) {
            $query->where('emp_id', 'LIKE', '%' . $request->emp_id . '%');
        }

        // tag no.
        if ($request->filled('tag_no')) {
            $query->whereHas('assetInventory', function ($q) use ($request) {
                $q->where('tag_no', 'LIKE', '%'. $request->tag_no . '%');
            });
        }

        // $issueRegisters = IssueRegister::with(['designation', 'discipline', 'deptSection', 'assetInventory'])->latest()->get();
        $issueRegisters = $query->latest()->get();

        return view('issue-register.index', compact('issueRegisters'));
    }

    public function create(){
        $designations = Designation::where('status', 1)->orderBy('name')->get();
        $departments = Discipline::where('status', 1)->orderBy('name')->get();
        $assets = AssetInventory::where('status', 1)->orderBy('tag_no')->get();

        return view('issue-register.create', compact('designations', 'departments', 'assets'));
    }


    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'custodian_name' => [
                'required',
                'string',
                'max:255'
            ],

            'designation_id' => [
                'required',
                'exists:designations,id'
            ],

            'discipline_id' => [
                'required',
                'string'
            ],

            'user_type' => [
                'required',
                'string',
                'in:self,multiuser,operator'
            ],

            'operator_name' => [
                'nullable',
                'string',
                'max:255'
            ],

            'emp_id' => [
                'required',
                'digits:8',
            ],

            'asset_inventory_id' => [
                'required',
                'exists:asset_inventories,id'
            ],

        ], [

            'custodian_name.required' =>  'Custodian name is required.',               
            'designation_id.required' => 'Designation is required.',                
            'designation_id.exists' => 'Selected designation is invalid.',               
            'discipline_id.required' => 'Department is required.',                
            'user_type.required' => 'User type is required.',                
            'user_type.in' => 'Please select a valid user type.',               
            'emp_id.required' => 'Employee ID is required.',               
            'emp_id.digits' => 'Please enter an 8 digit employee ID.',
            'asset_inventory_id.required' => 'Asset tag is required.',               
            'asset_inventory_id.exists' => 'Selected asset tag is invalid.',
                
        ]);

        /*
        |--------------------------------------------------------------------------
        | Decrypt Department ID
        |--------------------------------------------------------------------------
        */

        try {

            $disciplineId = decryptId($request->discipline_id);

        } catch (\Throwable $e) {

            return back()
                ->withErrors([
                    'discipline_id' =>
                        'Invalid department selected.'
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Department Exists
        |--------------------------------------------------------------------------
        */

        $discipline = Discipline::where('id', $disciplineId)
            ->where('status', 1)
            ->first();

        if (!$discipline) {

            return back()
                ->withErrors([
                    'discipline_id' =>
                        'Invalid department selected.'
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Operator Name Validation
        |--------------------------------------------------------------------------
        */

        if ($request->user_type === 'operator') {

            if (!$request->filled('operator_name')) {

                return back()
                    ->withErrors([
                        'operator_name' =>
                            'Operator name is required.'
                    ])
                    ->withInput();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Check Whether Department Has Sections
        |--------------------------------------------------------------------------
        */

        $hasSections = DeptSection::where('discipline_id', $disciplineId)
            ->where('status', 1)
            ->exists();


        /*
        |--------------------------------------------------------------------------
        | Section Validation
        |--------------------------------------------------------------------------
        */

        $sectionId = null;
        if ($hasSections) {

            /*
            |----------------------------------------------------------------------
            | Section is required
            |----------------------------------------------------------------------
            */

            if (!$request->filled('section_id')) {

                return back()
                    ->withErrors([
                        'section_id' =>
                            'Section is required.'
                    ])
                    ->withInput();
            }

            /*
            |----------------------------------------------------------------------
            | Check Section Belongs To Department
            |----------------------------------------------------------------------
            */

            $sectionExists = DeptSection::where('id', $request->section_id)
                ->where('discipline_id', $disciplineId)
                ->where('status', 1)
                ->exists();

            if (!$sectionExists) {

                return back()
                    ->withErrors([
                        'section_id' =>
                            'Selected section does not belong to the selected department.'
                    ])
                    ->withInput();
            }

            $sectionId = $request->section_id;
        }


        /*
        |--------------------------------------------------------------------------
        | Store Issue Register
        |--------------------------------------------------------------------------
        */

        IssueRegister::create([

            'custodian_name' => $request->custodian_name,                
            'designation_id' => $request->designation_id,                
            'discipline_id' => $disciplineId,                
            'section_id' => $sectionId,                
            'user_type' => $request->user_type,               
            'operator_name' =>
                $request->user_type === 'operator'
                    ? $request->operator_name
                    : null,

            'emp_id' => $request->emp_id,               
            'asset_inventory_id' => $request->asset_inventory_id,                
            'status' => 1,               
        ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()->route('issue-register.index')->with('success', 'Issue register added successfully.');
           
    }

    public function getSections($id){
        $departmentId  = decryptId($id);
        $sections = DeptSection::where('discipline_id', $departmentId)->where('status', 1)->orderBy('section_name')->get(['id', 'section_name']);
        return response()->json($sections);
    }

    public function employeeAssets($empId)
    {
        $issueRegisters = IssueRegister::with([
            'designation',
            'discipline',
            'deptSection',
            'assetInventory.assetModel',
            'assetInventory.location',
        ])
        ->where('emp_id', $empId)
        ->where('status', 1)
        ->get();

        if ($issueRegisters->isEmpty()) {

            return response()->json([
                'status'  => false,
                'message' => 'No issued assets found.',
            ]);
        }

        $first = $issueRegisters->first();
        return response()->json([
            'status' => true,
            'employee' => [
                'custodian_name'    => $first->custodian_name,                    
                'emp_id'            => $first->emp_id,                    
                'designation'       => $first->designation?->name,                    
                'department'        => $first->discipline?->name,                    
                'section'           => $first->deptSection?->section_name,                    
                'user_type'         => ucfirst($first->user_type),                    
                'operator_name'     => $first->operator_name,                   
            ],

            'assets' => $issueRegisters->map(function ($issue) {
                $asset = $issue->assetInventory;

                return [

                    'tag_no'        => $asset?->tag_no,                        
                    'asset_model'   => $asset?->assetModel?->model_name,                        
                    'serial_no'     => $asset?->serial_no,                        
                    'location'      => $asset?->location?->name,                       
                    'po_number'     => $asset?->po_number,                       
                    'installation_date' => $asset?->installation_date,                       
                    'warranty_end'  => $asset?->warranty_end,                        
                    'asset_status'  => $asset?->asset_status,                        
                    'issued_date'   => $issue->created_at?->format('d M Y'),                       
                ];

            })->values(),

        ]);
    }
}
