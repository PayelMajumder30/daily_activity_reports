<div class="table-responsive">

    <table class="table table-bordered table-hover align-middle">

        <thead>

            <tr>
                <th>Sl No</th>
                <th>Call ID</th>
                <th>Custodian Name</th>
                <th>Asset Model</th>
                <th>Tag No</th>
                <th>Serial No</th>
                <th>Assigned To</th>
                <th>Call Generation Time</th>
                <th>Call Attend Time</th>
                <th>Call Closing Time</th>
                <th>Remarks</th>
                <th>Status</th>
            </tr>

        </thead>

        <tbody>

            @forelse($incidents as $incident)

                @php
                    $asset = $incident->assetIssueRegister?->assetInventory;
                    $custodian = $incident->assetIssueRegister?->custodian;
                @endphp

                <tr>

                    <td>{{ $loop->iteration }}</td>
                                           
                    <td>
                        <strong>{{ $incident->call_id }}</strong>                                                
                    </td>
                    
                    <td>{{ $custodian->custodian_name ?? 'N/A' }}</td>                                                                      
                    <td>
                        {{ $asset?->assetModel?->model_name ?? $asset?->assetModel?->title ?? 'N/A' }}                                                
                    </td>

                    <td>{{ $asset?->tag_no ?? 'N/A' }} </td>                                           
                    <td>{{ $asset?->serial_no ?? 'N/A' }}</td>                                         
                    <td>{{ $incident->supportUser?->name ?? 'N/A' }}</td>                                          
                    <td>{{ $incident->call_generated_at?->format('d-m-Y H:i:s') ?? '-' }}</td>                                           
                    <td>{{ $incident->call_attended_at?->format('d-m-Y H:i:s') ?? '-' }}</td>                                           
                    <td>{{ $incident->call_closed_at?->format('d-m-Y H:i:s') ?? '-' }}</td>                                           
                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($incident->remarks), 50) }}</td>                                           
                    <td>
                        @if($incident->status == 'Open')
                            <button type="button" class="btn btn-sm btn-danger incident-status-btn" data-id="{{ $incident->id }}" data-status="Open">                                                                                                         
                                Attend
                            </button>

                        @elseif($incident->status == 'Attend')

                            <button type="button" class="btn btn-sm btn-warning incident-status-btn" data-id="{{ $incident->id }}" data-status="Attend">                                                                                                         
                                Close
                            </button>

                        @else

                            <button type="button" class="btn btn-sm btn-success incident-status-btn" data-id="{{ $incident->id }}" data-status="Close">
                                Closed
                            </button>

                        @endif
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="11" class="text-center text-muted">                       
                        No Incident Found
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>