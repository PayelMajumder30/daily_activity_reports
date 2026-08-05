<?php

namespace App\Http\Controllers;

use App\Models\{Designation, Discipline, AssetType};
use Illuminate\Http\Request;

class SettingController extends Controller
{
    //Designation
    public function desIndex(){

        // dd('Controller Hit');
        $designations = Designation::latest()->get();
        return view('settings.designation.index', compact('designations'));
    }

    public function desStore(Request $request){
        // dd($request->all());
        $request->validate([
            'name' => 'required|unique:designations,name'
        ],[
            'name.unique' => 'This name already exist',
        ]);

        Designation::create([
            'name' => $request->name
        ]);

        eventLog('Create', 'Designation', 'Create designation: '.$request->name);

        return redirect()->back()->with('success','Designation added successfully.');
    }

    public function desEdit($id) {
        $designation = Designation::findOrFail(decryptId($id));

        return response()->json($designation);
    }

    public function desUpdate(Request $request, $id){

        $designation = Designation::findOrFail(decryptId($id));

        $request->validate([
            'name' => 'required|unique:designations,name,'.$designation->id
        ]);

        $designation->update([
            'name'=>$request->name
        ]);

        eventLog('Update', 'Designation', 'Updated designation: '.$designation->name);

        return redirect()->back()->with('success','Designation updated successfully.');
    }

    public function desChangeStatus($id){
        
        $designation = Designation::findOrFail(decryptId($id));
        $designation->status = !$designation->status;
        $designation->save();

        eventLog('Status Change', 'Designation', $designation->status ? 'Activated designation: '.$designation->name : 'Deactivated designation: '.$designation->name);
        return response()->json([
            'success' => true,
            'status'  => $designation->status
        ]);
    }  

    //Discipline
    public function discIndex(){

        // dd('Controller Hit');
        $disciplines = Discipline::latest()->get();
        return view('settings.discipline.index', compact('disciplines'));
    }

    public function discStore(Request $request){
        // dd($request->all());
        $request->validate([
            'name' => 'required|unique:disciplines,name'
        ],[
            'name.unique' => 'This name already exist',
        ]);

        Discipline::create([
            'name' => $request->name
        ]);

        eventLog('Create', 'Discipline', 'Create discipline: '.$request->name);

        return redirect()->back()->with('success','Discipline added successfully.');
    }

    public function discEdit($id) {
        $discipline = Discipline::findOrFail(decryptId($id));

        return response()->json($discipline);
    }

    public function discUpdate(Request $request, $id){

        $discipline = Discipline::findOrFail(decryptId($id));

        $request->validate([
            'name' => 'required|unique:disciplines,name,'.$discipline->id
        ]);

        $discipline->update([
            'name'=>$request->name
        ]);

        eventLog('Update', 'Discipline', 'Updated discipline: '.$discipline->name);

        return redirect()->back()->with('success','Discipline updated successfully.');
    }

    public function discChangeStatus($id){
        
        $discipline = Discipline::findOrFail(decryptId($id));
        $discipline->status = !$discipline->status;
        $discipline->save();

        eventLog('Status Change', 'Discipline', $discipline->status ? 'Activated discipline: '.$discipline->name : 'Deactivated discipline: '.$discipline->name);
        return response()->json([
            'success' => true,
            'status'  => $discipline->status
        ]);
    }  

    // asset type
    public function assetIndex(){

        // dd('Controller Hit');
        $assetTypes = AssetType::latest()->get();
        return view('settings.assetType.index', compact('assetTypes'));
    }

    public function assetStore(Request $request){
        // dd($request->all());
        $request->validate([
            'name' => 'required|unique:asset_types,name'
        ],[
            'name.unique' => 'This name already exist',
        ]);

        AssetType::create([
            'name' => $request->name
        ]);

        eventLog('Create', 'Asset Type', 'Create asset type: '.$request->name);

        return redirect()->back()->with('success','Asset type added successfully.');
    }

    public function assetEdit($id) {
        $assetType = AssetType::findOrFail(decryptId($id));

        return response()->json($assetType);
    }

    public function assetUpdate(Request $request, $id){

        $assetType = AssetType::findOrFail(decryptId($id));

        $request->validate([
            'name' => 'required|unique:asset_types,name,'.$assetType->id
        ]);

        $assetType->update([
            'name'=>$request->name
        ]);

        eventLog('Update', 'Asset type', 'Updated asset type: '.$assetType->name);

        return redirect()->back()->with('success','Asset type updated successfully.');
    }

    public function assetChangeStatus($id){
        
        $assetType = AssetType::findOrFail(decryptId($id));
        $assetType->status = !$assetType->status;
        $assetType->save();

        eventLog('Status Change', 'Asset type', $assetType->status ? 'Activated discipline: '.$assetType->name : 'Deactivated assetType: '.$assetType->name);
        return response()->json([
            'success' => true,
            'status'  => $assetType->status
        ]);
    }  
}
