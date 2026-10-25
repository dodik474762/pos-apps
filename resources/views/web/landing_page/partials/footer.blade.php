<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand" href="{{ url('/landing') }}" aria-label="DMS — kembali ke beranda">
                <span class="brand-mark" aria-hidden="true">D</span>
                <span class="brand-copy">
                    <strong>{{ $brand ?? 'DMS' }}</strong>
                    <small>{{ $brandTagline ?? 'Distribution Management System' }}</small>
                </span>
            </a>
            <p>Membantu distributor Indonesia mengelola order, stok, salesman, piutang, dan pengiriman dari satu dashboard.</p>
            <div class="footer-contact">
                <a href="https://wa.me/{{ $whatsappNumber ?? '6281234567890' }}" target="_blank" rel="noopener">
                    <svg class="icon" aria-hidden="true"><use href="#i-whatsapp"></use></svg>
                    WhatsApp
                </a>
                <a href="mailto:{{ $contactEmail ?? 'hello@dms.id' }}">
                    <svg class="icon" aria-hidden="true"><use href="#i-mail"></use></svg>
                    {{ $contactEmail ?? 'hello@dms.id' }}
                </a>
            </div>
        </div>

        <div class="footer-col">
            <h3>Produk</h3>
            <a href="{{ url('/landing') }}#fitur">Fitur</a>
            <a href="{{ url('/landing') }}#harga">Harga</a>
            <a href="{{ url('/landing') }}#studi-kasus">Studi kasus</a>
            <a href="{{ url('/landing') }}#faq">FAQ</a>
        </div>

        <div class="footer-col">
            <h3>Perusahaan</h3>
            <a href="{{ route('landing.privacy') }}">Kebijakan privasi</a>
            <a href="{{ route('landing.terms') }}">Syarat layanan</a>
            <a href="{{ url('/landing') }}#demo">Hubungi sales</a>
        </div>

        <div class="footer-col footer-hours">
            <h3>Jam operasional</h3>
            <p>Senin–Jumat<br>09.00–17.00 WIB</p>
            <p class="footer-note">Respons WhatsApp biasanya di bawah 15 menit pada jam kerja.</p>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; {{ date('Y') }} {{ $brand ?? 'DMS' }}. Seluruh hak cipta dilindungi.</p>
        <p>Dibuat untuk distributor yang ingin tumbuh lebih rapi.</p>
    </div>
</footer>
