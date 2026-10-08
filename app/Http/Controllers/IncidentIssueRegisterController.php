<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{AssetIssueRegister, IncidentIssueRegister, IssueCategory, User};


class IncidentIssueRegisterController extends Controller
{
    /**
     * Incident Issue Register index.
     */
    // public function index()
    // {
    //     $openIncidents      = $this->incidentQuery()->where('status', 'Open')->latest()->get();                              
    //     $attendIncidents    = $this->incidentQuery()->where('status', 'Attend')->latest()->get();                                
    //     $closeIncidents     = $this->incidentQuery()->where('status', 'Close')->latest()->get();                              
    //     $engineers          = User::where('role', 2)->where('status', 1)->orderBy('name')->get();

    //     return view('incident-issue-register.index', compact('openIncidents', 'attendIncidents', 'closeIncidents', 'engineers'));                              
    // }

    public function index()
    {
        $openIncidents = $this->incidentQuery()
                                ->where('status', 'Open')
                                ->latest()
                                ->get();

        $attendIncidents = $this->incidentQuery()
                                ->where('status', 'Attend')
                                ->latest()
                                ->get();

        $closeIncidents = $this->incidentQuery()
                                ->where('status', 'Close')
                                ->latest()
                                ->get();


        /*
        |--------------------------------------------------------------------------
        | Engineers
        |--------------------------------------------------------------------------
        */

        $engineersQuery = User::where('role', 2)->where('status', 1);
            
        /*
        |--------------------------------------------------------------------------
        | Call Coordinator
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 1) {

            $stationIds = permittedStationIds();

            $engineersQuery->whereIn(
                'station_id',
                $stationIds
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        */

        $engineers = $engineersQuery->orderBy('name')->get();         

        return view('incident-issue-register.index', compact('openIncidents', 'attendIncidents', 'closeIncidents', 'engineers'));   
    }

    /**
     * Common incident query.
     */
    // private function incidentQuery()
    // {
    //     $query = IncidentIssueRegister::with([
    //         'assetIssueRegister.assetInventory.assetModel.assetType',
    //         'assetIssueRegister.custodian', 'supportUser', 'category',
    //     ]);

    //      /*
    //     |--------------------------------------------------------------------------
    //     | Engineer: Show only assigned incidents
    //     |--------------------------------------------------------------------------
    //     */

    //     if(auth()->user()->role == 2){
    //         $query->where('support_user_id', auth()->id());
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Management & Call Coordinator
    //     |--------------------------------------------------------------------------
    //     | Role 0 and Role 1 can see incidents of all engineers.
    //     |--------------------------------------------------------------------------
    //     */

    //     return $query;
    // }

