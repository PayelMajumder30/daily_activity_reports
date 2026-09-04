<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{AssetInventory, AssetType, AssetModel, Location, AirportStation, AssetRetainedAsset, Custodian, AssetIssueRegister};

class AssetRetainedController extends Controller
{
    //

    public function create()
    {
        $custodians = Custodian::with(['designation', 'discipline', 'section', 'location',])
                                ->where('status', 1)
                                ->orderBy('custodian_name')
                                ->get();

        $stations = AirportStation::with('location')
            ->where('status', 1)
            ->orderBy('station_name')
            ->get();

        return view('asset-retained.create', compact('custodians', 'stations'));                       
    }

    // public function getCustodianAssets($custodianId)
    // {
    //     // Option A: Fetch assets currently issued to this custodian OR assets with 'Available' status
    //     $assets = AssetInventory::with(['assetType', 'assetModel', 'station', 'location'])
    //         ->where(function ($query) use ($custodianId) {
    //             $query->whereHas('issueHistory', function ($q) use ($custodianId) {
    //                 $q->where('custodian_id', $custodianId)
    //                 ->whereIn('issue_status', ['Issued', 'Assigned', 'Retained']);
    //             })
    //             ->orWhere('asset_status', 'Available'); // Fallback to show available assets
    //         })
    //         ->get()
    //         ->map(function ($asset) {
    //             return [
    //                 'id'              => $asset->id,
    //                 'tag_no'          => $asset->tag_no,
    //                 'asset_type'      => $asset->assetType?->name ?? 'N/A',
    //                 'asset_model'     => $asset->assetModel?->model_name ?? 'N/A',
    //                 'from_station_id' => $asset->station_id,
    //                 'from_station'    => $asset->station?->station_name ?? 'N/A',
    //                 'from_location'   => $asset->location?->name ?? 'N/A',
    //             ];
    //         });

    //     return response()->json($assets);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'custodian_id'                => ['required', 'exists:custodians,id'],
    //         'retained_date'               => ['required', 'date'],
    //         'assets'                      => ['required', 'array', 'min:1'],
    //         'assets.*.asset_inventory_id' => ['required', 'exists:asset_inventories,id'],
    //         'assets.*.to_station_id'      => ['required', 'exists:airport_stations,id'],
    //         'remarks'                     => ['nullable', 'string'],
    //     ]);

    //     DB::transaction(function () use ($request) {
    //         foreach ($request->assets as $assetData) {
    //             $asset = AssetInventory::findOrFail($assetData['asset_inventory_id']);

    //             if ($asset->asset_status === 'Retained') {
    //                 throw new \Exception("Asset {$asset->tag_no} is already marked as retained.");
    //             }

    //             if ($asset->station_id == $assetData['to_station_id']) {
    //                 throw new \Exception("Destination station cannot be the same as source station.");
    //             }

    //             // Fetch the target station to auto-resolve its parent location_id
    //             $destinationStation = AirportStation::findOrFail($assetData['to_station_id']);

    //             // 1. Save record into asset_retained_assets
    //             AssetRetainedAsset::create([
    //                 'asset_inventory_id' => $asset->id,
    //                 'custodian_id'       => $request->custodian_id,
    //                 'from_location_id'   => $asset->location_id,
    //                 'from_station_id'    => $asset->station_id,
    //                 'to_location_id'     => $destinationStation->location_id,
    //                 'to_station_id'      => $destinationStation->id,
    //                 'retained_date'      => $request->retained_date,
    //                 'retention_status'   => 'Retained',
    //                 'remarks'            => $request->remarks,
    //                 'created_by'         => auth()->id(),
    //             ]);

    //             // 2. Log entry in asset_issue_registers
    //             AssetIssueRegister::create([
    //                 'asset_inventory_id' => $asset->id,
    //                 'custodian_id'       => $request->custodian_id,
    //                 'user_type'          => 'self',
    //                 'operator_name'      => null,
    //                 'issued_date'        => $request->retained_date,
    //                 'returned_date'      => null,
    //                 'issue_status'       => 'Retained',
    //                 'remarks'            => $request->remarks,
    //             ]);

