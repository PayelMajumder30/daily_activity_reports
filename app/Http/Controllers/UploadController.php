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

        $exists = Upload::where(function ($query) use ($request, $file) {
            $query->whereDate('report_date', $request->report_date)
                ->orWhere('file_name', $file->getClientOriginalName());
        })->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'report_date' => 'This report date or file already exists.'
                ]);
        }

        // $fileName = time().'_'.$file->getClientOriginalName();

        // $file->move(public_path('uploads'),$fileName);

        $upload = Upload::create([
            'user_id'       => auth()->id(),
            'report_date'   =>$request->report_date,
            // 'file_name'=>$fileName
            'file_name'     =>$file->getClientOriginalName(),
        ]);

        Excel::import(
            new ComplaintImport($upload->id),
            // public_path('uploads/'.$fileName)
            $file
        );

        eventLog('Upload', 'Complaint', 'Uploaded complaint report for '.$upload->report_date);

        return redirect()
                ->route('complaints.index')
                ->with('success','Excel uploaded successfully.');
    }
}
