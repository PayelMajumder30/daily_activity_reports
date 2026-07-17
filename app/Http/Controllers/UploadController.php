<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint};
use App\Imports\ComplaintImport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
   
    public function store(Request $request)
    {
        $request->validate([
            'report_date'   => 'required|date',
            'excel_file'    => 'required|file|mimes:xlsx,xls'
        ],[
            'report_date.required'  => 'Please select report date.',
            'excel_file.required'   => 'Please select excel file.'
        ]);

        if(Upload::whereDate('report_date',$request->report_date)->exists()){
            return back()->withErrors([
                'report_date'=>'Report already uploaded for this date.'
            ]);
        }

        $file = $request->file('excel_file');

        // $fileName = time().'_'.$file->getClientOriginalName();

        // $file->move(public_path('uploads'),$fileName);

        $upload = Upload::create([
            'report_date'=>$request->report_date,
            // 'file_name'=>$fileName
            'file_name'=>$file->getClientOriginalName(),
        ]);

        Excel::import(
            new ComplaintImport($upload->id),
            // public_path('uploads/'.$fileName)
            $file
        );

        return redirect()
                ->route('complaints.index')
                ->with('success','Excel uploaded successfully.');
    }
}
