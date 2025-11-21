<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable">


<head>

    <meta charset="utf-8" />
    <title>{{ isset($title_top) ? $title_top : 'POS APPS' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{ isset($title_top) ? $title_top : 'POS APPS' }}" name="description" />
    <meta content="Dodik Rismawan Affrudin" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('main_template/assets/images/favicon.ico') }}">

    <!-- Sweet Alert css-->
    <link href="{{ asset('main_template/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- gridjs css -->
    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/gridjs/theme/mermaid.min.css') }}">

    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/cdn.datatables.net/fixedheader/css/fixedColumns.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/cdn.datatables.net/fixedheader/css/fixedHeader.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('main_template/assets/libs/cdn.jsdelivr.net/npm/flatpicker/flatpicker.css') }}">

    <!-- jsvectormap css -->
    <link href="{{ asset('main_template/assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('main_template/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('main_template/assets/libs/cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Layout config Js -->
    <script src="{{ asset('main_template/assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('main_template/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('main_template/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('main_template/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('main_template/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('assets/css/template.css?v='.date('YmdHis')) }}" id="app-style" rel="stylesheet" type="text/css" />

    {{-- loader --}}
    <link rel="stylesheet" href="{{ asset('assets/css/loader/loader.css') }}">

    @if (isset($header_data))
        @php
            $version = str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz');
        @endphp
        @foreach ($header_data as $key => $v_head)
            @php
                $data_key = explode('-', $key);
            @endphp
            @if ($data_key[0] == 'css')
                <link rel="stylesheet" href="{{ $v_head }}?v={{ $version }}">
            @endif
        @endforeach
    @endif

</head>

<body>
    <div class="loader"></div>
    <!-- Begin page -->
    <div id="layout-wrapper">

        @include('web.template.header')

        @include('web.template.leftmenu')
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    {!! $view_file !!}

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            @include('web.template.footer')
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->

    <!--preloader-->
    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    {{-- Setting Template --}}
    {{-- <div class="customizer-setting d-none d-md-block">
        <div class="btn-info rounded-pill shadow-lg btn btn-icon btn-lg p-2 d-none" data-bs-toggle="offcanvas"
            data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
            <i class='mdi mdi-spin mdi-cog-outline fs-22'></i>
        </div>
    </div>

    @include('web.template.theme') --}}

    <!-- JAVASCRIPT -->
    <script src="{{ asset('main_template/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('main_template/assets/js/plugins.js') }}"></script>

    <!-- prismjs plugin -->
    <script src="{{ asset('main_template/assets/libs/prismjs/prism.js') }}"></script>

    <!-- gridjs js -->
    <script src="{{ asset('main_template/assets/libs/gridjs/gridjs.umd.js') }}"></script>

    <!--datatable js-->
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/fixedheader/js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdn.datatables.net/fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>


    <!-- apexcharts -->
    <script src="{{ asset('main_template/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('main_template/assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('main_template/assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!--Swiper slider js-->
    <script src="{{ asset('main_template/assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Dashboard init -->
    {{-- <script src="{{ asset('main_template/assets/js/pages/dashboard-ecommerce.init.js') }}"></script> --}}

    <!-- list.js min js -->
    <script src="{{ asset('main_template/assets/libs/list.js/list.min.js') }}"></script>

    <!--list pagination js-->
    <script src="{{ asset('main_template/assets/libs/list.pagination.js/list.pagination.min.js') }}"></script>

    <!-- Sweet Alerts js -->
    <script src="{{ asset('main_template/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- Sweet Alerts js -->
    <script src="{{ asset('main_template/assets/libs/cdn.jsdelivr.net/npm/flatpicker/flatpicker.js') }}"></script>

    <script src="{{ asset('main_template/assets/libs/cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js') }}"></script>

    <!-- Other JS -->
    <script src="{{ asset('main_template/assets/libs/bootbox/bootbox.js') }}"></script>
    <script src="{{ asset('assets/utils/url.js?v='.time()) }}"></script>
    <script src="{{ asset('assets/utils/message.js?v='.time()) }}"></script>
    <script src="{{ asset('assets/utils/validation.js?v='.time()) }}"></script>
    <script src="{{ asset('assets/js/lib/html2pdf.js') }}"></script>
    <script src="{{ asset('assets/js/xlsx/xlsx.min.js') }}"></script>
    <!-- Other JS -->


     <!-- App js -->
     <script src="{{ asset('main_template/assets/js/app.js') }}"></script>

    @if (isset($header_data))
        @php
            $version = str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz');
        @endphp
        @foreach ($header_data as $key => $v_head)
            @php
                $data_key = explode('-', $key);
            @endphp
            @if ($data_key[0] == 'js')
                <script src="{{ $v_head }}?v={{ $version }}"></script>
            @endif
        @endforeach
    @endif
</body>

</html>
