<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\{AssetInventory, AssetType, AssetModel, Custodian, Designation, Discipline, DeptSection, Location, AirportStation};

class CustodianController extends Controller
{
    //

    /** 
    * Display the Custodian list with optional search filters. 
    * 
    * This function retrieves all custodian records along with their related 
    * Designation, Department/Discipline, and Section information. 
    * 
    * The custodian list can be filtered by: 
    * - Custodian Name 
    * - Employee ID 
    * - Department 
    * - Designation 
    * - Airport/Station 
    * 
    * Related models are eager loaded to reduce unnecessary database queries 
    * when displaying custodian information in the listing page. 
    * 
    * The function also retrieves active Departments, Designations, and 
    * Airport/Stations for use in the search filter dropdowns. 
    * 
    * @param Request $request Contains optional search and filter parameters. 
    * @return \Illuminate\View\View Returns the Custodian listing page. */

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


    /** 
    * Display the form used to create a new Custodian. 
    * 
    * This function retrieves all active master data required to populate 
    * the custodian creation form. 
    * 
    * The form receives: 
    * - Active Designations 
    * - Active Departments/Disciplines 
    * - Active Locations/Regions 
    * - Active Airport/Stations 
    * 
    * Only records with active status are loaded to prevent inactive 
    * master data from being assigned to a new custodian. 
    * 
    * @return \Illuminate\View\View Returns the Custodian creation page. */

    public function create(){

        $designations   = Designation::where('status', 1)->orderBy('name')->get();
        $departments    = Discipline::where('status', 1)->orderBy('name')->get();
        $locations      = Location::where('status', 1)->orderBy('name')->get();     
        $stations       = AirportStation::where('status', 1)->orderBy('station_name')->get();
        return view('custodian.create', compact('designations', 'departments', 'locations', 'stations'));
    }

    

