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
        loadChart();
        loadEngineerChart();

    });

    function loadChart(){

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
                'Closed':'#FF6384'
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