<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint};

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

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

    public function pieChartData(Request $request)
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

        if($request->filled('asset_tag_no')){
            $query->where('asset_tag_no',$request->asset_tag_no);
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

    public function barpieChartData(Request $request){
        $query = Complaint::query();

        if($request->filled('from_date')){
            $query->whereHas('upload', function ($q) use ($request){
                $q->wheredate('report_date', '>=',$request->from_date);
            });
        }

        
        if($request->filled('to_date')){
            $query->whereHas('upload', function ($q) use ($request){
                $q->wheredate('report_date', '<=',$request->to_date);
            });
        }

        if($request->filled('engineer')){
            $query->where('engineer_name', $request->engineer);
        }

        if($request->filled('asset_tag_no')){
            $query->where('asset_tag_no',$request->asset_tag_no);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->selectRaw('engineer_name, status, COUNT(*) total')
                    ->groupBy('engineer_name','status')
                    ->orderBy('engineer_name')
                    ->get()
        );
    }

    public function statusDetails(Request $request){
        // $query = Complaint::query();
        $query = Complaint::with('upload');

        if($request->filled('from_date')){
            $query->whereHas('upload', function($q) use ($request){
                $q->whereDate('report_date', '>=',$request->from_date);
            });
        }

        if($request->filled('to_date')){
            $query->whereHas('upload', function($q) use ($request){
                $q->whereDate('report_date', '<=',$request->to_date);
            });
        }

        if($request->filled('engineer')){
            $query->where('engineer_name', $request->engineer);
        }

        if($request->filled('resolution_time')){
            $query->where('resolution_time', $request->resolution_time);
        }

        if($request->filled('asset_tag_no')){
            $query->where('asset_tag_no',$request->asset_tag_no);
        }

        // Status selected from Pie Slice
        if($request->filled('status')){
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->select('complaint_title', 'engineer_name', 'status', 'upload_id', 'resolution_time', 'asset_tag_no')->orderBy('complaint_title')->get()
        );
    }
}
