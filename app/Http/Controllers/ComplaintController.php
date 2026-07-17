<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint};

use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    //

    public function index() {
        $complaints = Complaint::with('upload')
                        ->latest()
                        ->get();

        $engineers = Complaint::select('engineer_name')
                        ->distinct()
                        ->orderBy('engineer_name')
                        ->get();

        return view('complaints.index',compact('complaints', 'engineers'));
    }
    
    public function search(Request $request){
        $query = Complaint::with('upload');

        if ($request->filled('engineer')) {
            $query->where('engineer_name', $request->engineer);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('complaint')) {
            $query->where('complaint_title', 'LIKE', '%' . $request->complaint . '%');
        }

        if ($request->filled('date')) {
            $query->whereHas('upload', function ($q) use ($request) {
                $q->whereDate('report_date', $request->date);
            });
        }

        $complaints = $query->get();

        // return response()->json($complaints);
        return response()->json(

            $complaints->map(function($row){
                return [
                    'complaint_title'   => $row->complaint_title,
                    'engineer_name'     => $row->engineer_name,
                    'status'            => $row->status,
                    'resolution_time'   => $row->resolution_time,
                    'report_date'       => optional($row->upload?->report_date)
                                            ->format('d-m-Y')

                ];

            })

        );
    }
}