    //             // 3. Update active location & status in asset_inventories
    //             $asset->update([
    //                 'location_id'  => $destinationStation->location_id,
    //                 'station_id'   => $destinationStation->id,
    //                 'asset_status' => 'Retained',
    //             ]);
    //         }
    //     });

    //     return redirect()
    //         ->route('asset-issue-register.index')
    //         ->with('success', 'Assets successfully retained and registered.');
    // }

    public function getCustodianAssets($custodianId)
{
    // Simplified logic: Directly fetch available assets from asset_inventories table
    $assets = AssetInventory::with(['assetType', 'assetModel', 'station', 'location'])
        ->where('asset_status', 'Available')
        ->where('status', 1)
        ->orderBy('tag_no')
        ->get()
        ->map(function ($asset) {
            return [
                'id'              => $asset->id,
                'tag_no'          => $asset->tag_no,
                'asset_type'      => $asset->assetType?->name ?? 'N/A',
                'asset_model'     => $asset->assetModel?->model_name ?? 'N/A',
                'from_station_id' => $asset->station_id,
                'from_station'    => $asset->station?->station_name ?? 'N/A',
                'from_location'   => $asset->location?->name ?? 'N/A',
            ];
        });

    return response()->json($assets);
}

public function store(Request $request)
{
    $request->validate([
        'custodian_id'                => ['required', 'exists:custodians,id'],
        'retained_date'               => ['required', 'date'],
        'assets'                      => ['required', 'array', 'min:1'],
        'assets.*.asset_inventory_id' => ['required', 'exists:asset_inventories,id'],
        'assets.*.to_station_id'      => ['required', 'exists:airport_stations,id'],
        'remarks'                     => ['nullable', 'string'],
    ]);

    DB::transaction(function () use ($request) {
        foreach ($request->assets as $assetData) {
            $asset = AssetInventory::findOrFail($assetData['asset_inventory_id']);

            if ($asset->asset_status === 'Retained') {
                throw new \Exception("Asset {$asset->tag_no} is already marked as retained.");
            }

            if ($asset->station_id == $assetData['to_station_id']) {
                throw new \Exception("Destination station cannot be the same as source station.");
            }

            // Resolve target station's location_id dynamically
            $destinationStation = AirportStation::findOrFail($assetData['to_station_id']);

            // 1. Create record in asset_retained_assets (Column fixed to 'retained_status')
            AssetRetainedAsset::create([
                'asset_inventory_id' => $asset->id,
                'custodian_id'       => $request->custodian_id,
                'from_location_id'   => $asset->location_id,
                'from_station_id'    => $asset->station_id,
                'to_location_id'     => $destinationStation->location_id,
                'to_station_id'      => $destinationStation->id,
                'retained_date'      => $request->retained_date,
                'retained_status'    => 'Retained', // Updated from retention_status
                'remarks'            => $request->remarks,
                'created_by'         => auth()->id(),
            ]);

            // 2. Log entry in asset_issue_registers
            AssetIssueRegister::create([
                'asset_inventory_id' => $asset->id,
                'custodian_id'       => $request->custodian_id,
                'user_type'          => 'self',
                'operator_name'      => null,
                'issued_date'        => $request->retained_date,
                'returned_date'      => null,
                'issue_status'       => 'Retained',
                'remarks'            => $request->remarks,
            ]);

            // 3. Update inventory location & status
            $asset->update([
                'location_id'  => $destinationStation->location_id,
                'station_id'   => $destinationStation->id,
                'asset_status' => 'Retained',
            ]);
        }
    });

    return redirect()
        ->route('asset-issue-register.index')
        ->with('success', 'Assets successfully retained and registered.');
}

    public function getAssetDetailsByTag($assetId)
    {
        $asset = AssetInventory::with([
            'assetType', 
            'assetModel', 
            'station', 
            'location'
        ])->find($assetId);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Asset record not found.'
            ], 404);
        }

        return response()->json([
            'success'         => true,
            'id'              => $asset->id,
            'tag_no'          => $asset->tag_no,
            'asset_type'      => $asset->assetType?->name ?? 'N/A',
            'asset_model'     => $asset->assetModel?->model_name ?? 'N/A',
            'from_station_id' => $asset->station_id,
            'from_station'    => $asset->station?->station_name ?? 'N/A',
            'from_location'   => $asset->location?->name ?? 'N/A',
        ]);
    }
}
