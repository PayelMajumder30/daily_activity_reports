<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint, ComplaintTemp};
use Illuminate\Http\Request;
use App\Imports\ComplaintTempImport;
use Maatwebsite\Excel\Facades\Excel;

class UploaderController extends Controller
{
    //

    public function index(){
        return view('uploader.index', [
            'preview'=>collect(),
            'uploadId'=>null
        ]);
    }

    public function uploadPreview(Request $request){

        $request->validate([
            'report_date'=>'required|date',
            'excel_file'=>'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('excel_file');

        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);

        $upload=Upload::create([
            'report_date'=>$request->report_date,
            'file_name'=>$filename
        ]);

        Excel::import(
            new ComplaintTempImport($upload->id),
            public_path('uploads/'.$filename)
        );

        $rows=ComplaintTemp::where('upload_id',$upload->id)->get();
        // dd($rows);

        return response()->json([
            'upload_id'=>$upload->id,
            'rows'=>$rows
        ]);
    }


    public function updateTemp(Request $request,$id)
    {
        $row=ComplaintTemp::findOrFail($id);

        $row->update([
            'complaint_title'   =>$request->complaint_title,
            'engineer_name'     =>$request->engineer_name,
            'status'            =>$request->status,
            'resolution_time'   =>$request->resolution_time
        ]);

        return response()->json([
            'success'=>true
        ]);
    }

    public function savePermanent(Request $request)
    {
        $temps=ComplaintTemp::where(
            'upload_id',
            $request->upload_id
        )->get();

        foreach($temps as $temp){

            Complaint::create([

                'upload_id'         =>$temp->upload_id,
                'complaint_title'   =>$temp->complaint_title,
                'engineer_name'     =>$temp->engineer_name,
                'status'            =>$temp->status,
                'resolution_time'   =>$temp->resolution_time

            ]);

        }

        ComplaintTemp::where(
            'upload_id',
            $request->upload_id
        )->delete();

        return response()->json([
            'success'=>true
        ]);
    }
}
