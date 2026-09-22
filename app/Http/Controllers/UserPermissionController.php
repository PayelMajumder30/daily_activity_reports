<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\{User, UserLocationPermission, Location, AirportStation, Custodian};

class UserPermissionController extends Controller
{
    //
     /**
     * Display location/station permissions.
     */
    // public function index(Request $request)
    // {
    //     // Only Call Coordinators / Uploaders
    //     $users = User::where('role', 1)
    //         ->where('status', 1)
    //         ->orderBy('name')
    //         ->get();

    //     $locations = Location::with([
    //         'airportStation' => function ($query) {
    //             $query->where('status', 1)
    //                 ->orderBy('station_name');
    //         }
    //     ])
    //     ->where('status', 1)
    //     ->orderBy('name')
    //     ->get();

    //     $selectedUser = null;
    //     $permissionStationIds = [];

    //     if ($request->filled('user_id')) {

    //         $selectedUser = User::where('role', 1)
    //             ->where('id', $request->user_id)
    //             ->first();

    //         if ($selectedUser) {

    //             $permissionStationIds = UserLocationPermission::where(
    //                 'user_id',
    //                 $selectedUser->id
    //             )->pluck('station_id')->toArray();               
                
    //         }
    //     }

    //     return view('user-location-permission.index', compact('users', 'locations', 'selectedUser', 'permissionStationIds'));    
    // }

    public function index(Request $request)
    {
        $users = User::where('role', 1)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $locations = Location::with([
            'airportStation' => function ($query) {
                $query->where('status', 1)
                    ->orderBy('station_name');
            }
        ])
        ->where('status', 1)
        ->orderBy('name')
        ->get();

        $selectedUser = null;
        $permissionStationIds = [];

        if ($request->filled('user_id')) {

            try {
                // Decrypt encrypted user ID
                $userId = decryptId($request->user_id);

                // Fetch only Call Coordinator
                $selectedUser = User::where('role', 1)
                    ->where('id', $userId)
                    ->first();

                if ($selectedUser) {
                    $permissionStationIds = UserLocationPermission::where(
                        'user_id',
                        $selectedUser->id
                    )
                    ->pluck('station_id')
                    ->toArray();
                }

            } catch (DecryptException $e) {
                abort(404, 'Invalid user ID.');
            }
        }

        return view('user-location-permission.index',compact('users', 'locations', 'selectedUser', 'permissionStationIds'));      
    
    }

    /**
     * Save user location/station permissions.
     */
    public function store(Request $request)
    {

        try {
            $userId = decryptId($request->user_id);
        } catch (DecryptException $e) {
            abort(404, 'Invalid user ID.');
        }

        $request->merge([
            'user_id' => $userId
        ]);

        $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'stations' => [
                'nullable',
                'array',
            ],

            'stations.*' => [
                'integer',
                'exists:airport_stations,id',
            ],
        ]);


        $user = User::where('role', 1)
            ->where('id', $request->user_id)
            ->firstOrFail();

        $stationIds = collect($request->stations ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values();


        /*
        |--------------------------------------------------------------------------
        | Validate station -> location relationship
        |--------------------------------------------------------------------------
        */

        $stations = AirportStation::whereIn('id', $stationIds)->get();           

        DB::transaction(function () use (
            $user,
            $stationIds,
            $stations
        ) {

            // Remove old permissions
            UserLocationPermission::where(
                'user_id',
                $user->id
            )->delete();


            /*
            |--------------------------------------------------------------------------
            | Insert new permissions
            |--------------------------------------------------------------------------
            */

            foreach ($stations as $station) {

                UserLocationPermission::create([
                    'user_id'     => $user->id,
                    'location_id' => $station->location_id,
                    'station_id'  => $station->id,
                ]);
            }
        });

         /*
        |--------------------------------------------------------------------------
        | Event Log
        |--------------------------------------------------------------------------
        */
        $stationNames = $stations->pluck('station_name')->implode(', ');
        eventLog('Update', 'User Location Permission', 'Updated location/station permissions for Call Coordinator: '. $user->name . ' (' . $user->email . '). '
                    .'Assigned Stations: ' . ($stationNames ?: 'None') . '. Total Stations: ' . $stations->count());


        return redirect()->route('user-location-permission.index', ['user_id' => encryptId($user->id)])
                            ->with('success', 'Location and station permissions updated successfully.');
                                
                            
    }

}
