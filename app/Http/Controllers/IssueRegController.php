<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{IssueRegister, AssetInventory, Designation, Discipline, DeptSection};

class IssueRegController extends Controller
{
    //

    public function index(){
        $issueRegisters = IssueRegister::with(['designation', 'discipline', 'deptSection', 'assetInventory'])->latest()->get();

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
        $request->validate([
            'custodian_name' => 'required|string|max:255',

            'designation_id' => 'required|exists:designations,id',

            'discipline_id' => 'required|exists:disciplines,id',

            'section_id' => 'required|exists:dept_sections,id',

            'user_type' => 'required|string|max:255',

            'operator_name' => 'nullable|string|max:255',

            'asset_inventory_id' =>
                'required|exists:asset_inventories,id',
        ], [
            'custodian_name.required' => 'Custodian name is required.',
            'designation_id.required' => 'Designation is required.',
            'discipline_id.required' => 'Department is required.',
            'section_id.required' => 'Section is required.',
            'user_type.required' => 'User type is required.',
            'asset_inventory_id.required' => 'Asset tag is required.',
        ]);


        // Operator name is mandatory only for Operator
        if ($request->user_type === 'operator' &&
            empty($request->operator_name)) {

            return back()
                ->withErrors([
                    'operator_name' =>'Operator name is required.'                       
                ])->withInput();                
        }


        // Make sure selected section belongs
        // to selected department
        $sectionExists = DeptSection::where('id', $request->section_id)
            ->where('discipline_id', $request->discipline_id)
            ->exists();

        if (!$sectionExists) {

            return back()
                ->withErrors([
                    'section_id' =>
                        'Selected section does not belong to the selected department.'
                ])
                ->withInput();
        }

        IssueRegister::create([
            'custodian_name' =>
                $request->custodian_name,

            'designation_id' =>
                $request->designation_id,

            'discipline_id' =>
                $request->discipline_id,

            'section_id' =>
                $request->section_id,

            'user_type' =>
                $request->user_type,

            'operator_name' =>
                $request->user_type === 'operator'
                    ? $request->operator_name
                    : null,

            'asset_inventory_id' =>
                $request->asset_inventory_id,

            'status' => 1,
        ]);


        return redirect()->route('issue-register.index')->with('success','Issue register added successfully.');
    }

    public function getSections($id){
        $departmentId  = decryptId($id);
        $sections = DeptSection::where('discipline_id', $departmentId)->where('status', 1)->orderBy('section_name')->get(['id', 'section_name']);
        return response()->json($sections);
    }
}
