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