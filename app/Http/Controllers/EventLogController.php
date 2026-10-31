<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{EventLog, User};

class EventLogController extends Controller
{
    //

    public function index(Request $request){
        $query = EventLog::with('user');

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->latest()->get();
        $users = User::orderBy('name')->get();

        $modules = EventLog::select('module')->distinct()->orderBy('module')->pluck('module');

        $actions = EventLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit-trail.index', compact('logs', 'users', 'modules', 'actions'));

    }
}
