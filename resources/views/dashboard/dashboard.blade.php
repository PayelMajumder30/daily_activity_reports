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
                Upload daily complaint reports and search complaint details.
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
                    <label>From Date</label>
                    <input type="text"
                        id="from_date"
                        class="form-control datepicker">
                </div>

                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="text"
                        id="to_date"
                        class="form-control datepicker">
                </div>

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

    <div class="card shadow mt-4">

        <div class="card-header">

            <h5>Status Distribution</h5>

        </div>

        <div class="card-body">

            <canvas id="statusChart" width="50" height="50"></canvas>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chart;

    loadChart();

    $('#btnSearch').click(function(){

        loadChart();

    });

    $('#btnReset').click(function(){

        $('#from_date').val('');
        $('#to_date').val('');
        $('#engineer').val('');
        $('#status').val('');

        loadChart();

    });

    function loadChart(){

        $.get("{{ route('dashboard.chart') }}",{

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

            chart=new Chart(document.getElementById('statusChart'),{

                type:'pie',

                data:{

                    labels:labels,

                    datasets:[{

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

                options:{

                    responsive:true,

                    plugins:{

                        legend:{
                            position:'bottom'
                        }

                    }

                }

            });

        });

    }
</script>
@endpush
@endsection