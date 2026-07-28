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

        return redirect()->back()->with('success','Activity updated successfully.');
    }

    public function destroy($id) {
        Activity::findOrFail(decryptId($id))->delete();

        return redirect()->back()->with('success','Activity deleted successfully.');
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

        return redirect()->back()->with('success','Status updated successfully.');
    }

    public function statusDestroy($id) {
        Status::findOrFail(decryptId($id))->delete();

        return redirect()->back()->with('success','Status deleted successfully.');
    }
}