    /** 
    * Create and store a new Custodian record. 
    * 
    * This function validates the submitted custodian information and performs 
    * additional business validation before creating the record. 
    * 
    * Main validation includes: 
    * - Custodian name is required. 
    * - Designation must exist. 
    * - Department/Discipline must exist and be active. 
    * - Location must exist and be active. 
    * - Station must exist. 
    * - Employee ID must contain exactly 8 digits. 
    * - Employee ID must be unique. 
    * - Email address must be valid and unique. 
    * - Phone number is optional. 
    * 
    * Additional business validation: 
    * 
    * 1. Location Validation 
    * - Confirms that the selected location exists and is active. 
    * 
    * 2. Station and Location Relationship Validation 
    * - Confirms that the selected Airport/Station belongs to the 
    * selected Location/Region. 
    * 
    * 3. Department Validation 
    * - Confirms that the selected Department/Discipline is active. 
    * 
    * 4. Section Validation 
    * - If a section is selected, it must belong to the selected 
    * Department/Discipline. 
    * - The selected section must also be active. 
    * 
    * After all validation is completed successfully, a new Custodian 
    * record is created with active status. 
    * 
    * @param Request $request Contains the new custodian information. 
    * @return \Illuminate\Http\RedirectResponse Redirects to the Custodian 
    * listing page with success 
    * or validation error messages. */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'custodian_name'    => ['required', 'string', 'max:255',],
            'designation_id'    => ['required', 'exists:designations,id',],                                  
            'discipline_id'     => ['required', Rule::exists('disciplines', 'id')                              
                                        ->where(function ($query) {$query->where('status', 1); }),],                                                                                                              
            'location_id'       => ['required', 'exists:locations,id',],
            'station_id'        => ['required', 'exists:airport_stations,id',],     
            'emp_id'            => ['required', 'digits:8', 'unique:custodians,emp_id',],
            'email'             => ['required', 'email', 'max:255','unique:custodians,email',],                                                          
            'phone'             => ['nullable', 'string', 'max:10',],
                                             
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
                ])->withInput();               
        }


     


        /*
        |--------------------------------------------------------------------------
        | Section Handling
        |--------------------------------------------------------------------------
        */

        $sectionId = null;

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
            ->with('success', 'Custodian added successfully.');
                                         
    }

    /** 
    * Display the form used to edit an existing Custodian. 
    * 
    * This function decrypts the provided Custodian ID and retrieves the 
    * selected custodian along with related master information. 
    * 
    * The function loads: 
    * - Custodian details 
    * - Current Designation 
    * - Current Department/Discipline 
    * - Current Section 
    * - Current Location 
    * - Current Airport/Station 
    * 
    * It also retrieves active master data required by the edit form: 
    * - Active Designations 
    * - Active Departments 
    * - Active Locations 
    * - Active Sections belonging to the custodian's current department 
    * 
    * The existing success session message is cleared to avoid displaying 
    * a previous success message when opening the edit page. 
    * 
    * If the encrypted ID cannot be decrypted, the user is redirected to 
    * the Custodian listing page with an error message. 
    * 
    * @param Request $request Used to manage session messages. 
    * @param string $id Encrypted Custodian ID. 
    * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse */

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
    * Update an existing Custodian record. 
    * 
    * This function decrypts the Custodian ID, retrieves the existing record, 
    * validates the submitted information, and updates the custodian details. 
    * 
    * Main validation includes: 
    * - Custodian name is required. 
    * - Designation must exist. 
    * - Department/Discipline must exist. 
    * - Location must exist. 
    * - Station must exist. 
    * - Employee ID must contain exactly 8 digits. 
    * - Employee ID must remain unique. 
    * - The current custodian's Employee ID is excluded from the unique check. 
    * - Email must be valid when provided. 
    * - Phone number must contain exactly 10 digits when provided. 
    * 
    * Additional business validation: 
    * 
    * 1. Department Validation 
    * - Confirms that the selected Department/Discipline is active. 
    * 
    * 2. Section Requirement 
    * - Checks whether the selected Department contains active sections. 
    * - If sections exist, selecting a section becomes mandatory. 
    * 
    * 3. Section and Department Relationship 
    * - Confirms that the selected section belongs to the selected 
    * Department/Discipline. 
    * - The selected section must be active. 
    * 
    * 4. Station and Location Relationship 
    * - Confirms that the selected Airport/Station belongs to the 
    * selected Location/Region. 
    * - The selected station must be active. 
    * 
    * If the selected Department does not contain any active sections, 
    * the section_id is stored as NULL. 
    * 
    * After all validation passes, the existing Custodian record is updated 
    * with the new information. 
    * 
    * @param Request $request Contains updated custodian information. 
    * @param string $id Encrypted Custodian ID. 
    * @return \Illuminate\Http\RedirectResponse Redirects to the Custodian 
    * listing page with success 
    * or validation error messages. */

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


    /** 
    * Retrieve all active sections belonging to a selected Department. 
    * 
    * This function is typically called through AJAX when a user selects 
    * a Department/Discipline in the Custodian form. 
    * 
    * The function: 
    * - Finds sections using the selected Department ID. 
    * - Returns only active sections. 
    * - Orders sections alphabetically by section name. 
    * - Returns only the ID and Section Name required by the frontend. 
    * 
    * This allows the Section dropdown to be dynamically populated based 
    * on the selected Department. 
    * 
    * @param int|string $id Department/Discipline ID. 
    * @return \Illuminate\Http\JsonResponse Returns a list of active sections. */

    public function sections($id){

        $sections = DeptSection::where('discipline_id', $id)->where('status', 1)->orderBy('section_name')->get(['id', 'section_name',]);                        
        return response()->json($sections);
    }


    /** 
    * Change the active status of a Custodian. 
    * 
    * This function toggles the current status of the selected Custodian. 
    * 
    * Status behavior: 
    * - Active (1) becomes Inactive (0). 
    * - Inactive (0) becomes Active (1). 
    * 
    * The Custodian ID is decrypted before retrieving the record. 
    * 
    * After the status is changed, an Event Log entry is created to record 
    * whether the custodian was activated or deactivated. 
    * 
    * This function is generally used through AJAX from a status toggle 
    * switch on the Custodian listing page. 
    * 
    * @param string $id Encrypted Custodian ID. 
    * @return \Illuminate\Http\JsonResponse Returns the updated status. */
    
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
