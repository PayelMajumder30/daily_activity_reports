<?php

namespace App\Http\Controllers;

use App\Models\{Upload, Complaint, ComplaintTemp};
use Illuminate\Http\Request;
use App\Imports\ComplaintTempImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UploaderController extends Controller
{
    //

    public function index(){
        // return view('uploader.index', [
        //     'preview'=>collect(),
        //     'uploadId'=>null
        // ]);

        $upload = Upload::where('user_id', auth()->id())
            ->whereDoesntHave('complaints') // not permanently saved
            ->latest()
            ->first();

        $preview = collect();

        if ($upload) {
            $preview = ComplaintTemp::where('upload_id', $upload->id)->get();
        }

        return view('uploader.index', [
            'preview'  => $preview,
            'uploadId' => $upload?->id,
            'upload'   => $upload,
        ]);
    }

    // public function uploadPreview(Request $request){

    //     $request->validate([
    //         'report_date'=>'required|date',
    //         'excel_file'=>'required|mimes:xlsx,xls'
    //     ]);

    //     if(Upload::whereDate('report_date',$request->report_date)->exists()){
    //         return back()->withErrors([
    //             'report_date'=>'Report already uploaded for this date.'
    //         ]);
    //     }

    //     $file = $request->file('excel_file');

    //     $exists = Upload::where(function ($query) use ($request, $file) {
    //         $query->whereDate('report_date', $request->report_date)
    //             ->orWhere('file_name', $file->getClientOriginalName());
    //     })->exists();

    //     if ($exists) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'report_date' => 'This report date or file already exists.'
    //             ]);
    //     }

    //     // $filename = time().'_'.$file->getClientOriginalName();
    //     // $file->move(public_path('uploads'), $filename);

    //     $upload=Upload::create([
    //         'user_id'       => auth()->id(),
    //         'report_date'   =>$request->report_date,
    //         // 'file_name'=>$filename
    //         'file_name'     =>$file->getClientOriginalName()
    //     ]);

    //     Excel::import(
    //         new ComplaintTempImport($upload->id),
    //         // public_path('uploads/'.$filename)
    //         $file
    //     );

    //     $rows=ComplaintTemp::where('upload_id',$upload->id)->get();
    //     // dd($rows);

    //     return response()->json([
    //         'upload_id'=>$upload->id,
    //         'rows'=>$rows
    //     ]);
    // }

    public function uploadPreview(Request $request) {
        $request->validate([
            'report_date' => 'required|date',
            'excel_file'  => 'required|mimes:xlsx,xls'
        ]);

        if (Upload::whereDate('report_date', $request->report_date)->exists()) {
            return response()->json([
                'message' => 'Report already uploaded for this date.'
            ], 422);
        }

        $file = $request->file('excel_file');

        $exists = Upload::where(function ($query) use ($request, $file) {
            $query->whereDate('report_date', $request->report_date)
                ->orWhere('file_name', $file->getClientOriginalName());
        })->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This report date or file already exists.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $upload = Upload::create([
                'user_id'     => auth()->id(),
                'report_date' => $request->report_date,
                'file_name'   => $file->getClientOriginalName()
            ]);

            Excel::import(
                new ComplaintTempImport($upload->id),
                $file
            );

            $rows = ComplaintTemp::where('upload_id', $upload->id)->get();

            // No valid rows imported
            if ($rows->count() == 0) {

                throw new \Exception(
                    'Invalid Excel format or the Excel contains no valid data. Please use the sample template.'
                );
            }

            DB::commit();

            return response()->json([
                'upload_id' => $upload->id,
                'rows'      => $rows
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            // Extra safety (normally rollback removes it)
            if (isset($upload)) {
                ComplaintTemp::where('upload_id', $upload->id)->delete();
                $upload->delete();
            }

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function updateTemp(Request $request,$id)
    {
        $row=ComplaintTemp::findOrFail($id);

        $row->update([
            'complaint_title'   =>$request->complaint_title,
            'type_of_activity'  =>$request->type_of_activity,
            'asset_tag_no'      =>$request->asset_tag_no,
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
        $upload = Upload::findOrFail($request->upload_id);

        if (Complaint::where('upload_id', $upload->id)->exists()) {

            return response()->json([
                'success' => false,
                'message' => 'This report has already been saved.'
            ], 422);
        }

        $temps=ComplaintTemp::where('upload_id',$upload->id)->get();

        foreach($temps as $temp){

            Complaint::create([

                'upload_id'         =>$temp->upload_id,
                'complaint_title'   =>$temp->complaint_title,
                'type_of_activity'  =>$temp->type_of_activity,
                'asset_tag_no'      =>$temp->asset_tag_no,
                'engineer_name'     =>$temp->engineer_name,
                'status'            =>$temp->status,
                'resolution_time'   =>$temp->resolution_time

            ]);

        }

        ComplaintTemp::where('upload_id', $upload->id)->delete();

        return response()->json([
            'success'=> true,
            'message' => 'Complaint data saved successfully.'
        ]);
    }

    public function deleteUpload($upload_id){

        $upload = Upload::findOrFail($upload_id);

        // Prevent deletion if already permanently saved
        if(Complaint::where('upload_id',$upload->id)->exists()){
            return response()->json([
                'success' => false,
                'message'   => 'This report has already been permanently saved.'
            ],422);
        }

        ComplaintTemp::where('upload_id', $upload->id)->delete();
        $upload->delete();

        return response()->json([
            'success' => true,
            'message' => 'Uploaded data deleted successfully.'
        ]);
    }

    public function downloadTemplate(){
        return response()->download(
            public_path('templates/complaint_template.xlsx')
        );
    }
}
