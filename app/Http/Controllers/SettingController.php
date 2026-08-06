<?php

namespace App\Http\Controllers;

use App\Models\{Designation, Discipline, AssetType, AssetModel, AssetTag};
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
            'name.required' => 'Designation is required.',
            'name.unique'   => 'This Designation already exists.',
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
        ],[
            'name.required' => 'Designation is required.',
            'name.unique'   => 'This Designation already exists.',
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
            'name.required' => 'Discipline is required.',
            'name.unique'   => 'This Discipline already exists.',
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
        ],[
            'name.required' => 'Discipline is required.',
            'name.unique'   => 'This Discipline already exists.',
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
            'name.required' => 'Asset is required.',
            'name.unique'   => 'This Asset already exists.',
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
        ],[
            'name.required' => 'Asset is required.',
            'name.unique'   => 'This Asset already exists.',
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

        eventLog('Status Change', 'Asset type', $assetType->status ? 'Activated asset type: '.$assetType->name : 'Deactivated asset type: '.$assetType->name);
        return response()->json([
            'success' => true,
            'status'  => $assetType->status
        ]);
    }  


    // asset model
    public function assetModelIndex(){

        // dd('Controller Hit');
        $assetModels = AssetModel::with('assetType')->latest()->get();
        $assetTypes = AssetType::where('status', 1)->orderBy('name')->get();

        return view('settings.assetModel.index', compact('assetModels', 'assetTypes'));
    }

    public function assetModelStore(Request $request){
        // dd($request->all());
        $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'model_name'    => 'required|unique:asset_models,model_name',
            'manufacturer'  => 'nullable|string|max:255',
        ]);

       $model = AssetModel::create([
            'asset_type_id' => $request->asset_type_id,
            'model_name'    => $request->model_name,
            'manufacturer'  => $request->manufacturer,
        ]);

        eventLog('Create', 'Asset Model', 'Created asset model: '.$request->model_name);

        return response()->json([
            'success'=>true,
            'message'=>'Asset Model created successfully.'
        ]);
    }

    public function assetModelEdit($id) {
        return response()->json(
            AssetModel::findOrFail(decryptId($id))
        );
    }

    public function assetModelUpdate(Request $request,$id)
    {
        $model = AssetModel::findOrFail(decryptId($id));

        $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'model_name'    => 'required|unique:asset_models,model_name,'.$model->id,
            'manufacturer'  => 'nullable|max:255',
        ]);

        $model->update([
            'asset_type_id' =>$request->asset_type_id,
            'model_name'    =>$request->model_name,
            'manufacturer'  =>$request->manufacturer,
        ]);

        eventLog(
            'Update',
            'Asset Model',
            'Updated asset model: '.$model->model_name
        );

        return response()->json([
            'success'=>true,
            'message'=>'Asset Model updated successfully.'
        ]);
    }

    public function assetModelChangeStatus($id){
        
        $assetModel = AssetModel::findOrFail(decryptId($id));
        $assetModel->status = !$assetModel->status;
        $assetModel->save();

        eventLog('Status Change', 'Asset model', $assetModel->status ? 'Activated discipline: '.$assetModel->name : 'Deactivated assetModel: '.$assetModel->name);
        return response()->json([
            'success' => true,
            'status'  => $assetModel->status
        ]);
    } 

    // tag no
    public function tagIndex(){

        // dd('Controller Hit');
        $assetTags = AssetTag::latest()->get();
        return view('settings.asset-tag.index', compact('assetTags'));
    }

    public function tagStore(Request $request){
        // dd($request->all());
        $request->validate([
            'tag_no' => 'required|unique:asset_tags,tag_no'
        ],[
            'tag_no.required' => 'Asset Tag Number is required.',
            'tag_no.unique'   => 'This Asset Tag Number already exists.',
        ]);

        AssetTag::create([
            'tag_no' => $request->tag_no
        ]);

        eventLog('Create', 'Asset Tag', 'Create asset tag: '.$request->tag_no);

        return redirect()->back()->with('success','Asset tag added successfully.');
    }

    public function tagEdit($id) {
        $assetTag = AssetTag::findOrFail(decryptId($id));

        return response()->json($assetTag);
    }

    public function tagUpdate(Request $request, $id){

        $assetTag = AssetTag::findOrFail(decryptId($id));

        $request->validate([
            'tag_no' => 'required|unique:asset_tags,tag_no,'.$assetTag->id
        ],[
            'tag_no.required' => 'Asset Tag Number is required.',
            'tag_no.unique'   => 'This Asset Tag Number already exists.',
        ]);

        $assetTag->update([
            'tag_no'=>$request->tag_no
        ]);

        eventLog('Update', 'Asset tag', 'Updated asset tag: '.$assetTag->tag_no);

        return redirect()->back()->with('success','Asset tag updated successfully.');
    }

    public function tagChangeStatus($id){
        
        $assetTag = AssetTag::findOrFail(decryptId($id));
        $assetTag->status = !$assetTag->status;
        $assetTag->save();

        eventLog('Status Change', 'Asset type', $assetTag->status ? 'Activated asset tag: '.$assetTag->name : 'Deactivated asset tag: '.$assetTag->name);
        return response()->json([
            'success' => true,
            'status'  => $assetTag->status
        ]);
    } 
}
