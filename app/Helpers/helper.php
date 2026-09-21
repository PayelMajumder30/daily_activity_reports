<?php

use Illuminate\Support\Facades\Crypt;
use App\Models\{EventLog, AssetType, AssetInventory, Location, AirportStation, UserLocationPermission};

if (!function_exists('encryptId')) {
    function encryptId($id)
    {
        return Crypt::encryptString($id);
    }
}

if (!function_exists('decryptId')) {
    function decryptId($encryptedId)
    {
        return Crypt::decryptString($encryptedId);
    }
}

// for enventlog
if(!function_exists('eventLog')) {
    function eventLog($action, $module, $description = null) {
        EventLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}

// for tag generate
if (!function_exists('generateAssetTag')) {

    function generateAssetTag($locationId, $stationId, $assetTypeId, $running = null)
    {
        $location = Location::findOrFail($locationId);
        $station = AirportStation::findOrFail($stationId);
        $assetType = AssetType::findOrFail($assetTypeId);

        $prefix = strtoupper($station->short_name)
            . '/IT/'
            . now()->format('my')
            . '/'
            . strtoupper($assetType->short_name);

        if ($running === null) {

            $last = AssetInventory::where(
                'tag_no',
                'like',
                $prefix . '/%'
            )->latest('id')->first();

            if ($last) {
                $running = (int) substr($last->tag_no, -4) + 1;
            } else {
                $running = 1;
            }
        }

        return $prefix . '/' . str_pad($running, 4, '0', STR_PAD_LEFT);
    }

    // user permission location

}

/*
|--------------------------------------------------------------------------
| Get Permitted Station IDs
|--------------------------------------------------------------------------
|
| Management = all stations
| Call Coordinator = only assigned stations
|
*/

if (!function_exists('permittedStationIds')) {

    function permittedStationIds(?int $userId = null)
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        */

        if ($user->role == 0) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Call Coordinator / Uploader
        |--------------------------------------------------------------------------
        */

        $userId = $userId ?? $user->id;

        return UserLocationPermission::where('user_id', $userId)->pluck('station_id');                            
        
    }
}


/*
|--------------------------------------------------------------------------
| Check Station Permission
|--------------------------------------------------------------------------
*/

if (!function_exists('hasStationPermission')) {

    function hasStationPermission(
        int $stationId,
        ?int $userId = null
    ): bool {

        $user = auth()->user();

        if (!$user) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Management has unrestricted access
        |--------------------------------------------------------------------------
        */

        if ($user->role == 0) {
            return true;
        }

        $userId = $userId ?? $user->id;

        return UserLocationPermission::where('user_id', $userId)->where('station_id', $stationId)->exists();     
        
    }
}


/*
|--------------------------------------------------------------------------
| Check Location Permission
|--------------------------------------------------------------------------
|
| A location is accessible when the user has at least
| one permitted station under that location.
|
*/

if (!function_exists('hasLocationPermission')) {

    function hasLocationPermission(
        int $locationId,
        ?int $userId = null
    ): bool {

        $user = auth()->user();

        if (!$user) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        */

        if ($user->role == 0) {
            return true;
        }

        $userId = $userId ?? $user->id;

        return UserLocationPermission::where('user_id', $userId)->where('location_id', $locationId)->exists();             
        
    }
}


/*
|--------------------------------------------------------------------------
| Get Permitted Location IDs
|--------------------------------------------------------------------------
*/

if (!function_exists('permittedLocationIds')) {

    function permittedLocationIds(?int $userId = null)
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        */

        if ($user->role == 0) {
            return null;
        }

        $userId = $userId ?? $user->id;

        return UserLocationPermission::where('user_id', $userId)->distinct()->pluck('location_id');                  
        
    }
}
