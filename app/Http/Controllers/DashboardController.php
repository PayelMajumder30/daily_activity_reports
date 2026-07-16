<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint};

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function dashboard(){
        $totalComplaints = Complaint::count();

        $totalEngineers = Complaint::distinct('engineer_name')->count();

        $openComplaints = Complaint::where('status', 'Open')->count();

        $closedComplaints = Complaint::where('status', 'Closed')->count();

        $resolved = Complaint::where('status','Resolved')->count();

        $pending = Complaint::where('status','!=','Resolved')->count();

        $latestUpload = Upload::latest()->first();

        return view('dashboard.dashboard',compact(
            'totalComplaints',
            'totalEngineers',
            'resolved',
            'pending',
            'latestUpload', 'openComplaints', 'closedComplaints'
        ));
    }
}
