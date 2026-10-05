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

        return view('incident-issue-register.index', compact('openIncidents', 'attendIncidents', 'closeIncidents'));
                        
        
    }

    /**
     * Common incident query.
     */
    private function incidentQuery()
    {
        return IncidentIssueRegister::with([
            'assetIssueRegister.assetInventory.assetModel.assetType',
            'assetIssueRegister.custodian',
            'supportUser',
            'category',
        ]);
    }

    /**
     * Create incident page.
     */
    public function create()
    {
        // Engineer cannot create incident
        if (auth()->user()->role == 2) {
            abort(403);
        }

        $engineers = User::where('role', 2)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $categories = IssueCategory::where('status', 1)
            ->orderBy('title')
            ->get();

        return view('incident-issue-register.create', compact('engineers', 'categories'));          
        
    }

    /**
     * Search currently issued assets.
     */
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
        ])
            ->where('issue_status', 'Issued')
            ->where(function ($query) use ($search) {

                $query->whereHas('assetInventory', function ($q) use ($search) {
                    $q->where('tag_no', 'like', "%{$search}%")
                        ->orWhere('serial_no', 'like', "%{$search}%");
                });

                $query->orWhereHas('custodian', function ($q) use ($search) {
                    $q->where('custodian_name', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->get();

        $data = $assetIssues->map(function ($issue) {

            $asset = $issue->assetInventory;
            $custodian = $issue->custodian;

            return [
                'id' => $issue->id,

                'tag_no' => $asset?->tag_no ?? 'N/A',
                'serial_no' => $asset?->serial_no ?? 'N/A',

                'asset_model' => $asset?->assetModel?->model_name
                    ?? $asset?->assetModel?->title
                    ?? 'N/A',

                'asset_type' => $asset?->assetModel?->assetType?->title
                    ?? 'N/A',

                'custodian_name' => $custodian?->custodian_name
                    ?? 'N/A',
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
    public function assetDetails($id)
    {
        $assetIssue = AssetIssueRegister::with([
            'assetInventory.assetModel.assetType',
            'assetInventory.location',
            'assetInventory.station',
            'custodian',
        ])
            ->where('issue_status', 'Issued')
            ->find($id);

        if (!$assetIssue) {
            return response()->json([
                'status' => false,
                'message' => 'Issued asset not found.'
            ], 404);
        }

        $asset = $assetIssue->assetInventory;
        $custodian = $assetIssue->custodian;

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $assetIssue->id,

                'tag_no' => $asset?->tag_no ?? 'N/A',
                'serial_no' => $asset?->serial_no ?? 'N/A',

                'asset_model' => $asset?->assetModel?->model_name
                    ?? $asset?->assetModel?->title
                    ?? 'N/A',

                'asset_type' => $asset?->assetModel?->assetType?->name
                    ?? 'N/A',

                'custodian_name' => $custodian?->custodian_name
                    ?? 'N/A',

                'location' => $asset?->location?->name
                    ?? 'N/A',

                'station' => $asset?->station?->station_name
                    ?? 'N/A',
            ]
        ]);
    }

    /**
     * Store new incident.
     */
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
                ])
                ->withInput();
        }

        // Make sure selected issue record is currently issued
        $assetIssue = AssetIssueRegister::where('id', $request->asset_issue_register_id)
            ->where('issue_status', 'Issued')
            ->first();

        if (!$assetIssue) {
            return back()
                ->withErrors([
                    'asset_issue_register_id' => 'Selected asset is not currently issued.'
                ])
                ->withInput();
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
        $incident = $this->incidentQuery()
            ->find($id);

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
            ->first();

        if (!$incident) {
            return response()->json([
                'status' => false,
                'message' => 'Incident is not available for attendance.'
            ], 422);
        }

        $incident->update([
            'remarks' => $request->remarks,
            'status' => 'Attend',
            'call_attended_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Incident attended successfully.'
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
            ->first();

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
}