    private function incidentQuery()
    {
        $query = IncidentIssueRegister::with([
            'assetIssueRegister.assetInventory.assetModel.assetType',
            'assetIssueRegister.assetInventory.location',
            'assetIssueRegister.assetInventory.station',
            'assetIssueRegister.custodian',
            'supportUser',
            'category',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Engineer
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 2) {

            $query->where(
                'support_user_id',
                auth()->id()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Call Coordinator
        |--------------------------------------------------------------------------
        | Only incidents from permitted stations.
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 1) {

            $stationIds = permittedStationIds();

            $query->whereHas(
                'assetIssueRegister.assetInventory',
                function ($q) use ($stationIds) {

                    $q->whereIn(
                        'station_id',
                        $stationIds
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        | No restriction.
        |--------------------------------------------------------------------------
        */

        return $query;
    }

    /**
     * Create incident page.
     */
    // public function create()
    // {
    //     // Engineer cannot create incident
    //     if (auth()->user()->role == 2) {
    //         abort(403);
    //     }

    //     $engineers = User::where('role', 2)
    //         ->where('status', 1)
    //         ->orderBy('name')
    //         ->get();

    //     $categories = IssueCategory::where('status', 1)
    //         ->orderBy('title')
    //         ->get();

    //     return view('incident-issue-register.create', compact('engineers', 'categories'));          
        
    // }

    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Engineer cannot create incident
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 2) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Active Engineers
        |--------------------------------------------------------------------------
        */

        $engineersQuery = User::where('role', 2)->where('status', 1);       

        /*
        |--------------------------------------------------------------------------
        | Call Coordinator
        |--------------------------------------------------------------------------
        | Show only engineers belonging to permitted stations.
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 1) {

            $stationIds = permittedStationIds();
            $engineersQuery->whereIn('station_id', $stationIds);
        }

        $engineers = $engineersQuery->orderBy('name')->get();
                      
        /*
        |--------------------------------------------------------------------------
        | Issue Categories
        |--------------------------------------------------------------------------
        */

        $categories = IssueCategory::where('status', 1)->orderBy('title')->get();
        return view('incident-issue-register.create', compact('engineers', 'categories'));
                         
    }

    /**
     * Search currently issued assets.
     */
    // public function searchAsset(Request $request)
    // {
    //     $search = trim($request->search);

    //     if (!$search) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Please enter a search value.'
    //         ]);
    //     }

    //     $assetIssues = AssetIssueRegister::with([
    //         'assetInventory.assetModel.assetType',
    //         'assetInventory.location',
    //         'custodian',
    //     ])
    //         ->where('issue_status', 'Issued')
    //         ->where(function ($query) use ($search) {

    //             $query->whereHas('assetInventory', function ($q) use ($search) {
    //                 $q->where('tag_no', 'like', "%{$search}%")
    //                     ->orWhere('serial_no', 'like', "%{$search}%");
    //             });

    //             $query->orWhereHas('custodian', function ($q) use ($search) {
    //                 $q->where('custodian_name', 'like', "%{$search}%");
    //             });
    //         })
    //         ->latest('id')
    //         ->get();

    //     $data = $assetIssues->map(function ($issue) {

    //         $asset = $issue->assetInventory;
    //         $custodian = $issue->custodian;

    //         return [
    //             'id'            => $issue->id,
    //             'tag_no'        => $asset?->tag_no ?? 'N/A',
    //             'serial_no'     => $asset?->serial_no ?? 'N/A',
    //             'asset_model'   => $asset?->assetModel?->model_name ?? $asset?->assetModel?->title ?? 'N/A',                                   
    //             'asset_type'    => $asset?->assetModel?->assetType?->name ?? 'N/A',                
    //             'custodian_name' => $custodian?->custodian_name ?? 'N/A',
                   
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $data,
    //     ]);
    // }

    public function searchAsset(Request $request)
    {
        $search = trim($request->search);

        if (!$search) {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a search value.'
            ]);
        }


        $assetIssues = AssetIssueRegister::with([
            'assetInventory.assetModel.assetType',
            'assetInventory.location',
            'custodian',
        ])->where('issue_status', 'Issued')
            
            /*
            |--------------------------------------------------------------------------
            | Location Permission
            |--------------------------------------------------------------------------
            */

            ->when(auth()->user()->role == 1, function ($query) {
                $stationIds = permittedStationIds();
                $query->whereHas('assetInventory',              
                    function ($q) use ($stationIds) {
                        $q->whereIn('station_id', $stationIds);
                    }
                );
            })


            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            ->where(function ($query) use ($search) {

                $query->whereHas('assetInventory',
                    
                    function ($q) use ($search) {

                        $q->where('tag_no', 'like', "%{$search}%")
                            ->orWhere(
                                'serial_no',
                                'like',
                                "%{$search}%"
                            );
                    }
                );

                $query->orWhereHas(
                    'custodian',
                    function ($q) use ($search) {

                        $q->where(
                            'custodian_name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            })->latest('id')->get();           

        $data = $assetIssues->map(function ($issue) {

            $asset = $issue->assetInventory;
            $custodian = $issue->custodian;

            return [

                'id'            => $issue->id,
                'tag_no'        => $asset?->tag_no ?? 'N/A',                   
                'serial_no'     => $asset?->serial_no ?? 'N/A',                   
                'asset_model'   => $asset?->assetModel?->model_name ?? $asset?->assetModel?->title ?? 'N/A',                                         
                'asset_type'    => $asset?->assetModel?->assetType?->name ?? 'N/A',                            
                'custodian_name' => $custodian?->custodian_name ?? 'N/A',
                    
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get complete selected asset details.
     */
    // public function assetDetails($id)
    // {
    //     $assetIssue = AssetIssueRegister::with([
    //         'assetInventory.assetModel.assetType',
    //         'assetInventory.location',
    //         'assetInventory.station',
    //         'custodian',
    //     ])
    //         ->where('issue_status', 'Issued')
    //         ->find($id);

    //     if (!$assetIssue) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Issued asset not found.'
    //         ], 404);
    //     }

    //     $asset      = $assetIssue->assetInventory;
    //     $custodian  = $assetIssue->custodian;

    //     return response()->json([
    //         'status' => true,
    //         'data' => [
    //             'id'            => $assetIssue->id,
    //             'tag_no'        => $asset?->tag_no ?? 'N/A',
    //             'serial_no'     => $asset?->serial_no ?? 'N/A',
    //             'asset_model'   => $asset?->assetModel?->model_name ?? $asset?->assetModel?->title ?? 'N/A',                                     
    //             'asset_type'    => $asset?->assetModel?->assetType?->name ?? 'N/A',                   
    //             'custodian_name' => $custodian?->custodian_name ?? 'N/A',                  
    //             'location'      => $asset?->location?->name ?? 'N/A',                 
    //             'station'       => $asset?->station?->station_name ?? 'N/A',
                    
    //         ]
    //     ]);
    // }

    public function assetDetails($id)
    {
        $assetIssue = AssetIssueRegister::with([
            'assetInventory.assetModel.assetType',
            'assetInventory.location',
            'assetInventory.station',
            'custodian',
        ])->where('issue_status', 'Issued')
            
            /*
            |--------------------------------------------------------------------------
            | Location Permission
            |--------------------------------------------------------------------------
            */

            ->when(auth()->user()->role == 1, function ($query) {

                $stationIds = permittedStationIds();
                $query->whereHas(
                    'assetInventory',
                    function ($q) use ($stationIds) {
                        $q->whereIn('station_id', $stationIds);
                    }
                );
            })


            ->find($id);


        if (!$assetIssue) {

            return response()->json([
                'status' => false,
                'message' => 'Issued asset not found or you do not have permission to access this asset.'
            ], 404);
        }


        $asset = $assetIssue->assetInventory;
        $custodian = $assetIssue->custodian;


        return response()->json([
            'status' => true,

            'data' => [
                'id'            => $assetIssue->id,                  
                'tag_no'        => $asset?->tag_no ?? 'N/A',                  
                'serial_no'     => $asset?->serial_no ?? 'N/A',                  
                'asset_model'   => $asset?->assetModel?->model_name ?? $asset?->assetModel?->title ?? 'N/A',                                              
                'asset_type'    => $asset?->assetModel?->assetType?->name ?? 'N/A',                             
                'custodian_name' => $custodian?->custodian_name ?? 'N/A',                
                'location'      => $asset?->location?->name ?? 'N/A',                   
                'station'       => $asset?->station?->station_name ?? 'N/A',                
            ]
        ]);
    }

    /**
     * Store new incident.
     */
    // public function store(Request $request)
    // {
    //     if (auth()->user()->role == 2) {
    //         abort(403);
    //     }

    //     $request->validate([
    //         'asset_issue_register_id' => [
    //             'required',
    //             'exists:asset_issue_registers,id'
    //         ],

    //         'support_user_id' => [
    //             'required',
    //             'exists:users,id'
    //         ],

    //         'category_id' => [
    //             'required',
    //             'exists:issue_categories,id'
    //         ],

    //         'remarks' => [
    //             'required',
    //             'string'
    //         ],
    //     ]);

    //     // Make sure selected user is actually an engineer
    //     $engineer = User::where('id', $request->support_user_id)
    //         ->where('role', 2)
    //         ->where('status', 1)
    //         ->first();

    //     if (!$engineer) {
    //         return back()
    //             ->withErrors([
    //                 'support_user_id' => 'Invalid engineer selected.'
    //             ])
    //             ->withInput();
    //     }

    //     // Make sure selected issue record is currently issued
    //     $assetIssue = AssetIssueRegister::where('id', $request->asset_issue_register_id)
    //         ->where('issue_status', 'Issued')
    //         ->first();

    //     if (!$assetIssue) {
    //         return back()
    //             ->withErrors([
    //                 'asset_issue_register_id' => 'Selected asset is not currently issued.'
    //             ])
    //             ->withInput();
    //     }

    //     DB::transaction(function () use ($request) {

    //         $incident = IncidentIssueRegister::create([
    //             'call_id'           => 'TEMP',
    //             'asset_issue_register_id' => $request->asset_issue_register_id,
    //             'support_user_id'   => $request->support_user_id,
    //             'category_id'       => $request->category_id,
    //             'remarks'           => $request->remarks,
    //             'status'            => 'Open',
    //             'call_generated_at' => now(),
    //         ]);

    //         $incident->update([
    //             'call_id' => 'INC-' .
    //                 now()->format('Ymd') . '-' .
    //                 str_pad($incident->id, 4, '0', STR_PAD_LEFT),
    //         ]);
    //     });

    //     return redirect()->route('incident_issue_register.index')->with('success', 'Incident generated successfully.');
                        
    // }

    public function store(Request $request)
    {
        if (auth()->user()->role == 2) {
            abort(403);
        }

        $request->validate([
            'asset_issue_register_id' => [
                'required',
                'exists:asset_issue_registers,id'
            ],

            'support_user_id' => [
                'required',
                'exists:users,id'
            ],

            'category_id' => [
                'required',
                'exists:issue_categories,id'
            ],

            'remarks' => [
                'required',
                'string'
            ],
        ]);

        // Make sure selected user is actually an engineer
        $engineer = User::where('id', $request->support_user_id)
            ->where('role', 2)
            ->where('status', 1)
            ->first();

        if (!$engineer) {
            return back()
                ->withErrors([
                    'support_user_id' => 'Invalid engineer selected.'
                ])->withInput();               
        }

        // Make sure selected issue record is currently issued
        $assetIssue = AssetIssueRegister::where('id', $request->asset_issue_register_id)
            ->where('issue_status', 'Issued')
            ->first();

        if (auth()->user()->role == 1) {

            $stationIds = permittedStationIds();

            /*
            |--------------------------------------------------------------------------
            | Check Asset Station
            |--------------------------------------------------------------------------
            */

            if (
                !$assetIssue->assetInventory ||
                !$stationIds->contains(
                    $assetIssue->assetInventory->station_id
                )
            ) {
                return back()
                    ->withErrors(['asset_issue_register_id' => 'You do not have permission to generate an incident for this asset.'])->withInput();                                                           
                    
            }

            /*
            |--------------------------------------------------------------------------
            | Check Engineer Station
            |--------------------------------------------------------------------------
            */

            if (!$stationIds->contains($engineer->station_id)) {
                return back()->withErrors(['support_user_id' => 'Selected engineer is outside your permitted station.'])->withInput();                                                                                
            }
        }

        DB::transaction(function () use ($request) {

            $incident = IncidentIssueRegister::create([
                'call_id'           => 'TEMP',
                'asset_issue_register_id' => $request->asset_issue_register_id,
                'support_user_id'   => $request->support_user_id,
                'category_id'       => $request->category_id,
                'remarks'           => $request->remarks,
                'status'            => 'Open',
                'call_generated_at' => now(),
            ]);

            $incident->update([
                'call_id' => 'INC-' .
                    now()->format('Ymd') . '-' .
                    str_pad($incident->id, 4, '0', STR_PAD_LEFT),
            ]);
        });

        return redirect()->route('incident_issue_register.index')->with('success', 'Incident generated successfully.');
                        
    }

    /**
     * Get incident details.
     */
    public function details($id)
    {
        $incident = $this->incidentQuery()->find($id);
            
        if (!$incident) {
            return response()->json([
                'status' => false,
                'message' => 'Incident not found.'
            ], 404);
        }

        return response()->json([
            'status'    => true,
            'data'      => [
                'id'    => $incident->id,
                'call_id'   => $incident->call_id,
                'status'    => $incident->status,
                'category'  => $incident->category?->title ?? 'N/A',
                'remarks'   => $incident->remarks,
                'tag_no'    => $incident->assetIssueRegister?->assetInventory?->tag_no ?? 'N/A',                                    
                'serial_no' => $incident->assetIssueRegister?->assetInventory?->serial_no ?? 'N/A',                                   
                'asset_model' =>
                    $incident->assetIssueRegister?->assetInventory?->assetModel?->model_name
                    ?? $incident->assetIssueRegister?->assetInventory?->assetModel?->title
                    ?? 'N/A',

                'custodian'         => $incident->assetIssueRegister?->custodian?->custodian_name ?? 'N/A',                                    
                'call_generated_at' => $incident->call_generated_at?->format('d-m-Y H:i:s'),                
                'call_attended_at'  => $incident->call_attended_at?->format('d-m-Y H:i:s'),                    
                'call_closed_at'    => $incident->call_closed_at?->format('d-m-Y H:i:s'),
                    
            ]
        ]);
    }

    /**
     * Attend incident.
     */
    public function attend(Request $request, $id)
    {
        if (auth()->user()->role != 2) {
            abort(403);
        }

        $request->validate([
            'remarks' => [
                'required',
                'string'
            ],
        ]);

        $incident = IncidentIssueRegister::where('id', $id)
            ->where('status', 'Open')
            ->where('support_user_id', auth()->id())
            ->firstOrFail();

        if (!$incident) {
            return response()->json([
                'status'    => false,
                'message'   => 'Incident is not available for attendance.'
            ], 422);
        }

        $incident->update([
            'remarks'   => $request->remarks,
            'status'    => 'Attend',
            'call_attended_at' => now(),
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Incident attended successfully.'
        ]);
    }

    /**
     * Close incident.
     */
    public function close(Request $request, $id)
    {
        if (auth()->user()->role != 2) {
            abort(403);
        }

        $request->validate([
            'remarks' => [
                'required',
                'string'
            ],
        ]);

        $incident = IncidentIssueRegister::where('id', $id)
            ->where('status', 'Attend')
            ->where('support_user_id', auth()->id())
            ->firstOrFail();

        if (!$incident) {
            return response()->json([
                'status' => false,
                'message' => 'Incident is not available for closing.'
            ], 422);
        }

        $incident->update([
            'remarks' => $request->remarks,
            'status' => 'Close',
            'call_closed_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Incident closed successfully.'
        ]);
    }

    /**
     * Change assigned engineer.
     */
    // public function changeEngineer(Request $request, $id){
    //     if(!in_array(auth()->user()->role, [0,1])){
    //         abort(403);
    //     }

    //     $request->validate([
    //         'support_user_id' => [
    //             'required',
    //             'exists:users,id'
    //         ],
    //     ]);

    //     $incident = IncidentIssueRegister::findOrFail($id);

    //      /*
    //     |--------------------------------------------------------------------------
    //     | Engineer can only be changed while incident is Open
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($incident->status !== 'Open') {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Engineer cannot be changed after the incident has been attended.'
    //         ], 422);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Validate selected user is an active Engineer
    //     |--------------------------------------------------------------------------
    //     */

    //     $engineer = User::where('id', $request->support_user_id)->where('role', 2)->where('status', 1)->first();                           

    //     if (!$engineer) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Invalid engineer selected.'
    //         ], 422);
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Do not allow selecting the same engineer
    //     |--------------------------------------------------------------------------
    //     */

    //     if ((int) $incident->support_user_id === (int) $engineer->id) {

    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Please select a different engineer.'
    //         ], 422);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Update Engineer
    //     |--------------------------------------------------------------------------
    //     */

    //     $incident->update([
    //         'support_user_id' => $engineer->id,
    //     ]);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Assigned engineer changed successfully.',
    //         'engineer' => $engineer->name,
    //     ]);
    // }

    public function changeEngineer(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | Only Management and Call Coordinator
        |--------------------------------------------------------------------------
        */

        if (!in_array(auth()->user()->role, [0, 1])) {
            abort(403);
        }

        $request->validate([
            'support_user_id' => [
                'required',
                'exists:users,id'
            ],
        ]);


        $incident = IncidentIssueRegister::with('assetIssueRegister.assetInventory')->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Engineer can only be changed while Open
        |--------------------------------------------------------------------------
        */

        if ($incident->status !== 'Open') {
            return response()->json([
                'status' => false,
                'message' =>
                    'Engineer cannot be changed after the incident has been attended.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Current Asset Location
        |--------------------------------------------------------------------------
        */

        $asset = $incident->assetIssueRegister?->assetInventory;                

        if (!$asset) {

            return response()->json([
                'status' => false,
                'message' => 'Asset information not found.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Call Coordinator Station Permission
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 1) {
            $stationIds  = permittedStationIds();

            /*
            |--------------------------------------------------------------------------
            | Incident asset must belong to permitted station
            |--------------------------------------------------------------------------
            */

            if (!$stationIds ->contains($asset->station_id)) {

                return response()->json([
                    'status' => false,
                    'message' =>
                        'You do not have permission to change the engineer for this incident.'
                ], 403);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Selected Engineer
        |--------------------------------------------------------------------------
        */

        $engineerQuery = User::where('id', $request->support_user_id)->where('role', 2)->where('status', 1);
                  
        /*
        |--------------------------------------------------------------------------
        | Call Coordinator can assign only engineers
        | from permitted stations
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 1) {
            $stationIds  = permittedStationIds();
            $engineerQuery->whereIn('station_id', $stationIds );
        }


        $engineer = $engineerQuery->first();

        if (!$engineer) {
            return response()->json([
                'status' => false,
                'message' =>
                    'Invalid engineer selected or engineer is outside your permitted location.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Do not select same engineer
        |--------------------------------------------------------------------------
        */

        if ((int) $incident->support_user_id === (int) $engineer->id) {

            return response()->json([
                'status' => false,
                'message' => 'Please select a different engineer.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Engineer
        |--------------------------------------------------------------------------
        */

        $incident->update([
            'support_user_id' => $engineer->id,
        ]);


        return response()->json([
            'status' => true,
            'message' => 'Assigned engineer changed successfully.',
            'engineer' => $engineer->name,
        ]);
    }
}