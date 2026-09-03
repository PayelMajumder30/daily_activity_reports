<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{AssetInventory, AssetType, AssetModel, Location, AirportStation, AssetRetainedAsset, Custodian, AssetIssueRegister};

class AssetRetainedController extends Controller
{
    public function create()
    {
        $custodians = Custodian::with(['designation', 'discipline', 'section', 'location'])
            ->where('status', 1)
            ->orderBy('custodian_name')
            ->get();

        $stations = AirportStation::with('location')
            ->where('status', 1)
            ->orderBy('station_name')
            ->get();

        return view('asset-retained.create', compact('custodians', 'stations'));
    }

  
    public function getCustodianAssets($custodianId)
    {
        // Simply fetch available assets directly from the asset_inventories table
        $assets = AssetInventory::with(['assetModel.assetType', 'station', 'location'])
            ->where('asset_status', 'Available')
            ->where('status', 1) // Ensures the inventory item is active
            ->orderBy('tag_no')
            ->get()
            ->map(function ($asset) {
                return [
                    'id'              => $asset->id, // This gets stored in asset_inventory_id
                    'tag_no'          => $asset->tag_no,
                    'asset_type'      => $asset->assetModel?->assetType?->name ?? 'N/A',
                    'asset_model'     => $asset->assetModel?->model_name ?? 'N/A',
                    'from_station_id' => $asset->station_id,
                    'from_station'    => $asset->station?->station_name ?? 'N/A',
                    'from_location'   => $asset->location?->name ?? 'N/A',
                ];
            });

        return response()->json($assets);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'custodian_id'                => ['required', 'exists:custodians,id'],
    //         'user_type'                   => ['required', 'in:self,multiuser,operator'],
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

    //             $destinationStation = AirportStation::findOrFail($assetData['to_station_id']);

    //             // 1. Log Retention
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

    //             // 2. Log Issue Register History
    //             AssetIssueRegister::create([
    //                 'asset_inventory_id' => $asset->id,
    //                 'custodian_id'       => $request->custodian_id,
    //                 'user_type'          => $request->user_type,
    //                 'operator_name'      => null,
    //                 'issued_date'        => $request->retained_date,
    //                 'returned_date'      => null,
    //                 'issue_status'       => 'Retained',
    //                 'remarks'            => $request->remarks,
    //             ]);

    //             // 3. Update active station, location & status
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
   public function store(Request $request)
{
    $request->validate([
        'custodian_id'                => ['required', 'exists:custodians,id'],
        'user_type'                   => ['required', 'in:self,multiuser,operator'],
        'retained_date'               => ['required', 'date'],
        'assets'                      => ['required', 'array', 'min:1'],
        'assets.*.asset_inventory_id' => ['required', 'exists:asset_inventories,id'],
        'remarks'                     => ['nullable', 'string'],
    ]);

    DB::transaction(function () use ($request) {
        foreach ($request->assets as $assetData) {
            $asset = AssetInventory::findOrFail($assetData['asset_inventory_id']);

            if ($asset->asset_status === 'Retained') {
                throw new \Exception("Asset {$asset->tag_no} is already marked as retained.");
            }

            // 1. Log History in Asset Issue Register
            AssetIssueRegister::create([
                'asset_inventory_id' => $asset->id,
                'custodian_id'       => $request->custodian_id,
                'user_type'          => $request->user_type,
                'operator_name'      => null,
                'issued_date'        => $request->retained_date,
                'retained_date'      => $request->retained_date,
                'returned_date'      => null,
                'issue_status'       => 'Retained',
                'remarks'            => $request->remarks,
            ]);

            // 2. Update Main Asset Status
            $asset->update([
                'asset_status' => 'Retained',
            ]);
        }
    });

    return redirect()
        ->route('asset-issue-register.index')
        ->with('success', 'Assets successfully retained and registered.');
}
}