<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{AssetInventory, AssetType, AssetModel, Location};

class AssetInventoryController extends Controller
{
    //

    public function index(){
        $inventories = AssetInventory::with(['assetType', 'assetModel', 'location'])->latest()->get();
        return view('asset-inventory.index', compact('inventories'));
    }

    public function create() {
        $assetTypes = AssetType::where('status',1)->orderBy('name')->get();
                    
        $locations = Location::where('status',1)->orderBy('name')->get();
        // dd($locations);
        return view('asset-inventory.create',compact('assetTypes', 'locations'));
    }

    public function getModels($type){

        $models = AssetModel::where('asset_type_id', $type)->where('status',1)->orderBy('model_name')->get();
        return response()->json($models);
    }
}
