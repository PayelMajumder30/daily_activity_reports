<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{User, UserLocationPermission, Location, AirportStation, Custodian};

class UserPermissionController extends Controller
{
    //
     /**
     * Display location/station permissions.
     */
    public function index(Request $request)
    {
        // Only Call Coordinators / Uploaders
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

            $selectedUser = User::where('role', 1)
                ->where('id', $request->user_id)
                ->first();

            if ($selectedUser) {

                $permissionStationIds = UserLocationPermission::where(
                    'user_id',
                    $selectedUser->id
                )->pluck('station_id')->toArray();               
                
            }
        }

        return view('user-location-permission.index', compact('users', 'locations', 'selectedUser', 'permissionStationIds'));    
    }

    /**
     * Save user location/station permissions.
     */
    public function store(Request $request)
    {
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


        return redirect()->route('user-location-permission.index', ['user_id' => $user->id])
                            ->with('success', 'Location and station permissions updated successfully.');
                                
                            
    }

}
