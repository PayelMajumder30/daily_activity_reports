<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint};

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    // public function dashboard(){
    //     $totalComplaints = Complaint::count();

    //     $totalEngineers = Complaint::distinct('engineer_name')->count();

    //     $openComplaints = Complaint::where('status', 'Open')->count();

    //     $closedComplaints = Complaint::where('status', 'Closed')->count();

    //     $resolved = Complaint::where('status','Resolved')->count();

    //     $pending = Complaint::where('status','!=','Resolved')->count();

    //     $latestUpload = Upload::latest()->first();

    //     return view('dashboard.dashboard',compact(
    //         'totalComplaints',
    //         'totalEngineers',
    //         'resolved',
    //         'pending',
    //         'latestUpload', 'openComplaints', 'closedComplaints'
    //     ));
    // }

    public function dashboard()
    {
        $engineers = Complaint::select('engineer_name')
                        ->distinct()
                        ->orderBy('engineer_name')
                        ->get();

        $statuses = Complaint::select('status')
                        ->distinct()
                        ->orderBy('status')
                        ->get();

        return view('dashboard.dashboard', compact(
            'engineers',
            'statuses'
        ));
    }

    public function chartData(Request $request)
    {
        $query = Complaint::query();

        if($request->filled('from_date')){
            $query->whereHas('upload', function($q) use($request){

                $q->whereDate('report_date','>=',$request->from_date);

            });
        }

        if($request->filled('to_date')){
            $query->whereHas('upload', function($q) use($request){

                $q->whereDate('report_date','<=',$request->to_date);

            });
        }

        if($request->filled('engineer')){
            $query->where('engineer_name',$request->engineer);
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        $result = $query
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get();

        return response()->json($result);
    }
}
