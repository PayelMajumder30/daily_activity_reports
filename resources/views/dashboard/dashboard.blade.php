@extends('layouts.app')

@section('title', 'Complaint Dashboard')

@section('content')

<div class="container-fluid py-4">
 
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-speedometer2"></i>
                Complaint Management Dashboard
            </h2>
            <p class="text-muted">
                Monitor complaint status, analyze engineer performance, and view complaint distribution using interactive filters and charts.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-body">
            <div class="row">

                <div class="col-md-3">
                    <label>Engineer</label>
                    <select id="engineer" class="form-select">

                        <option value="">All</option>

                        @foreach($engineers as $eng)
                            <option value="{{ $eng->engineer_name }}">
                                {{ $eng->engineer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="text" id="from_date" class="form-control datepicker">    
                    <small id="from_date_error" class="text-danger"></small>   
                </div>

                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="text" id="to_date" class="form-control datepicker">
                    <small id="to_date_error" class="text-danger"></small>     
                </div>

                <div class="col-md-3">
                    <label>Status</label>
                    <select id="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->status }}">
                            {{ $status->status }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3">

                <button class="btn btn-primary" id="btnSearch">
                    Search
                </button>

                <button class="btn btn-secondary" id="btnReset">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- pie chart -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h5 class="mb-0">Status Distribution</h5>
                </div>

                <div class="card-body text-center">
                    <div style="height:380px; width:380px;">
                        <canvas id="statusChart"></canvas>
                    </div>           
                </div>
            </div>
        </div>

         <!-- bar chart -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h5 class="mb-0">Engineer-wise Status</h5>
                </div>
                <div class="card-body">
                    <div style="height:380px;">
                        <canvas id="engineerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity details -->
        <div class="card shadow mt-3" id="complaintListCard" style="display:none;">
            <div class="card-header">
                <h5 id="selectedStatusHeading"></h5>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Activity Details</th>
                            <th>Engineer</th>
                            <th>Asset Tag No.</th>
                            <th>Status</th>
                            <th>Activity Duration</th>
                            <th>Report Date</th>
                        </tr>
                    </thead>

                    <tbody id="complaintListBody"></tbody>
                </table>

                <!-- pagination -->
                <div class="mt-3 d-flex justify-content-center">
                    <div id="paginationLinks"></div>
                </div>
            </div>
        </div>
    </div>   
</div>

@push('scripts')

<script>
    let chart;
    let engineerChart;

    loadChart();
    loadEngineerChart();
    $('#btnSearch').click(function(){

        //clear previous error
        $('#from_date_error').text('');
        $('#to_date_error').text('');

        let fromDate = $('#from_date').val();
        let toDate = $('#to_date').val();

        // Validate only if both dates are selected
        if(fromDate !== '' && toDate !== ''){
            let from = new Date(fromDate);
            let to = new Date(toDate);

            if(from>to){
                $('#from_date_error').text('From Date must be less than or equal to To Date.');
                $('#to_date_error').text('To Date must be greater than or equal to From Date.');

                return; 
            }
        }

        hideComplaintList();
        loadChart();
        loadEngineerChart();
    });

    $('#btnReset').click(function(){

        $('#from_date').val('');
        $('#to_date').val('');
        $('#engineer').val('');
        $('#status').val('');

        $('#from_date_error').text('');
        $('#to_date_error').text('');
        hideComplaintList();
        loadChart();
        loadEngineerChart();

        $('#complaintListCard').hide();
        $('#complaintListBody').html('');

    });

    function loadChart(){
        
        hideComplaintList();

        $.get("{{ route('dashboard.pieChart') }}",{

            from_date:$('#from_date').val(),
            to_date:$('#to_date').val(),
            engineer:$('#engineer').val(),
            status:$('#status').val()

        },function(data){

            let labels=[];
            let totals=[];

            $.each(data,function(i,row){
                labels.push(row.status);
                totals.push(row.total);
            });

            if(chart){
                chart.destroy();
            }
            // Create a new Chart.js pie chart
            chart=new Chart(document.getElementById('statusChart'),{
                type:'pie',
                
                // Data to display in the chart
                data:{

                    // Labels shown in the legend
                    // Example: Open, Resolved, Closed
                    labels:labels,

                    datasets:[{
                        // Number of records for each status
                        // Example: [20, 15, 8]
                        data:totals,
                        
                        backgroundColor:[
                            '#36A2EB',
                            '#FF6384',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]

                    }]
                },

                 // Chart display settings
                options:{
                    // Automatically resize when browser size changes
                    responsive:true,
                    // Allow custom width and height from CSS
                    maintainAspectRatio:false,
                    onClick:function(event,elements){
                        if(elements.length==0)
                            return;

                        let index=elements[0].index;
                        let status=labels[index];
                        loadComplaintList(status);
                    },
                    plugins:{
                        // Configure the legend (status names)
                        legend:{
                            // Show legend below the chart
                            position:'bottom'
                        }
                    }
                }
            });
        });

    }

    function hideComplaintList(){
        $('#complaintListCard').hide();
        $('#complaintListBody').empty();
        $('#selectedStatusHeading').text('');
    }

      // for pie chart details status-wise
    function loadComplaintList(status,page=1){
        $.get("{{route('dashboard.statusDetails')}}",{
            from_date:$('#from_date').val(),
            to_date:$('#to_date').val(),
            engineer:$('#engineer').val(),
            asset_tag_no:$('#asset_tag_no').val(),
            status:status,
            page: page
        },function(res){
           
            let html = '';
            
            $.each(res.data,function(i,row){

                let sl = ((res.current_page - 1) * res.per_page) + i + 1;
                let report_date = '-';
                if(row.upload && row.upload.report_date){
                   
                let d = new Date(row.upload.report_date);

                let day = String(d.getDate()).padStart(2, '0');
                let month = String(d.getMonth() + 1).padStart(2, '0');
                let year = d.getFullYear();

                reportDate = `${day}-${month}-${year}`;
                }
                html += `
                    <tr>
                        <td>${sl}</td>
                        <td class="activity-details">${row.complaint_title}</td>
                        <td>${row.engineer_name}</td>
                        <td>${row.asset_tag_no}</td>
                        <td>${row.status}</td>
                        <td>${row.resolution_time ? row.resolution_time : 'NA'}</td>
                        <td>${reportDate}</td>
                    </tr>
                `;
            });

            $('#complaintListBody').html(html);
            buildPagination(res,status);
            $('#selectedStatusHeading').text(status + "Activities");
            $('#complaintListCard').show();
        });

    }

    // build pagination function
    function buildPagination(res, status) {
        let html = '';
        if (res.last_page > 1) {
            html += '<nav><ul class="pagination justify-content-end">';
            // First
            html += `
                <li class="page-item ${res.current_page == 1 ? 'disabled' : ''}">
                    <a href="#" class="page-link page-number"
                        data-page="1"
                        data-status="${status}">
                        &laquo;
                    </a>
                </li>
            `;

            // Previous
            html += `
                <li class="page-item ${res.current_page == 1 ? 'disabled' : ''}">
                    <a href="#" class="page-link page-number"
                        data-page="${res.current_page-1}"
                        data-status="${status}">
                        &lsaquo;
                    </a>
                </li>
            `;

            let start = Math.max(1, res.current_page - 2);
            let end   = Math.min(res.last_page, res.current_page + 2);
            if(start > 1){
                html += `
                    <li class="page-item">
                        <a href="#" class="page-link page-number"
                            data-page="1"
                            data-status="${status}">
                            1
                        </a>
                    </li>
                `;

                if(start > 2){
                    html += `
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    `;
                }
            }

            for(let i=start;i<=end;i++){
                html += `
                    <li class="page-item ${i==res.current_page?'active':''}">
                        <a href="#"
                        class="page-link page-number"
                        data-page="${i}"
                        data-status="${status}">
                            ${i}
                        </a>
                    </li>
                `;
            }

            if(end < res.last_page){
                if(end < res.last_page-1){
                    html += `
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    `;
                }

                html += `
                    <li class="page-item">
                        <a href="#"
                        class="page-link page-number"
                        data-page="${res.last_page}"
                        data-status="${status}">
                            ${res.last_page}
                        </a>
                    </li>
                `;
            }

            // Next
            html += `
                <li class="page-item ${res.current_page==res.last_page?'disabled':''}">
                    <a href="#"
                    class="page-link page-number"
                    data-page="${res.current_page+1}"
                    data-status="${status}">
                        &rsaquo;
                    </a>
                </li>
            `;

            // Last
            html += `
                <li class="page-item ${res.current_page==res.last_page?'disabled':''}">
                    <a href="#"
                    class="page-link page-number"
                    data-page="${res.last_page}"
                    data-status="${status}">
                        &raquo;
                    </a>
                </li>
            `;

            html += '</ul></nav>';
        }

        $('#paginationLinks').html(html);
    }

    $(document).on('click','.page-number',function(e){
        e.preventDefault();

        loadComplaintList(
            $(this).data('status'),
            $(this).data('page')
        );

    });

    // for bar chart details engineer-wise
    function loadEngineerComplaintList(engineer,status,page=1){
        $.get("{{route('dashboard.statusDetails')}}", {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            asset_tag_no: $('#asset_tag_no').val(),
            engineer: engineer,
            status: status,
            page: page
        }, function(res){
            
            let html='';
            $.each(res.data,function(i,row){

                let sl = ((res.current_page - 1) * res.per_page) + i + 1;
                let reportDate='-';
                if(row.upload && row.upload.report_date){
                    let d=new Date(row.upload.report_date);
                    reportDate= String(d.getDate()).padStart(2,'0')+'-'+String(d.getMonth()).padStart(2,'0')+'-'+d.getFullYear();
                }

                html +=`
                    <tr>
                        <td>${sl}</td>
                        <td>${row.complaint_title}</td>
                        <td>${row.engineer_name}</td>
                        <td>${row.asset_tag_no}</td>
                        <td>${row.status}</td>
                        <td>${row.resolution_time ?? 'NA'}</td>
                        <td>${reportDate}</td>
                    </tr>`;
            });

            $('#complaintListBody').html(html);
            buildEngineerPagination(res,engineer,status);
            $('#selectedStatusHeading').text(engineer + " - " + status + " Activities ");
            $('#complaintListCard').show();
        });
    }

    // pagination
    function buildEngineerPagination(res,engineer,status){
        let html='';
        if(res.last_page>1){
            html+='<nav><ul class="pagination">';
            for(let i=1;i<=res.last_page;i++){
                html+=`
                    <li class="page-item ${i==res.current_page?'active':''}">
                        <a href="#"
                        class="page-link engineer-page"
                        data-page="${i}"
                        data-engineer="${engineer}"
                        data-status="${status}">
                            ${i}
                        </a>
                    </li>
                `;
            }

            html+='</ul></nav>';
        }
        $('#paginationLinks').html(html);
    }

    $(document).on('click','.engineer-page',function(e){
        e.preventDefault();
        loadEngineerComplaintList(
            $(this).data('engineer'),
            $(this).data('status'),
            $(this).data('page')
        );
    });

    function loadEngineerChart(){

        $.get("{{ route('dashboard.barChart') }}",{

            from_date:$('#from_date').val(),
            to_date:$('#to_date').val(),
            engineer:$('#engineer').val(),
            status:$('#status').val()

        },function(data){

            let engineers=[];
            let statuses=[];

            data.forEach(function(row){

                if(!engineers.includes(row.engineer_name))
                    engineers.push(row.engineer_name);

                if(!statuses.includes(row.status))
                    statuses.push(row.status);

            });

            let colors={
                'Open':'#36A2EB',
                'Resolved':'#FFCE56',
                'Completed':'#4BC0C0',
                'Closed':'#FF6384',
                "Done":'#9966FF'
            };

            let datasets=[];

            statuses.forEach(function(status){

                let values=[];

                engineers.forEach(function(engineer){
                    let found=data.find(function(r){
                        return r.engineer_name==engineer &&
                            r.status==status;
                    });

                    values.push(found?found.total:0);

                });

                datasets.push({

                    label:status,
                    data:values,
                    backgroundColor:colors[status] || '#999'

                });

            });

            if(engineerChart){
                engineerChart.destroy();
            }

            engineerChart=new Chart(
                document.getElementById('engineerChart'),
                {
                    type:'bar',
                    data:{
                        labels:engineers,
                        datasets:datasets
                    },

                    options:{
                        responsive:true,
                        maintainAspectRatio:false,

                        onClick:function(event,elements){
                            if(elements.length===0)
                                return;

                            let point=elements[0];
                            let engineer=engineers[point.index];
                            let status=datasets[point.datasetIndex].label;
                            loadEngineerComplaintList(engineer,status);
                        },
                        plugins:{
                            legend:{
                                position:'bottom'
                            }
                        },

                        scales:{
                            x:{
                                stacked:true
                            },
                            y:{
                                stacked:true,
                                beginAtZero:true
                            }
                        }
                    }
                }

            );

        });

    }
</script>
@endpush
@endsection