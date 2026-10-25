@include('web.landing_page.partials.head')

@include('web.landing_page.partials.header')

<main class="legal-main" id="main">
    <div class="container legal-layout">
        <a class="back-link" href="{{ url('/landing') }}">
            <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
            Kembali ke beranda
        </a>
        <span class="eyebrow">Legal</span>
        <h1>{{ $legalTitle }}</h1>
        <p class="legal-updated">{{ $legalUpdated }}</p>

        <div class="legal-content">
            @foreach ($legalSections as $section)
                <section>
                    <h2>{{ $section['heading'] }}</h2>
                    @foreach ($section['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </section>
            @endforeach
        </div>

        <div class="legal-callout">
            <h2>Butuh klarifikasi?</h2>
            <p>Hubungi tim kami melalui email atau WhatsApp. Kami akan membantu menjawab pertanyaan Anda.</p>
            <div class="legal-actions">
                <a class="btn btn-primary" href="mailto:{{ $contactEmail }}">Kirim email</a>
                <a class="btn btn-outline" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Chat WhatsApp</a>
            </div>
        </div>
    </div>
</main>

@include('web.landing_page.partials.footer')
@include('web.landing_page.partials.scripts')
</body>
</html>
