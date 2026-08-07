<?php

use Illuminate\Support\Facades\Crypt;
use App\Models\EventLog;

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

function generateAssetTag($locationId,$assetTypeId)
{
    $location = Location::findOrFail($locationId);
    $assetType = AssetType::findOrFail($assetTypeId);
    $prefix = $location->short_name.'/IT/'.now()->format('my').'/'.strtoupper($assetType->short_name);

    $last = AssetInventory::where('tag_no','like',$prefix.'/%')->latest('id')->first();

    if($last){
        $running = (int)substr($last->tag_no,-4)+1;
            
    }else{
        $running=0;
    }

    return $prefix.'/'.str_pad($running,4,'0',STR_PAD_LEFT);
}