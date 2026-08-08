{{--
    Partial rekursif: 1 panel flyout untuk 1 node menu (beserta anak-anaknya sebagai kartu).
    Kalau sebuah kartu punya anak lagi (menu 3 level), kartu itu jadi tombol pembuka
    panel berikutnya (menyamping ke kanan), bukan link langsung.
--}}
@php $level = $level ?? 1; @endphp

<div class="accurate-panel" id="panel-{{ $node['id'] }}" data-level="{{ $level }}">
    <div class="accurate-panel-header">
        <h5 class="mb-0">{{ $node['nama'] }}</h5>
        <button type="button" class="acc-panel-close" data-close-panel="panel-{{ $node['id'] }}" title="Tutup">
            <i class="ri-close-line"></i>
        </button>
    </div>

    @if (!empty($node['children']) && count($node['children']) > 6)
        <div class="accurate-panel-search">
            <i class="ri-search-line acc-search-icon"></i>
            <input type="text" class="acc-search-input" placeholder="Cari menu...">
            <button type="button" class="acc-search-clear" title="Bersihkan">
                <i class="ri-close-circle-fill"></i>
            </button>
        </div>
    @endif

    <div class="accurate-panel-grid">
        @forelse ($node['children'] as $child)
            @php
                $hasChild = !empty($child['children']);
                $label = !empty($child['menu_alias']) ? $child['menu_alias'] : $child['nama'];
                $url = trim($child['url']) == '' || trim($child['url']) == '-' ? '#' : url($child['url']);
                $color = $child['color'] ?? 'secondary';
            @endphp

            @if ($hasChild)
                <button type="button" class="acc-card acc-{{ $color }} acc-card-parent"
                    data-target="panel-{{ $child['id'] }}" data-search="{{ strtolower($label) }}">
                    <span class="acc-card-icon"><i class="{{ $child['icon'] }}"></i></span>
                    <span class="acc-card-label">{{ $label }}</span>
                    <i class="ri-arrow-right-s-line acc-card-chevron"></i>
                </button>
            @else
                <a href="{{ $url }}" class="acc-card acc-{{ $color }}"
                    data-search="{{ strtolower($label) }}">
                    <span class="acc-card-icon"><i class="{{ $child['icon'] }}"></i></span>
                    <span class="acc-card-label">{{ $label }}</span>
                </a>
            @endif
        @empty
            <div class="acc-empty">Belum ada menu.</div>
        @endforelse

        <div class="acc-empty acc-search-empty" style="display:none;">
            Menu tidak ditemukan.
        </div>
    </div>
</div>

@foreach ($node['children'] as $child)
    @if (!empty($child['children']))
        @include('web.template.partials.accurate-panel', ['node' => $child, 'level' => $level + 1])
    @endif
@endforeach
