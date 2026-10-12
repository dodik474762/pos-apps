@php
    use App\Http\Controllers\api\template\TemplateMenuController;

    $apiMenu = new TemplateMenuController();
    $menuTree = $apiMenu->getAccurateMenuTree();
@endphp

<div class="accurate-sidebar" id="accurateSidebar">

    {{-- ===== Rail icon di paling kiri ===== --}}
    <div class="accurate-rail">
        <div class="accurate-rail-logo">
            <a href="{{ url('/dashboard') }}">
                <img src="{{ asset('main_template/assets/images/favicon.ico') }}" alt="logo" class="rail-logo-sm">
            </a>
        </div>

        <div class="accurate-rail-items">
            @foreach ($menuTree as $menu)
                @php
                    $menuUrl = trim($menu['url'] ?? '', '/');
                    $active = $menuUrl !== '' && $menuUrl !== '-' && request()->is($menuUrl . '*') ? 'active' : '';
                @endphp
                <div class="rail-item {{ $active }}" data-target="panel-{{ $menu['id'] }}"
                    title="{{ $menu['nama'] }}">
                    <i class="{{ $menu['icon'] }}"></i>
                    <span class="rail-item-label">{{ $menu['nama'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== Overlay + kumpulan panel flyout ===== --}}
    <div class="accurate-panel-overlay" id="accuratePanelOverlay"></div>

    <div class="accurate-panel-wrapper" id="accuratePanelWrapper">
        @foreach ($menuTree as $menu)
            @include('web.template.cardmenu', ['node' => $menu, 'level' => 1])
        @endforeach
    </div>
</div>

<script src="{{ asset('assets/js/menu-card.js?v=' . date('YmdHis')) }}"></script>
