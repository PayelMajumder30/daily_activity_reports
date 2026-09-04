<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\{AssetInventory, AssetType, AssetModel, Custodian, Designation, Discipline, DeptSection, Location, AirportStation};

class CustodianController extends Controller
{
    //

    public function index(Request $request){

        $query = Custodian::with(['designation', 'discipline', 'section']);

        /* search custodian name*/
        if($request->filled('custodian_name')){
            $query->where('custodian_name', 'LIKE', '%' . $request->custodian_name . '%');
        }

        /* search Employee ID*/
        if($request->filled('emp_id')){
            $query->where('emp_id', 'LIKE', '%' . $request->emp_id . '%');
        }

        /* search by department*/
        if($request->filled('discipline_id')) {
            $query->where('discipline_id', 'LIKE', '%' . $request->discipline_id . '%');
        }

        /* search by designation*/
        if($request->filled('designation_id')) {
            $query->where('designation_id', 'LIKE', '%' . $request->designation_id . '%');
        }

        /* search by airport/station*/
        if($request->filled('station_id')) {
            $query->where('station_id', 'LIKE', '%' . $request->station_id . '%');
        }

        $custodians = $query->latest()->get();

        /* Departments*/
        $departments = Discipline::where('status', 1)->orderBy('name')->get();

        /* Designations*/
        $designations = Designation::where('status', 1)->orderBy('name')->get();

        /* Airport/Station*/
        $stations = AirportStation::where('status', 1)->orderBy('station_name')->get();
        
        return view('custodian.index', compact('custodians', 'departments', 'designations', 'stations'));
    }

    public function create(){

        $designations = Designation::where('status', 1)->orderBy('name')->get();
        $departments = Discipline::where('status', 1)->orderBy('name')->get();
        $locations = Location::where('status', 1)->orderBy('name')->get();     
        $stations = AirportStation::where('status', 1)->orderBy('station_name')->get();
        return view('custodian.create', compact('designations', 'departments', 'locations', 'stations'));
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
                'max:255',
            ],

            'designation_id' => [
                'required',
                'exists:designations,id',
            ],

            'discipline_id' => [
                'required',
                Rule::exists('disciplines', 'id')
                    ->where(function ($query) {
                        $query->where('status', 1);
                    }),
            ],

            'location_id' => [
                'required',
                'exists:locations,id',
            ],

            'station_id' => [
                'required',
                'exists:airport_stations,id',
            ],
           
