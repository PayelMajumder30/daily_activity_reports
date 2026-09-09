<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\{Location, AirportStation};

class UserConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $users = User::latest()->get();
        return view('user-configure.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $request->session()->forget('success');
        $locations = Location::where('status', 1)->orderBy('name')->get();
        return view('user-configure.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     //
    //     $request->validate([
    //         'name'      =>'required',
    //         'email'     =>'required|email|unique:users,email',
    //         'password'  =>'required|min:6',
    //         'role'      =>'required|in:0,1,2'
    //     ],[
    //         'email.unique' => 'This email already registered',
    //     ]);

    //     User::create([
    //         'name'      =>$request->name,
    //         'email'     =>$request->email,
    //         'password'  =>Hash::make($request->password),
    //         'role'      =>$request->role
    //     ]);

    //     eventLog('Create', 'User', 'Created user: '.$request->name);

    //     return redirect()->route('user-configuration.index')->with('success', 'User added successfully.');
    // }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'role'      => 'required|in:0,1,2',
            'location_id'   => 'required|exists:locations,id',
            'station_id'    => 'required|exists:airport_stations,id',
        ], [
            'email.unique'          => 'This email already registered',
            'location_id.required'  => 'Please select a location / region',
            'station_id.required'   => 'Please select an airport / station',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'location_id'   => $request->location_id,
            'station_id'    => $request->station_id,
        ]);

        eventLog(
            'Create',
            'User',
            'Created user: ' . $request->name
        );

        return redirect()->route('user-configuration.index')->with('success', 'User added successfully.');
                        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        //
        $request->session()->forget('success');
        $user = User::findOrFail(decryptId($id));
        $locations = Location::where('status', 1)->orderBy('name')->get();
    
        return view('user-configure.edit', compact('user', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     // dd($id);
    //     $user = User::findOrFail(decryptId($id));

    //     $request->validate([
    //         'name'=>'required',
    //         'email'=>'required|email|unique:users,email,'.$user->id,
    //         'role'=>'required|in:0,1,2'
    //     ],[
    //         'email.unique'  => 'This email already registered',
    //     ]);

    //     $data=[
    //         'name'=>$request->name,
    //         'email'=>$request->email,
    //         'role'=>$request->role
    //     ];

    //     if($request->filled('password')){
    //         $data['password']=Hash::make($request->password);
    //     }

    //     $user->update($data);

    //     eventLog('Update', 'User', 'Updated user:'.$user->name);

    //     return redirect()->route('user-configuration.index')
    //         ->with('success','User updated successfully.');
    // }

    public function update(Request $request, $id)
    {
        // dd($id);
        $user = User::findOrFail(decryptId($id));

        $request->validate([
            'name'      =>'required',
            'email'     =>'required|email|unique:users,email,'.$user->id,
            'role'      =>'required|in:0,1,2',
            'location_id'   => ['required', 'exists:locations,id',],
            'station_id'    => ['required', Rule::exists('airport_stations', 'id')
                                    ->where(function ($query) use ($request) {
                                        $query->where('location_id', $request->location_id)->where('status', 1);                                               
                                    }),
                                ],
            'password'      => 'nullable|min:6',
        ],[
            'email.unique'  => 'This email already registered',
            'location_id.required'  => 'Please select a region',
            'station_id.required'   => 'Please select an airport/station',
        ]);

        $data=[
            'name'      =>  $request->name,
            'email'     =>  $request->email,
            'role'      =>  $request->role,
            'location_id'   => $request->location_id,
            'station_id'    => $request->station_id,
        ];

        if($request->filled('password')){
            $data['password']=Hash::make($request->password);
        }

        $user->update($data);

        eventLog('Update', 'User', 'Updated user:'.$user->name);

        return redirect()->route('user-configuration.index')
            ->with('success','User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $user = User::findOrFail(decryptId($id));       
        $user->delete();
        return redirect()->route('user-configuration.index')->with('success','User deleted successfully.');           
    }

    public function changeStatus($id){
        
        $user = User::findOrFail(decryptId($id));
        $user->status = !$user->status;
        $user->save();
        eventLog('Status Change', 'User', $user->status ? 'Activated user: '.$user->name : 'Deactivated user: '.$user->name);
        return response()->json([
            'success' => true,
            'status'  => $user->status
        ]);
    }  

    public function getStations($locationId){
        $stations = AirportStation::where('location_id', $locationId)->where('status', 1)->orderBy('station_name')
                                    ->get(['id', 'station_name']);
        return response()->json($stations);
    }
}
