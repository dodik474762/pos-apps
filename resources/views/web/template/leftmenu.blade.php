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
        <a href="{{ url('/dashboard') }}" class="logo logo-dark" style="">
            <span class="logo-sm">
                <img src="{{ asset('main_template/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg" style="margin-right:120px;">
                <img src="{{ asset('assets/images/logo-main-app.png') }}" alt="" height="50"
                    width="70">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ url('/dashboard') }}" class="logo logo-light"
            style="">
            <span class="logo-sm">
                <img src="{{ asset('main_template/assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg" style="margin-right:120px;">
                <img src="{{ asset('assets/images/logo-app-v2.png') }}" alt="" height="70" width="70">
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
