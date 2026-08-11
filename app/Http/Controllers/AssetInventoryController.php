<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{AssetInventory, AssetType, AssetModel, Location};

class AssetInventoryController extends Controller
{
    //

    // public function index(){
    //     $inventories = AssetInventory::with(['assetModel.assetType','location'])->latest()->get();
    //     return view('asset-inventory.index', compact('inventories'));
    // }

    public function index(Request $request)
    {
        $query = AssetInventory::with([
            'assetModel.assetType',
            'location'
        ]);

        // Tag No.
        if ($request->filled('tag_no')) {
            $query->where('tag_no', 'LIKE', '%' . $request->tag_no . '%' );     
        }

        // PO Number
        if ($request->filled('po_number')) {
            $query->where('po_number', 'LIKE','%' . $request->po_number . '%');
        }

        // Serial Number
        if ($request->filled('serial_no')) {
            $query->where('serial_no', 'LIKE', '%' . $request->serial_no . '%');
        }

        // Asset Type
        if ($request->filled('asset_type')) {
            $query->whereHas('assetModel', function ($q) use ($request) {
                $q->where('asset_type_id', $request->asset_type);
            });
        }

        // Location
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }

        // Installation Date
        if ($request->filled('installation_date')) {
            $query->whereDate('installation_date', $request->installation_date);
        }

        $inventories = $query->latest()->get();
            
        // Asset Type dropdown
        $assetTypes = AssetType::where('status', 1)->orderBy('name')->get();
            
        // Location dropdown
        $locations = Location::where('status', 1)->orderBy('name')->get();
            
        return view('asset-inventory.index', compact('inventories', 'assetTypes', 'locations'));
    }

    public function create() {
        $assetTypes = AssetType::where('status',1)->orderBy('name')->get();
                    
        $locations = Location::where('status',1)->orderBy('name')->get();
        // dd($locations);
        return view('asset-inventory.create',compact('assetTypes', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_type_id'     => 'required|exists:asset_types,id',
            'asset_model_id'    => 'required|exists:asset_models,id',
            'location_id'       => 'required|exists:locations,id',
            'po_number'         => 'required|string|max:255',
            'installation_date' => 'required|date',
            'warranty_year'     => 'required|integer|min:0',
            'warranty_end'      => 'required|date',
            'tag_no'            => 'required|array|min:1',
            'tag_no.*'          => 'required|string',
            'serial_no'         => 'required|array|min:1',
            'serial_no.*'       => 'required|string|max:255',
        ]);

        try {

            DB::beginTransaction();

            $tags = $request->tag_no;
            $serialNumbers = $request->serial_no;

            /*
            |--------------------------------------------------------------------------
            | Check quantity
            |--------------------------------------------------------------------------
            */

            if (count($tags) !== count($serialNumbers)) {

                return response()->json([
                    'success' => false,
                    'message' => 'Tag and Serial Number quantity mismatch.'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Save each inventory
            |--------------------------------------------------------------------------
            */

            foreach ($tags as $index => $tagNo) {

                /*
                |--------------------------------------------------------------------------
                | Prevent duplicate tag
                |--------------------------------------------------------------------------
                */

                if (AssetInventory::where('tag_no', $tagNo)->exists()) {

                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => "Asset tag {$tagNo} already exists."
                    ], 422);
                }

                AssetInventory::create([

                    'tag_no'            => $tagNo,
                    'asset_model_id'    => $request->asset_model_id,
                    'location_id'       => $request->location_id,
                    'po_number'         => $request->po_number,
                    'serial_no'         => $serialNumbers[$index],
                    'installation_date' => $request->installation_date,
                    'warranty_year'     => $request->warranty_year,
                    'warranty_end'      => $request->warranty_end,
                    'asset_status'      => 'Available',
                    'created_by'        => auth()->id(),
                    'status'            => 1,
                ]);
            }

            eventLog(
                'Create',
                'Asset Inventory',
                'Created ' . count($tags) . ' asset inventory record(s). Tags: ' . implode(', ', $tags)
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($tags) . ' asset inventory record(s) created successfully.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to save asset inventory.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function generateTags($locationId, $assetTypeId, $quantity)
    {
        $location = Location::findOrFail($locationId);
        $assetType = AssetType::findOrFail($assetTypeId);

        $prefix = strtoupper($location->short_name)
            . '/IT/'
            . now()->format('my')
            . '/'
            . strtoupper($assetType->short_name);

        $last = AssetInventory::where('tag_no', 'like', $prefix . '/%')->latest('id')->first();

        $startNumber = 1;

        if ($last) {
            $startNumber = (int) substr($last->tag_no, -4) + 1;
        }

        $tags = [];

        for ($i = 0; $i < $quantity; $i++) {

            $running = $startNumber + $i;

            $tags[] = generateAssetTag(
                $locationId,
                $assetTypeId,
                $running
            );
        }

        return response()->json([
            'success' => true,
            'tags' => $tags
        ]);
    }

    

    public function getModels($type){

        $models = AssetModel::where('asset_type_id', $type)->where('status',1)->orderBy('model_name')->get();
        return response()->json($models);
    }
}
