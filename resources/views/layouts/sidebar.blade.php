<div class="bg-dark text-white vh-100 p-3" style="width:250px;">

    <h6 class="text-center mb-4 d-flex align-items-center justify-content-center">
        <img src="{{asset('assets/images/favicon.ico')}}" alt="Logo" width="32" height="32" class="me-2">
        Complaint Management System
    </h6>

    <ul class="nav flex-column">

        <li class="nav-item">

            <a href="{{ route('uploader.index') }}"
            class="nav-link text-white {{ request()->routeIs('uploader.*') ? 'active bg-primary' : '' }}">

                <i class="bi bi-cloud-upload"></i>

                Upload Complaint

            </a>

        </li>

        <li class="nav-item mb-2">

            <a href="{{ route('dashboard') }}"
               class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}">

                <i class="bi bi-speedometer2"></i>

                Dashboard

            </a>

        </li>

        <li class="nav-item">

            <a href="{{ route('complaints.index') }}"
               class="nav-link text-white {{ request()->routeIs('complaints.index') ? 'active bg-primary' : '' }}">

                <i class="bi bi-file-earmark-text"></i>

                Total Complaints

            </a>

        </li>

    </ul>

</div>