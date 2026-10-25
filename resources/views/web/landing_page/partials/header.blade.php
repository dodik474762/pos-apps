<header class="site-header" id="siteHeader">
    <div class="container header-inner">
        <a class="brand" href="{{ url('/landing') }}" aria-label="DMS — kembali ke beranda">
            <span class="brand-mark" aria-hidden="true">D</span>
            <span class="brand-copy">
                <strong>{{ $brand ?? 'DMS' }}</strong>
                <small>{{ $brandTagline ?? 'Distribution Management System' }}</small>
            </span>
        </a>

        <nav class="site-nav" id="siteNav" aria-label="Navigasi utama">
            @foreach (($nav ?? []) as $item)
                <a class="nav-link" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            @endforeach
            <a class="btn btn-primary btn-sm nav-cta" href="{{ url('/landing') }}#demo" data-track="nav_demo">
                Jadwalkan Demo
            </a>
        </nav>

        <button class="nav-toggle" id="navToggle" type="button" aria-expanded="false" aria-controls="siteNav" aria-label="Buka menu navigasi">
            <span class="nav-toggle-icon nav-toggle-open"><svg class="icon" aria-hidden="true"><use href="#i-menu"></use></svg></span>
            <span class="nav-toggle-icon nav-toggle-close"><svg class="icon" aria-hidden="true"><use href="#i-close"></use></svg></span>
        </button>
    </div>
</header>
