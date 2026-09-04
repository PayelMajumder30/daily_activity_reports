<?php

namespace App\Http\Controllers;

use App\Models\{Activity, Status};
use Illuminate\Http\Request;

class ConfigureController extends Controller
{
    //Activity Configuration
    public function index(){

        // dd('Controller Hit');
        $activitylist = Activity::latest()->get();
        return view('configuration.activity.index', compact('activitylist'));
    }

    public function store(Request $request){

        $request->validate([
            'title' => 'required|unique:activities,title'
        ],[
            'title.unique' => 'This title already exist',
        ]);

        Activity::create([
            'title' => $request->title
        ]);

        eventLog('Create', 'Activity', 'Create activity: '.$request->title);

        return redirect()->back()->with('success','Activity added successfully.');
    }

    public function edit($id) {
        $activity = Activity::findOrFail(decryptId($id));

        return response()->json($activity);
    }

    public function update(Request $request, $id){

        $activity = Activity::findOrFail(decryptId($id));

        $request->validate([
            'title' => 'required|unique:activities,title,'.$activity->id
        ]);

        $activity->update([
            'title'=>$request->title
        ]);

        eventLog('Update', 'Activity', 'Updated activity: '.$activity->title);

        return redirect()->back()->with('success','Activity updated successfully.');
    }

    public function destroy($id) {
        Activity::findOrFail(decryptId($id))->delete();

        eventLog('Delete','Activity','Deleted activity: '.$title);

        return redirect()->back()->with('success','Activity deleted successfully.');
    }

    public function changeStatus($id){
        
        $activity = Activity::findOrFail(decryptId($id));
        $activity->status = !$activity->status;
        $activity->save();

        eventLog('Status Change', 'Activity', $activity->status ? 'Activated activity: '.$activity->title : 'Deactivated activity: '.$activity->title);
        return response()->json([
            'success' => true,
            'status'  => $activity->status
        ]);
    }  

    // Status Configuration

    public function statusIndex(){

        // dd('Controller Hit');
        $statuslist = Status::latest()->get();
        return view('configuration.status.index', compact('statuslist'));
    }

    public function statusStore(Request $request){

        $request->validate([
            'title' => 'required|unique:statuses,title'
        ],[
            'title.unique' => 'This title already exist',
        ]);

        Status::create([
            'title' => $request->title
        ]);

        eventLog('Create', 'Status', 'Create status: '.$request->title);

        return redirect()->back()->with('success','Status added successfully.');
    }

    public function statusEdit($id) {
        $status = Status::findOrFail(decryptId($id));

        return response()->json($status);
    }

    public function statusUpdate(Request $request, $id){

        // dd($request->all());
        $status = Status::findOrFail(decryptId($id));

        $request->validate([
            'title' => 'required|unique:statuses,title,'.$status->id
        ]);

        $status->update([
            'title'=>$request->title
        ]);

        eventLog('Update', 'Status', 'Create status: '.$request->title);

        return redirect()->back()->with('success','Status updated successfully.');
    }

    public function statusDestroy($id) {
        Status::findOrFail(decryptId($id))->delete();

        eventLog('Delete','Status','Deleted status: '.$title);

        return redirect()->back()->with('success','Status deleted successfully.');
    }

    public function statusChange($id){

        $statususe = Status::findOrFail(decryptId($id));
        $statususe->status = !$statususe->status;
        $statususe->save();

         eventLog(
        'Status Change',
        'Status',
        $statususe->status
                ? 'Activated status: '.$statususe->title
                : 'Deactivated status: '.$statususe->title
        );
        return response()->json([
            'success'   => true,
            'status'    => $statususe->status
        ]);

    }

   
}
