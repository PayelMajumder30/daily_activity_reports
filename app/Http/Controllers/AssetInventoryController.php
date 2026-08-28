<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\{AssetInventoryExport, AssetInventoryTemplateExport};
use App\Imports\AssetInventoryImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\{AssetInventory, AssetType, AssetModel, Location, AirportStation};

class AssetInventoryController extends Controller
{
    //

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

        // Asset Model
        if ($request->filled('asset_model')) {
            $query->where('asset_model_id', $request->asset_model);
        }

        //Asset status
        if($request->filled('asset_status')) {
            $query->where('asset_status', $request->asset_status);
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

        // Asset model dropdown
        $assetModels = AssetModel::where('status', 1)->orderBy('model_name')->get();

        // asset status
        $assetStatuses = AssetInventory::where('asset_status', '!=', '')->distinct()->orderBy('asset_status')->pluck('asset_status');
            
        return view('asset-inventory.index', compact('inventories', 'assetTypes', 'assetModels', 'assetStatuses'));
    }

    public function create() {
        $assetTypes = AssetType::where('status',1)->orderBy('name')->get();
                    
        $locations = Location::where('status',1)->orderBy('name')->get();
        $stations = AirportStation::where('status', 1)->orderBy('station_name')->get();
        // dd($locations);
        return view('asset-inventory.create',compact('assetTypes', 'locations', 'stations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_type_id'     => 'required|exists:asset_types,id',
            'asset_model_id'    => 'nullable|exists:asset_models,id',
            'location_id'       => 'required|exists:locations,id',
            'station_id'        => 'required|exists:airport_stations,id',
            'po_number'         => 'required|string|max:255',
            'installation_date' => 'required|date',
            'warranty_year'     => 'required|integer|min:0',
            'warranty_end'      => 'required|date',
            'tag_no'            => 'required|array|min:1',
            'tag_no.*'          => 'required|string',
            'serial_no'         => 'required|array|min:1',
            'serial_no.*'       => 'required|string|max:255',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Asset Model belongs to Asset Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('asset_model_id')) {

            $modelExists = AssetModel::where('id', $request->asset_model_id)
                ->where('asset_type_id', $request->asset_type_id)
                ->where('status', 1)
                ->exists();

            if (!$modelExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected asset model does not belong to the selected asset type.'
                ], 422);
            }
        }

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
                    'asset_type_id'     => $request->asset_type_id,
                    'asset_model_id'    => $request->asset_model_id,
                    'location_id'       => $request->location_id,
                    'station_id'        => $request->station_id,
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

    public function generateTags($locationId, $stationId, $assetTypeId, $quantity)
    {
        $location = Location::findOrFail($locationId);
        $station = AirportStation::findOrFail($stationId);
        $assetType = AssetType::findOrFail($assetTypeId);

        $prefix = strtoupper($station->short_name)
            . '/IT/'
            . now()->format('my')
            . '/'
            . strtoupper($assetType->short_name);

        $last = AssetInventory::where(
            'tag_no',
            'like',
            $prefix . '/%'
        )->latest('id')->first();

        $startNumber = 1;

        if ($last) {
            $startNumber = (int) substr($last->tag_no, -4) + 1;
        }

        $tags = [];

        for ($i = 0; $i < $quantity; $i++) {

            $running = $startNumber + $i;

            $tags[] = generateAssetTag(
                $locationId,
                $stationId,
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

    public function export(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Get current search filters
        |--------------------------------------------------------------------------
        */

        $filters = $request->only([
            'tag_no',
            'po_number',
            'serial_no',
            'asset_type',
            'asset_model',
            'installation_date',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Event Log
        |--------------------------------------------------------------------------
        */

        eventLog(
            'Download',
            'Asset Inventory',
            'Asset inventory Excel downloaded with filters: ' .
            json_encode(
                array_filter($filters, function ($value) {
                    return $value !== null && $value !== '';
                })
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Download Excel
        |--------------------------------------------------------------------------
        */

        return Excel::download(
            new AssetInventoryExport($filters),
            'Asset_Inventory_' . now()->format('d-m-Y_H-i-s') . '.xlsx'
        );
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'SL',
            'Asset Type',
            'Asset Model',
            'Asset Serial No.',
            'Asset Tag',
            'Region',
            'Airport/Station',
            'PO NO',
            'Installation Date',
            'Warranty (Yrs)',
            'Warranty End Date',
            'Asset Status',
        ];

        $column = 'A';

        foreach ($headers as $header) {

            $sheet->setCellValue(
                $column . '1',
                $header
            );

            $column++;
        }

        /*
        |--------------------------------------------------------------------------
        | Example row
        |--------------------------------------------------------------------------
        */

        $example = [
            1,
            2,
            18,
            '1234',
            'ATC/IT/0826/CP/0001',
            1,
            10,
            'gem-235',
            '27-08-2026',
            2,
            '26-08-2028',
            'Available',
        ];

        $column = 'A';

        foreach ($example as $value) {

            $sheet->setCellValue(
                $column . '2',
                $value
            );

            $column++;
        }

        /*
        |--------------------------------------------------------------------------
        | Auto width
        |--------------------------------------------------------------------------
        */

        foreach (range('A', 'L') as $column) {

            $sheet->getColumnDimension($column)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $fileName = 'asset_inventory_import_format.xlsx';

        $tempFile = tempnam(
            sys_get_temp_dir(),
            'asset_inventory_'
        );

        $writer->save($tempFile);

        return response()
            ->download($tempFile, $fileName)
            ->deleteFileAfterSend(true);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],
        ]);

        try {

            $import = new AssetInventoryImport();

            Excel::import($import, $request->file('excel_file'));

            /*
            |--------------------------------------------------------------------------
            | If errors exist
            |--------------------------------------------------------------------------
            */

            if (count($import->errors) > 0) {

                return response()->json([
                    'success' => false,
                    'message' => 'Some records could not be imported.',
                    'success_count' => $import->successCount,
                    'errors' => $import->errors,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $import->successCount .
                    ' asset inventory record(s) imported successfully.',
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Unable to import Excel file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
