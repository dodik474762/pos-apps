@php
    use App\Http\Controllers\api\template\TemplateMenuController;
@endphp
<!-- ========== Left Sidebar Start ========== -->
{{-- <div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu">Menu</li>

                @php
                    $apiMenu = new TemplateMenuController();
                @endphp
                {!! $apiMenu->generateMenuWeb() !!}
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End --> --}}

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ url('/dashboard') }}" class="logo logo-dark" style="background-color: white;width: 100%;border-radius: 3px;padding: 8px;box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
            <span class="logo-sm">
                <img src="{{ asset('main_template/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo_dpi.png') }}" alt="" height="50"
                    width="150">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ url('/dashboard') }}" class="logo logo-light"
            style="background-color: white;width: 100%;border-radius: 3px;padding: 8px;box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
            <span class="logo-sm">
                <img src="{{ asset('main_template/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo_dpi.png') }}" alt="" height="50" width="150">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                @php
                    $apiMenu = new TemplateMenuController();
                @endphp
                {!! $apiMenu->generateMenuWeb() !!}
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
