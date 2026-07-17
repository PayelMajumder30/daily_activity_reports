<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Complaint Dashboard')</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.dataTables.min.css') }}">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="{{ asset('assets/css/flatpickr.min.css') }}">

    <!-- SweetAlert -->
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">

    @stack('styles')

</head>

<body>

    <div class="d-flex">
        @include('layouts.sidebar')

        <div class="flex-grow-1">
            @yield('content')
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Bootstrap -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('assets/js/dataTables.min.js') }}"></script>

    <!-- Flatpickr -->
    <script src="{{ asset('assets/js/flatpickr.min.js') }}"></script>

    <!-- SweetAlert -->
    <script src="{{ asset('assets/js/sweetalert2@11.min.js') }}"></script>

    <!-- Common JS -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <!-- Search -->
    <script src="{{ asset('assets/js/search.js') }}"></script>

    <!-- DataTable -->
    <script src="{{ asset('assets/js/datatable.js') }}"></script>

    @stack('scripts')

</body>
</html>