<div class="bg-dark text-white p-3 d-flex flex-column" style="width:250px;">

    <h6 class="text-center mb-4 d-flex align-items-center justify-content-center">
        <img src="{{asset('assets/images/favicon.ico')}}" alt="Logo" width="32" height="32" class="me-2">
        Daily Activity Management System
    </h6>

    <hr>
        <div class="text-center mb-4">
            <div class="mb-2">
                <i class="bi bi-person-circle fs-1 text-info"></i>
            </div>
            <div class="mb-1 text-white">
                {{ ucwords(auth()->user()->name)}}
            </div>

            <span class="badge {{ auth()->user()->role == 0 ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ auth()->user()->role == 0 ? 'Management' : 'Uploader' }}
            </span>
        </div>
    </hr>

    <ul class="nav flex-column">

        @if(auth()->user()->role == 0)
            <li class="nav-item mb-2">

                <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}">             
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
        @endif


        @if(auth()->user()->role == 0 || auth()->user()->role == 1)
            <li class="nav-item mb-2">
                <a href="{{ route('uploader.index') }}" class="nav-link text-white {{ request()->routeIs('uploader.*') ? 'active bg-primary' : '' }}">           
                    <i class="bi bi-cloud-upload"></i>
                    Upload Complaint
                </a>
            </li>
        @endif

        @if(auth()->user()->role == 0)
            <li class="nav-item mb-2">

                <a href="{{ route('complaints.index') }}"
                    class="nav-link text-white {{ request()->routeIs('complaints.index') ? 'active bg-primary' : '' }}">

                    <i class="bi bi-file-earmark-text"></i>
                    Total Complaints
                </a>
            </li>

            <li class="nav-item mb-2">

                <a class="nav-link text-white d-flex justify-content-between align-items-center 
                {{request()->routeIs('user-configuration.*') || request()->routeIs('activity-configuration.*') || request()->routeIs('status-configuration.*') ? '' : 'collapsed'}}" 
                data-bs-toggle="collapse" href="#configureMenu" role="button" 
                    aria-expanded="{{request()->routeIs('user-configuration.*') || request()->routeIs('activity-configuration.*') || request()->routeIs('status-configuration.*') ? 'true' : 'false'}}" 
                    aria-controls="configureMenu">
                    <span>
                        <i class="bi bi-gear"></i>
                        Configuration
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </a>

                <div class="collapse {{request()->routeIs('user-configuration.*') || request()->routeIs('activity-configuration.*') || request()->routeIs('status-configuration.*') ? 'show' : ''}}" 
                    id="configureMenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item">
                            <a href="{{ route('user-configuration.index') }}"
                                class="nav-link text-white {{ request()->routeIs('user-configuration.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-person"></i>
                                User Configuration
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('activity-configuration.index') }}"
                            class="nav-link text-white {{ request()->routeIs('activity-configuration.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-list-task"></i>
                                Activity Type Configuration
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('status-configuration.index') }}"
                            class="nav-link text-white {{ request()->routeIs('status-configuration.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-check2-circle"></i>
                                Status Configuration
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a href="{{ route('audit.trail') }}" class="nav-link text-white {{ request()->routeIs('audit.trail') ? 'active bg-primary' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    Audit Trail
                </a>
            </li>
        @endif

        @if(auth()->user()->role == 0 || auth()->user()->role == 1)
            <li class="nav-item mb-2">
                <a class="nav-link text-white d-flex justify-content-between align-items-center
                    {{ request()->routeIs('designation.*')|| request()->routeIs('discipline.*')|| request()->routeIs('asset-type.*')|| request()->routeIs('asset-model.*')
                        || request()->routeIs('asset-tag.*')|| request()->routeIs('asset-inventory.*')|| request()->routeIs('custodian.*')
                        || request()->routeIs('asset-assignment.*')
                        ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#settingsMenu" role="button"

                    aria-expanded="{{ request()->routeIs('designation.*') || request()->routeIs('discipline.*')|| request()->routeIs('asset-type.*')                        
                        || request()->routeIs('asset-model.*')|| request()->routeIs('asset-tag.*')|| request()->routeIs('asset-inventory.*')                       
                        || request()->routeIs('custodian.*')|| request()->routeIs('asset-assignment.*') ? 'true' : 'false' }}" aria-controls="settingsMenu">

                    <span>
                        <i class="bi bi-sliders"></i>
                        Settings
                    </span>

                    <i class="bi bi-chevron-down"></i>
                </a>

                <div class="collapse
                    {{ request()->routeIs('designation.*')|| request()->routeIs('discipline.*')|| request()->routeIs('asset-type.*')|| request()->routeIs('asset-model.*')   
                        || request()->routeIs('asset-tag.*')|| request()->routeIs('asset-inventory.*')|| request()->routeIs('custodian.*')                       
                        || request()->routeIs('asset-assignment.*')? 'show' : '' }}" id="settingsMenu">
                    <ul class="nav flex-column ms-3 mt-2">

                        <li class="nav-item">
                            <a href="{{ route('designation.index') }}"
                                class="nav-link text-white {{ request()->routeIs('designation.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-person-badge"></i>
                                Designation
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{route('discipline.index')}}"
                                class="nav-link text-white {{ request()->routeIs('discipline.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-diagram-3"></i>
                                Discipline
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('asset-type.index')}}"
                                class="nav-link text-white {{ request()->routeIs('asset-type.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-box"></i>
                                Asset Type
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('asset-model.index')}}"
                                class="nav-link text-white {{ request()->routeIs('asset-model.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-cpu"></i>
                                Asset Model
                            </a>
                        </li>

                        <!-- <li class="nav-item">
                            <a href="{{ route('asset-tag.index')}}"
                                class="nav-link text-white {{ request()->routeIs('asset-tag.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-upc-scan"></i>
                                Asset Tag
                            </a>
                        </li> -->

                        <li class="nav-item">
                            <a href="{{ route('location.index')}}"
                                class="nav-link text-white {{ request()->routeIs('location.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-geo-alt"></i>
                                Location
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#"
                                class="nav-link text-white {{ request()->routeIs('custodian.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-person-workspace"></i>
                                Custodian
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#"
                                class="nav-link text-white {{ request()->routeIs('asset-assignment.*') ? 'active bg-primary' : '' }}">
                                <i class="bi bi-arrow-left-right"></i>
                                Asset Assignment
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if(auth()->user()->role == 0 || auth()->user()->role == 1)            
            <li class="nav-item">
                <a href="{{ route('asset-inventory.index') }}" class="nav-link text-white {{ request()->routeIs('asset-inventory') ? 'active bg-primary' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    Asset Inventory
                </a>
            </li>
        @endif
    </ul>

    <div class="mt-auto">
        <hr class="text-secondary">
        <form method="POST" action="{{ route('logout') }}"> 
            @csrf
            <button type="submit" class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-right"></i>
                    Logout
            </button>
        </form>
    </div>

</div>