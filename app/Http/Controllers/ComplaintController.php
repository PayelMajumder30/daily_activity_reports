<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint};
use App\Exports\ComplaintExport;
use Maatwebsite\Excel\Facades\Excel;

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

        $statuses = Complaint::distinct()->pluck('status');

        return view('complaints.index',compact('complaints', 'engineers', 'statuses'));
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

        if($request->filled('from_date')){
            $query->whereHas('upload', function($q) use($request){

                $q->whereDate('report_date', '>=', $request->from_date);

            });
        }

        if($request->filled('to_date')){
            $query->whereHas('upload', function($q) use($request){
                $q->whereDate('report_date', '<=', $request->to_date);

            });
        }

        $complaints = $query->get();

        // return response()->json($complaints);
        return response()->json(

            $complaints->map(function($row){
                return [
                    'complaint_title'   => $row->complaint_title,
                    'type_of_activity'  => $row->type_of_activity ?: 'NA',
                    'asset_tag_no'      => $row->asset_tag_no ?: 'NA',
                    'engineer_name'     => $row->engineer_name,
                    'status'            => $row->status,
                    'resolution_time'   => $row->resolution_time ?: 'NA',
                    'report_date'       => optional($row->upload?->report_date)->format('d-m-Y')                                         

                ];

            })

        );
    }

    
    public function export(Request $request)
    {
        $filters = $request->validate([
            'engineer'  => ['nullable', 'string'],
            'status'    => ['nullable', 'string'],
            'complaint' => ['nullable', 'string', 'max:255'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date'   => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:from_date',
            ],
        ]);

        return Excel::download(
            new ComplaintExport($filters),
            'Complaint_Report_' . now()->format('d-m-Y_H-i-s') . '.xlsx'
        );
    }

}
