@push('head')
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@include('web.landing_page.partials.head')

@include('web.landing_page.partials.header')

<main id="main">
    <section class="hero" id="top">
        <div class="hero-bg" aria-hidden="true">
            <span class="orb orb-one"></span>
            <span class="orb orb-two"></span>
            <span class="orb orb-three"></span>
            <span class="hero-grid-lines"></span>
        </div>

        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <span class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    SaaS distribusi untuk tim yang bergerak cepat
                </span>
                <h1>Kelola distribusi dari <span class="text-gradient">order sampai penagihan</span> dalam satu dashboard.</h1>
                <p class="hero-sub">
                    DMS menyatukan order, stok multi-gudang, salesman lapangan, piutang, dan pengiriman.
                    Berhenti menyalin data antar file dan mulai ambil keputusan dari satu sumber angka.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="#demo" data-track="hero_demo">
                        Jadwalkan Demo Gratis
                        <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                    </a>
                    <a class="btn btn-outline btn-lg" href="#harga" data-track="hero_pricing">Lihat Harga</a>
                </div>
                <p class="hero-note">Konsultasi 30 menit · Tanpa kartu kredit · Setup dibantu tim kami</p>
                <ul class="trust-list">
                    @foreach ($trustPoints as $point)
                        <li>
                            <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="hero-visual reveal" aria-label="Pratinjau dashboard DMS">
                <div class="mockup">
                    <div class="mockup-top">
                        <span class="mockup-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span class="mockup-url">app.dms.id/dashboard</span>
                        <span class="mockup-live"><i></i>Live</span>
                    </div>
                    <div class="mockup-body">
                        <aside class="mock-sidebar" aria-hidden="true">
                            <span class="mock-logo">D</span>
                            <span class="mock-nav is-active"></span>
                            <span class="mock-nav"></span>
                            <span class="mock-nav"></span>
                            <span class="mock-nav"></span>
                            <span class="mock-nav mock-nav-bottom"></span>
                        </aside>
                        <div class="mock-main">
                            <div class="mock-head">
                                <div>
                                    <small>Dashboard</small>
                                    <strong>Ringkasan hari ini</strong>
                                </div>
                                <span class="mock-avatar">RH</span>
                            </div>

                            <div class="kpi-row">
                                <div class="kpi">
                                    <span>Omzet bulan ini</span>
                                    <strong>Rp 1,28 M</strong>
                                    <em class="up">+12,4%</em>
                                </div>
                                <div class="kpi">
                                    <span>Piutang berjalan</span>
                                    <strong>Rp 312 jt</strong>
                                    <em class="down">-18,2%</em>
                                </div>
                                <div class="kpi">
                                    <span>Order hari ini</span>
                                    <strong>148</strong>
                                    <em class="up">+8,1%</em>
                                </div>
                            </div>

                            <div class="mock-panels">
                                <div class="chart-card">
                                    <div class="chart-head">
                                        <span>Penjualan 7 hari</span>
                                        <span class="mini-chip">Mingguan</span>
                                    </div>
                                    <div class="chart-bars" aria-hidden="true">
                                        @foreach ([38, 52, 44, 68, 58, 82, 74] as $height)
                                            <span class="bar" style="--h: {{ $height }}%"></span>
                                        @endforeach
                                    </div>
                                    <div class="chart-labels" aria-hidden="true">
                                        <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                                    </div>
                                </div>

                                <div class="aging-card">
                                    <span class="aging-title">Aging piutang</span>
                                    <div class="donut">
                                        <span>Rp 312 jt</span>
                                    </div>
                                    <ul>
                                        <li><i class="dot dot-one"></i>Lancar <b>58%</b></li>
                                        <li><i class="dot dot-two"></i>1–30 hari <b>27%</b></li>
                                        <li><i class="dot dot-three"></i>&gt; 30 hari <b>15%</b></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mock-table">
                                <div class="mock-table-head">
                                    <span>Order terbaru</span>
                                    <span>Lihat semua</span>
                                </div>
                                <div class="mock-row">
                                    <span class="mock-doc">INV-0921</span>
                                    <span>Toko Sinar Jaya</span>
                                    <span class="status status-ok">Lunas</span>
                                    <strong>Rp 4,2 jt</strong>
                                </div>
                                <div class="mock-row">
                                    <span class="mock-doc">INV-0920</span>
                                    <span>CV Mitra Abadi</span>
                                    <span class="status status-wait">Proses</span>
                                    <strong>Rp 7,8 jt</strong>
                                </div>
                                <div class="mock-row">
                                    <span class="mock-doc">INV-0919</span>
                                    <span>UD Berkah Jaya</span>
                                    <span class="status status-ok">Lunas</span>
                                    <strong>Rp 2,6 jt</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="float-card float-stock">
                        <span class="float-icon"><svg class="icon" aria-hidden="true"><use href="#i-inventory"></use></svg></span>
                        <span><strong>Stok akurat 99,2%</strong><small>8 gudang tersinkron</small></span>
                    </div>
                    <div class="float-card float-receivable">
                        <span class="float-icon float-icon-green"><svg class="icon" aria-hidden="true"><use href="#i-receivable"></use></svg></span>
                        <span><strong>Piutang turun 18%</strong><small>vs bulan lalu</small></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="hero-stats reveal">
                @foreach ($metrics as $metric)
                    <div class="hero-stat">
                        <strong>{{ $metric['value'] }}</strong>
                        <span>{{ $metric['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="industry-strip" aria-label="Industri yang dilayani">
        <div class="container industry-inner">
            <p>Dipakai tim distribusi di berbagai industri</p>
            <div class="industry-list">
                @foreach ($industries as $industry)
                    <span>{{ $industry }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section problem-section" id="masalah">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Masih begini?</span>
                <h2>Operasional jalan, tapi datanya tidak pernah satu suara.</h2>
                <p>Semakin besar jaringan distribusi, semakin mahal biaya dari proses manual yang tidak terlihat.</p>
            </div>

            <div class="problem-grid">
                @foreach ($problems as $problem)
                    <article class="problem-card reveal">
                        <span class="icon-badge icon-badge-warn">
                            <svg class="icon" aria-hidden="true"><use href="#i-{{ $problem['icon'] }}"></use></svg>
                        </span>
                        <span class="problem-tag">{{ $problem['tag'] }}</span>
                        <h3>{{ $problem['title'] }}</h3>
                        <p>{{ $problem['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section feature-section" id="fitur">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Fitur</span>
                <h2>Satu platform untuk seluruh alur distribusi.</h2>
                <p>Dari sales mengambil order di toko sampai finance menagih piutang, semua tercatat di alur yang sama.</p>
            </div>

            <div class="feature-grid">
                @foreach ($features as $feature)
                    <article class="feature-card reveal {{ $loop->last ? 'feature-card-wide' : '' }}">
                        <span class="icon-badge">
                            <svg class="icon" aria-hidden="true"><use href="#i-{{ $feature['icon'] }}"></use></svg>
                        </span>
                        <div class="feature-content">
                            <h3>{{ $feature['title'] }}</h3>
                            <p>{{ $feature['description'] }}</p>
                            <ul class="check-list">
                                @foreach ($feature['points'] as $point)
                                    <li>
                                        <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
                                        {{ $point }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section steps-section">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Mulai tanpa drama</span>
                <h2>Dari data Excel ke sistem yang rapi dalam hitungan hari.</h2>
                <p>Tim kami mendampingi proses pindah sistem agar operasional tetap berjalan.</p>
            </div>

            <div class="steps">
                @foreach ($steps as $step)
                    <article class="step reveal">
                        <span class="step-number">0{{ $loop->iteration }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section benefits-section" id="manfaat">
        <div class="container">
            <div class="benefit-panel reveal">
                <div class="benefit-copy">
                    <span class="eyebrow eyebrow-light">Hasil yang terukur</span>
                    <h2>Lebih sedikit rekap manual. Lebih banyak waktu untuk bertumbuh.</h2>
                    <p>
                        Tim admin tidak lagi tenggelam di spreadsheet, sales punya panduan kerja yang jelas,
                        dan owner melihat kondisi bisnis tanpa menunggu laporan akhir bulan.
                    </p>
                    <ul class="benefit-list">
                        <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Order masuk langsung dari lapangan</li>
                        <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Stok dan piutang terpantau real-time</li>
                        <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Keputusan berbasis data, bukan feeling</li>
                    </ul>
                    <a class="btn btn-light" href="#demo" data-track="benefit_demo">
                        Lihat dampaknya untuk bisnis Anda
                        <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                    </a>
                </div>

                <div class="benefit-grid">
                    @foreach ($benefits as $benefit)
                        <div class="benefit-card">
                            <strong>{{ $benefit['value'] }}</strong>
                            <span>{{ $benefit['label'] }}</span>
                            <small>{{ $benefit['description'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="section pricing-section" id="harga">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Harga</span>
                <h2>Paket yang tumbuh bersama bisnis Anda.</h2>
                <p>Mulai dari kebutuhan dasar, lalu naikkan kapasitas saat jaringan distribusi bertambah.</p>
            </div>

            <div class="billing-row reveal">
                <div class="billing-toggle" role="group" aria-label="Pilih periode tagihan">
                    <button class="billing-btn is-active" type="button" data-billing="monthly" aria-pressed="true">Bulanan</button>
                    <button class="billing-btn" type="button" data-billing="annual" aria-pressed="false">
                        Tahunan <span>Hemat 15%</span>
                    </button>
                </div>
            </div>

            <div class="plan-grid">
                @foreach ($plans as $plan)
                    <article class="plan-card reveal {{ $plan['popular'] ? 'is-popular' : '' }}">
                        @if ($plan['popular'])
                            <span class="plan-badge">Paling populer</span>
                        @endif
                        <header class="plan-head">
                            <h3>{{ $plan['name'] }}</h3>
                            <p>{{ $plan['tagline'] }}</p>
                        </header>
                        <div class="plan-price">
                            <span
                                class="price"
                                data-price
                                data-monthly="{{ $plan['monthly'] }}"
                                data-annual="{{ $plan['annual'] }}"
                            >{{ $plan['monthly'] }}</span>
                            <span
                                class="period"
                                data-period
                                data-monthly="{{ str_contains($plan['monthly'], 'Rp') ? '/bulan' : '' }}"
                                data-annual="{{ str_contains($plan['annual'], 'Rp') ? '/bulan' : '' }}"
                            >{{ str_contains($plan['monthly'], 'Rp') ? '/bulan' : '' }}</span>
                        </div>
                        <p
                            class="plan-note"
                            data-plan-note
                            data-monthly="{{ $plan['noteMonthly'] }}"
                            data-annual="{{ $plan['noteAnnual'] }}"
                        >{{ $plan['noteMonthly'] }}</p>
                        <a
                            class="btn {{ $plan['popular'] ? 'btn-primary' : 'btn-outline' }} btn-block"
                            href="#demo"
                            data-track="plan_{{ strtolower($plan['name']) }}"
                            data-plan="{{ $plan['name'] }}"
                        >{{ $plan['cta'] }}</a>
                        <ul class="plan-features">
                            @foreach ($plan['features'] as $feature)
                                <li>
                                    <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>

            <p class="pricing-note reveal">
                Harga di atas adalah ilustrasi dan dapat disesuaikan dengan jumlah user serta kebutuhan implementasi.
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" data-track="pricing_whatsapp">Bantu pilih paket via WhatsApp</a>.
            </p>
        </div>
    </section>

    <section class="section testimonial-section" id="studi-kasus">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Studi kasus</span>
                <h2>Cerita tim yang sudah meninggalkan cara manual.</h2>
                <p>Hasil nyata dari distributor yang memindahkan operasional harian ke DMS.</p>
            </div>

            <div class="testimonial-grid">
                @foreach ($testimonials as $testimonial)
                    <article class="quote-card reveal">
                        <div class="quote-stars" aria-label="Rating 5 dari 5">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="icon" aria-hidden="true"><use href="#i-star"></use></svg>
                            @endfor
                        </div>
                        <blockquote>&ldquo;{{ $testimonial['quote'] }}&rdquo;</blockquote>
                        <span class="quote-result">{{ $testimonial['result'] }}</span>
                        <div class="quote-person">
                            <span class="quote-avatar" aria-hidden="true">{{ mb_substr($testimonial['name'], 0, 1) }}</span>
                            <span>
                                <strong>{{ $testimonial['name'] }}</strong>
                                <small>{{ $testimonial['role'] }} · {{ $testimonial['company'] }}</small>
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section roi-section" id="kalkulator">
        <div class="container roi-grid">
            <div class="roi-copy reveal">
                <span class="eyebrow">Kalkulator cepat</span>
                <h2>Berapa nilai waktu yang bisa Anda kembalikan?</h2>
                <p>
                    Hitung gambaran kasar penghematan dari pengurangan rekap manual dan penagihan yang lebih cepat.
                    Angka ini adalah estimasi ilustratif, bukan janji hasil.
                </p>
                <ul class="check-list">
                    <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Tanpa perlu data sensitif</li>
                    <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Hasil muncul langsung saat Anda mengubah angka</li>
                    <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Gunakan sebagai bahan diskusi dengan tim</li>
                </ul>
            </div>

            <div class="roi-card reveal">
                <div class="field">
                    <label for="roiRevenue">Omzet bulanan (juta rupiah)</label>
                    <input id="roiRevenue" type="number" inputmode="numeric" min="0" step="10" value="500">
                </div>
                <div class="field">
                    <label for="roiHours">Jam admin untuk rekap per minggu</label>
                    <input id="roiHours" type="number" inputmode="numeric" min="0" step="1" value="20">
                </div>
                <div class="roi-result" aria-live="polite">
                    <span>Estimasi nilai waktu yang kembali</span>
                    <strong id="roiMonthly">Rp 3,9 jt / bulan</strong>
                    <small id="roiYearly">Rp 46,8 jt / tahun</small>
                </div>
                <a class="btn btn-primary btn-block" href="#demo" data-track="roi_demo">Konsultasikan Hasilnya</a>
                <p class="roi-note">Asumsi: nilai jam admin Rp 30.000 dan potensi piutang tertagih lebih cepat 0,3% dari omzet.</p>
            </div>
        </div>
    </section>

    <section class="section faq-section" id="faq">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">FAQ</span>
                <h2>Hal yang biasanya ditanyakan distributor.</h2>
                <p>Belum menemukan jawaban? Tim kami siap membantu lewat WhatsApp.</p>
            </div>

            <div class="faq-list">
                @foreach ($faqs as $faq)
                    <details class="faq-item reveal">
                        <summary>
                            <span>{{ $faq['question'] }}</span>
                            <svg class="icon" aria-hidden="true"><use href="#i-chevron"></use></svg>
                        </summary>
                        <div class="faq-answer">
                            <p>{{ $faq['answer'] }}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section cta-section" id="demo">
        <div class="container">
            <div class="cta-panel">
                <div class="cta-copy reveal">
                    <span class="eyebrow eyebrow-light">Mulai sekarang</span>
                    <h2>Jadwalkan demo gratis dan lihat alur distribusi Anda di DMS.</h2>
                    <p>
                        Kami akan menyesuaikan demo dengan proses bisnis Anda, bukan sekadar menunjukkan fitur.
                        Setelah sesi, Anda mendapatkan rekomendasi paket dan langkah implementasi.
                    </p>
                    <ul class="cta-points">
                        <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Demo 30 menit sesuai alur bisnis</li>
                        <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Rekomendasi paket dan estimasi biaya</li>
                        <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> Tanpa komitmen dan tanpa kartu kredit</li>
                    </ul>

                    <div class="cta-contact">
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" data-track="whatsapp_cta">
                            <svg class="icon" aria-hidden="true"><use href="#i-whatsapp"></use></svg>
                            WhatsApp tim kami
                        </a>
                        <a href="mailto:{{ $contactEmail }}">
                            <svg class="icon" aria-hidden="true"><use href="#i-mail"></use></svg>
                            {{ $contactEmail }}
                        </a>
                    </div>
                    <p class="cta-hours">
                        <svg class="icon" aria-hidden="true"><use href="#i-clock"></use></svg>
                        Senin–Jumat, 09.00–17.00 WIB
                    </p>
                </div>

                <div class="lead-form-wrap reveal">
                    <form class="lead-form" id="leadForm" data-whatsapp="{{ $whatsappNumber }}">
                        <div class="form-head">
                            <h3>Minta demo gratis</h3>
                            <p>Isi data singkat, tim kami akan menghubungi Anda.</p>
                        </div>

                        <input type="hidden" name="plan" id="leadPlan" value="">

                        <div class="form-grid">
                            <div class="field">
                                <label for="leadName">Nama lengkap <span>*</span></label>
                                <input id="leadName" name="name" type="text" required autocomplete="name" placeholder="Nama Anda">
                            </div>
                            <div class="field">
                                <label for="leadCompany">Nama perusahaan <span>*</span></label>
                                <input id="leadCompany" name="company" type="text" required autocomplete="organization" placeholder="PT / CV ...">
                            </div>
                            <div class="field">
                                <label for="leadWhatsapp">Nomor WhatsApp <span>*</span></label>
                                <input id="leadWhatsapp" name="whatsapp" type="tel" inputmode="tel" required autocomplete="tel" placeholder="08xx xxxx xxxx">
                                <small class="field-hint">Aktif untuk dihubungi via WhatsApp</small>
                            </div>
                            <div class="field">
                                <label for="leadIndustry">Jenis usaha <span>*</span></label>
                                <select id="leadIndustry" name="industry" required>
                                    <option value="" selected disabled>Pilih industri</option>
                                    @foreach ($industries as $industry)
                                        <option value="{{ $industry }}">{{ $industry }}</option>
                                    @endforeach
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="leadSales">Jumlah sales <span>*</span></label>
                                <select id="leadSales" name="sales" required>
                                    <option value="" selected disabled>Pilih jumlah</option>
                                    <option value="1-5">1–5 sales</option>
                                    <option value="6-15">6–15 sales</option>
                                    <option value="16-50">16–50 sales</option>
                                    <option value="50+">Lebih dari 50 sales</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="leadMessage">Kebutuhan (opsional)</label>
                                <input id="leadMessage" name="message" type="text" placeholder="Contoh: integrasi akuntansi">
                            </div>
                        </div>

                        <button class="btn btn-primary btn-block btn-lg" type="submit">
                            Kirim via WhatsApp
                            <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                        </button>
                        <p class="form-privacy">
                            Dengan mengirim, Anda menyetujui
                            <a href="{{ route('landing.privacy') }}">Kebijakan Privasi</a>. Tidak ada spam.
                        </p>
                        <div class="form-status" id="formStatus" role="status" aria-live="polite"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

@include('web.landing_page.partials.footer')
@include('web.landing_page.partials.scripts')
</body>
</html>