            'emp_id' => [
                'required',
                'digits:8',
                'unique:custodians,emp_id',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:custodians,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:10',
            ],

        ], [

            'custodian_name.required'   => 'Custodian name is required.',                
            'designation_id.required'   => 'Designation is required.',               
            'designation_id.exists'     => 'Selected designation is invalid.',                
            'discipline_id.required'    => 'Department is required.',                
            'discipline_id.exists'      => 'Selected department is invalid.',               
            'location_id.required'      => 'Region is required.',               
            'station_id.required'       => 'Station is required.',               
            'station_id.exists'         => 'Selected station is invalid.',               
            'emp_id.required'           => 'Employee ID is required.',              
            'emp_id.digits'             => 'Please enter an 8 digit employee ID.',                
            'emp_id.unique'             => 'This Employee ID already exists.',               
            'email.required'            => 'Email is required.',             
            'email.email'               => 'Please enter a valid email address.',            
            'email.unique'              => 'This email address already exists.',
                
        ]);


        /*
        |--------------------------------------------------------------------------
        | Decrypt Location
        |--------------------------------------------------------------------------
        */

        try {

            $locationId = $request->location_id;

        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    'location_id' => 'Invalid location selected.'
                ])->withInput();             
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Location
        |--------------------------------------------------------------------------
        */

        $location = Location::where('id', $locationId)
            ->where('status', 1)
            ->first();

        if (!$location) {

            return back()
                ->withErrors([
                    'location_id' => 'Invalid location selected.'
                ])->withInput();               
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Station Belongs To Location
        |--------------------------------------------------------------------------
        */

        $station = AirportStation::where('id', $request->station_id)
            ->where('location_id', $request->location_id)
            ->where('status', 1)
            ->first();

        if (!$station) {

            return back()
                ->withErrors([
                    'station_id' =>
                        'Selected station does not belong to the selected location.'
                ])->withInput();              
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Department
        |--------------------------------------------------------------------------
        */

        $discipline = Discipline::where('id', $request->discipline_id)
            ->where('status', 1)
            ->first();

        if (!$discipline) {

            return back()
                ->withErrors([
                    'discipline_id' => 'Invalid department selected.'                       
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Check Whether Department Has Sections
        |--------------------------------------------------------------------------
        */

        // $hasSections = DeptSection::where(
        //     'discipline_id',
        //     $request->discipline_id
        // )
        // ->where('status', 1)
        // ->exists();


        /*
        |--------------------------------------------------------------------------
        | Section Handling
        |--------------------------------------------------------------------------
        */

        $sectionId = null;

        // if ($hasSections) {
        //     if (!$request->filled('section_id')) {
        //         return back()
        //             ->withErrors([
        //                 'section_id' => 'Section is required.'                          
        //             ])->withInput();                 
        //     }

        //     $sectionExists = DeptSection::where('id', $request->section_id)
        //         ->where('discipline_id', $request->discipline_id)
        //         ->where('status', 1)
        //         ->exists();

        //     if (!$sectionExists) {
        //         return back()
        //             ->withErrors(['section_id' => 'Selected section does not belong to the selected department.'                                             
        //             ])->withInput();
                    
        //     }
        //     $sectionId = $request->section_id;
        // }

        if ($request->filled('section_id')) {

            $sectionExists = DeptSection::where('id', $request->section_id)
                ->where('discipline_id', $request->discipline_id)
                ->where('status', 1)
                ->exists();

            if (!$sectionExists) {
                return back()
                    ->withErrors([
                        'section_id' => 'Selected section does not belong to the selected department.'                           
                    ])->withInput();                  
            }

            $sectionId = $request->section_id;
        }


        /*
        |--------------------------------------------------------------------------
        | Store Custodian
        |--------------------------------------------------------------------------
        */

        Custodian::create([

            'custodian_name'    => $request->custodian_name,
            'designation_id'    => $request->designation_id,
            'discipline_id'     => $request->discipline_id,
            'section_id'        => $sectionId,
            'location_id'       => $request->location_id,
            'station_id'        => $request->station_id,
            'emp_id'            => $request->emp_id,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'status'            => 1,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('custodian.index')
            ->with(
                'success',
                'Custodian added successfully.'
            );
    }

      /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $request->session()->forget('success');

        try {
            $custodianId = decryptId($id);

        } catch (\Throwable $e) {

            return redirect()
                ->route('custodian.index')
                ->with('error', 'Invalid custodian selected.');
        }

        $custodian = Custodian::with([
            'designation',
            'discipline',
            'section',
            'location',
            'station',
        ])->findOrFail($custodianId);

        $designations = Designation::where('status', 1)
            ->orderBy('name')
            ->get();

        $departments = Discipline::where('status', 1)
            ->orderBy('name')
            ->get();

        $locations = Location::where('status', 1)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Get sections of selected department
        |--------------------------------------------------------------------------
        */

        $sections = DeptSection::where(
            'discipline_id',
            $custodian->discipline_id
        )->where('status', 1)->orderBy('section_name')->get();
                     

        return view('custodian.edit', compact(
            'custodian',
            'designations',
            'departments',
            'locations',
            'sections'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | Decrypt Custodian ID
        |--------------------------------------------------------------------------
        */

        try {
            $custodianId = decryptId($id);
        } catch (\Throwable $e) {
            return redirect()->route('custodian.index')->with('error', 'Invalid custodian selected.');                             
        }

        /*
        |--------------------------------------------------------------------------
        | Find Custodian
        |--------------------------------------------------------------------------
        */

        $custodian = Custodian::findOrFail($custodianId);

        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'custodian_name' => [
                'required',
                'string',
                'max:255',
            ],

            'designation_id' => [
                'required',
                'exists:designations,id',
            ],

            'discipline_id' => [
                'required',
                'exists:disciplines,id',
            ],

            'location_id' => [
                'required',
                'exists:locations,id',
            ],

            'station_id' => [
                'required',
                'exists:airport_stations,id',
            ],

            'emp_id' => [
                'required',
                'digits:8',
                'unique:custodians,emp_id,' . $custodian->id,
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                // 'unique:custodians,email,' . $custodian->email,
            ],

            'phone' => [
                'nullable',
                'digits:10',
            ],

        ], [

            'custodian_name.required'   => 'Custodian name is required.',               
            'designation_id.required'   => 'Designation is required.',               
            'designation_id.exists'     => 'Selected designation is invalid.',               
            'discipline_id.required'    => 'Department is required.',               
            'discipline_id.exists'      => 'Selected department is invalid.',              
            'emp_id.required'           => 'Employee ID is required.',             
            'emp_id.digits'             => 'Please enter an 8 digit employee ID.',              
            'emp_id.unique'             => 'This Employee ID already exists.',              
            'email.email'               => 'Please enter a valid email address.',             
            'phone.digits'              => 'Please enter a valid 10 digit phone number.',             
                                 

        ]);

       
        /*
        |--------------------------------------------------------------------------
        | Validate Department
        |--------------------------------------------------------------------------
        */

        $discipline = Discipline::where('id', $request->discipline_id)
            ->where('status', 1)
            ->first();

        if (!$discipline) {
            return back()->withErrors(['discipline_id' => 'Invalid department selected.'])->withInput();      
        }


        /*
        |--------------------------------------------------------------------------
        | Check Department Sections
        |--------------------------------------------------------------------------
        */

        $hasSections = DeptSection::where(
            'discipline_id',
            $request->discipline_id
        )
        ->where('status', 1)
        ->exists();


        /*
        |--------------------------------------------------------------------------
        | Section Handling
        |--------------------------------------------------------------------------
        */

        $sectionId = null;

        if ($hasSections) {

            /*
            | Department has sections
            | Therefore section is required
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
            | Check section belongs to department
            */

            $sectionExists = DeptSection::where(
                'id',
                $request->section_id
            )
            ->where(
                'discipline_id',
                $request->discipline_id
            )
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

        } else {

            /*
            | Department has no sections
            | Store NULL
            */

            $sectionId = null;
        }

        $station = AirportStation::where('id', $request->station_id)
            ->where('location_id', $request->location_id)
            ->where('status', 1)
            ->first();

        if (!$station) {

            return back()
                ->withErrors([
                    'station_id' =>
                        'Selected station does not belong to the selected region.'
                ])
                ->withInput();
        }
        /*
        |--------------------------------------------------------------------------
        | Update Custodian
        |--------------------------------------------------------------------------
        */

        // $custodian->update([

        //     'custodian_name' =>
        //         $request->custodian_name,

        //     'designation_id' =>
        //         $request->designation_id,

                
        //     'location_id' =>
        //         $request->location_id,

        //     'discipline_id' =>
        //         $request->discipline_id,

        //     'section_id' =>
        //         $sectionId,

        //     'emp_id' =>
        //         $request->emp_id,

        //     'email' =>
        //         $request->email,

        //     'phone' =>
        //         $request->phone,

        // ]);
        $custodian->update([

            'custodian_name' => $request->custodian_name,
            'designation_id' => $request->designation_id,
            'location_id' => $request->location_id,
            'station_id' => $request->station_id,       
            'discipline_id' => $request->discipline_id,
            'section_id' => $sectionId,
            'emp_id' => $request->emp_id,
            'email' => $request->email,
            'phone' => $request->phone,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('custodian.index')
            ->with(
                'success',
                'Custodian updated successfully.'
            );
    }

    public function sections($id){

        $sections = DeptSection::where('discipline_id', $id)->where('status', 1)->orderBy('section_name')->get(['id', 'section_name',]);                        
        return response()->json($sections);
    }

    public function changeStatus($id){
        
        $custodian = Custodian::findOrFail(decryptId($id));
        $custodian->status = !$custodian->status;
        $custodian->save();

        eventLog('Status Change', 'Custodian', $custodian->status ? 'Activated custodian: '.$custodian->custodian_name : 'Deactivated custodian: '.$custodian->custodian_name);
        return response()->json([
            'success' => true,
            'status'  => $custodian->status
        ]);
    }  
    
}
