<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;

class LandingPageController extends Controller
{
    private const WHATSAPP_NUMBER = '6281234567890';
    private const CONTACT_EMAIL = 'hello@dms.id';

    public function index()
    {
        $whatsappMessage = rawurlencode('Halo DMS, saya ingin menjadwalkan demo gratis untuk distributor saya.');

        $data = [
            'pageTitle' => 'DMS — Software Distribusi dari Order sampai Penagihan',
            'pageDescription' => 'Platform SaaS untuk distributor FMCG, farmasi, sparepart, dan bahan bangunan. Kelola order, stok multi-gudang, salesman lapangan, piutang, dan pengiriman dalam satu dashboard.',
            'pageKeywords' => 'aplikasi distributor, software distribusi, sistem manajemen distribusi, aplikasi salesman, stok multi gudang, piutang distributor',
            'canonical' => url('/landing'),
            'whatsappNumber' => self::WHATSAPP_NUMBER,
            'whatsappUrl' => 'https://wa.me/' . self::WHATSAPP_NUMBER . '?text=' . $whatsappMessage,
            'contactEmail' => self::CONTACT_EMAIL,
            'brand' => 'DMS',
            'brandTagline' => 'Distribution Management System',
            'nav' => [
                ['label' => 'Fitur', 'href' => '#fitur'],
                ['label' => 'Harga', 'href' => '#harga'],
                ['label' => 'Studi Kasus', 'href' => '#studi-kasus'],
                ['label' => 'FAQ', 'href' => '#faq'],
            ],
            'trustPoints' => [
                'Data bisa diimpor dari Excel',
                'Training gratis untuk tim',
                'Tanpa biaya setup',
                'Batalkan kapan saja',
            ],
            'industries' => [
                'FMCG',
                'Farmasi',
                'Sparepart',
                'Bahan Bangunan',
                'Consumer Goods',
                'Distribusi Umum',
            ],
            'metrics' => [
                ['value' => '200+', 'label' => 'distributor aktif'],
                ['value' => '1 juta+', 'label' => 'transaksi per bulan'],
                ['value' => '99,9%', 'label' => 'target uptime'],
                ['value' => '4,9/5', 'label' => 'kepuasan pengguna'],
            ],
            'problems' => [
                [
                    'icon' => 'excel',
                    'title' => 'Order masih lewat Excel dan WhatsApp',
                    'description' => 'Pesanan tersebar di banyak file dan chat. Admin harus menyalin ulang, rawan salah harga, salah qty, dan salah toko.',
                    'tag' => 'Rawan salah input',
                ],
                [
                    'icon' => 'chat',
                    'title' => 'Stok tidak pernah satu suara',
                    'description' => 'Gudang, sales, dan finance memakai angka stok yang berbeda. Barang baru diketahui kosong saat sudah dijanjikan ke toko.',
                    'tag' => 'Stok tidak sinkron',
                ],
                [
                    'icon' => 'alert',
                    'title' => 'Piutang sulit dilacak',
                    'description' => 'Aging piutang dibuat manual, penagihan telat, dan limit kredit toko tidak terkontrol. Cashflow ikut terganggu.',
                    'tag' => 'Penagihan telat',
                ],
                [
                    'icon' => 'sales',
                    'title' => 'Kunjungan sales tanpa catatan',
                    'description' => 'Rute kunjungan tidak terukur, order kanvas tidak tercatat, dan performa sales sulit dievaluasi secara objektif.',
                    'tag' => 'Aktivitas tidak terukur',
                ],
            ],
            'features' => [
                [
                    'icon' => 'order',
                    'title' => 'Order management',
                    'description' => 'Terima order dari web, mobile, dan portal toko B2B dalam satu alur yang rapi.',
                    'points' => ['Approval order berjenjang', 'Harga per channel & customer', 'Riwayat order dan cetak dokumen'],
                ],
                [
                    'icon' => 'inventory',
                    'title' => 'Inventory multi-gudang',
                    'description' => 'Pantau stok real-time per gudang, batch, dan tanggal kedaluwarsa tanpa rekonsiliasi manual.',
                    'points' => ['Stok real-time per gudang', 'Batch & expired date', 'Mutasi stok dan adjustment'],
                ],
                [
                    'icon' => 'sales',
                    'title' => 'Salesman app',
                    'description' => 'Bekali sales dengan katalog, stok, harga, rute kunjungan, dan check-in GPS di mobile.',
                    'points' => ['Rute dan PJP harian', 'Take order dari toko', 'GPS check-in & foto kunjungan'],
                ],
                [
                    'icon' => 'receivable',
                    'title' => 'Piutang & penagihan',
                    'description' => 'Kontrol limit kredit, aging piutang, dan jadwal penagihan dalam satu tampilan.',
                    'points' => ['Aging piutang otomatis', 'Limit kredit & TOP', 'Reminder penagihan'],
                ],
                [
                    'icon' => 'delivery',
                    'title' => 'Delivery & armada',
                    'description' => 'Susun surat jalan, pantau pengiriman, dan simpan bukti terima secara digital.',
                    'points' => ['Surat jalan & packing list', 'Tracking status kirim', 'Bukti terima digital'],
                ],
                [
                    'icon' => 'report',
                    'title' => 'Laporan & analitik',
                    'description' => 'Lihat omzet per sales, produk, area, dan toko untuk keputusan yang lebih cepat.',
                    'points' => ['Omzet per sales & area', 'Produk terlaris', 'Dashboard owner real-time'],
                ],
                [
                    'icon' => 'integration',
                    'title' => 'Integrasi',
                    'description' => 'Hubungkan operasional distribusi dengan akuntansi, e-Faktur, WhatsApp, dan marketplace.',
                    'points' => ['Akuntansi & jurnal', 'e-Faktur dan WhatsApp', 'API untuk ERP & marketplace'],
                ],
            ],
            'steps' => [
                [
                    'title' => 'Daftar & konsultasi',
                    'description' => 'Ceritakan alur bisnis Anda. Tim kami bantu memetakan kebutuhan dan paket yang paling pas.',
                ],
                [
                    'title' => 'Import data',
                    'description' => 'Pindahkan master produk, customer, stok awal, dan piutang dari Excel dengan pendampingan.',
                ],
                [
                    'title' => 'Mulai jualan',
                    'description' => 'Tim dilatih, sistem siap dipakai, dan operasional berjalan dari satu dashboard.',
                ],
            ],
            'benefits' => [
                ['value' => '70%', 'label' => 'lebih cepat input order', 'description' => 'Order dari sales langsung masuk tanpa salin ulang.'],
                ['value' => '90%', 'label' => 'selisih stok turun', 'description' => 'Stok per gudang dan mutasi tercatat otomatis.'],
                ['value' => '2x', 'label' => 'kunjungan lebih terukur', 'description' => 'Rute, check-in, dan hasil kunjungan terlihat jelas.'],
                ['value' => 'H+1', 'label' => 'laporan siap pagi', 'description' => 'Omzet, piutang, dan stok tersedia tanpa rekap manual.'],
            ],
            'plans' => [
                [
                    'name' => 'Starter',
                    'tagline' => 'Untuk distributor kecil yang baru beralih dari cara manual.',
                    'monthly' => 'Rp 499rb',
                    'annual' => 'Rp 425rb',
                    'noteMonthly' => 'Ditagih bulanan',
                    'noteAnnual' => 'Ditagih tahunan, hemat 15%',
                    'popular' => false,
                    'cta' => 'Coba Gratis 14 Hari',
                    'features' => [
                        's.d. 5 user',
                        '1 gudang',
                        'Order management dasar',
                        'Salesman app basic',
                        'Laporan penjualan & stok',
                        'Support via email',
                    ],
                ],
                [
                    'name' => 'Growth',
                    'tagline' => 'Untuk distributor berkembang yang butuh kontrol lebih lengkap.',
                    'monthly' => 'Rp 1,29jt',
                    'annual' => 'Rp 1,1jt',
                    'noteMonthly' => 'Ditagih bulanan',
                    'noteAnnual' => 'Ditagih tahunan, hemat 15%',
                    'popular' => true,
                    'cta' => 'Jadwalkan Demo',
                    'features' => [
                        's.d. 25 user',
                        '3 gudang',
                        'Salesman app + GPS',
                        'Piutang & limit kredit',
                        'Delivery & surat jalan',
                        'Integrasi akuntansi & WhatsApp',
                        'Support chat prioritas',
                    ],
                ],
                [
                    'name' => 'Enterprise',
                    'tagline' => 'Untuk distributor besar dan multi-cabang dengan kebutuhan khusus.',
                    'monthly' => 'Hubungi sales',
                    'annual' => 'Hubungi sales',
                    'noteMonthly' => 'Harga disesuaikan kebutuhan',
                    'noteAnnual' => 'Kontrak tahunan & SLA',
                    'popular' => false,
                    'cta' => 'Konsultasi Kebutuhan',
                    'features' => [
                        'User unlimited',
                        'Gudang unlimited',
                        'Salesman app + custom',
                        'API, ERP, dan SSO',
                        'Multi-cabang & multi-tenant',
                        'Dedicated account manager',
                        'SLA & onboarding khusus',
                    ],
                ],
            ],
            'testimonials' => [
                [
                    'quote' => 'Dulu closing order jam 5 sore, sekarang sales input langsung dari toko. Admin bisa fokus ke penagihan.',
                    'name' => 'Rudi Hartono',
                    'role' => 'Owner',
                    'company' => 'Distributor FMCG, Surabaya',
                    'result' => 'Input order 68% lebih cepat',
                ],
                [
                    'quote' => 'Selisih stok antar gudang hampir hilang. Barang keluar-masuk dan retur sekarang bisa dilacak per dokumen.',
                    'name' => 'Sari Wulandari',
                    'role' => 'Admin Gudang',
                    'company' => 'Distributor Farmasi, Semarang',
                    'result' => 'Selisih stok turun 91%',
                ],
                [
                    'quote' => 'Aging piutang jadi rapi. Tim finance tahu toko mana yang harus ditagih lebih dulu setiap pagi.',
                    'name' => 'Andi Pratama',
                    'role' => 'Finance Manager',
                    'company' => 'Distributor Sparepart, Jakarta',
                    'result' => 'Penagihan 2x lebih cepat',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Apakah data dari Excel bisa dipindahkan?',
                    'answer' => 'Bisa. Tim onboarding membantu import master produk, customer, stok awal, dan saldo piutang. Anda juga mendapat pendampingan sampai data siap dipakai.',
                ],
                [
                    'question' => 'Apakah data kami aman?',
                    'answer' => 'Data disimpan di server dengan koneksi HTTPS, akses berbasis peran, dan pencatatan aktivitas pengguna. Setiap tenant terpisah secara data sehingga tidak bercampur dengan perusahaan lain.',
                ],
                [
                    'question' => 'Berapa jumlah user yang bisa ditambahkan?',
                    'answer' => 'Paket Starter mendukung sampai 5 user, Growth sampai 25 user, dan Enterprise tanpa batas. Jumlah user bisa dinaikkan kapan saja tanpa migrasi ulang.',
                ],
                [
                    'question' => 'Apakah harus kontrak jangka panjang?',
                    'answer' => 'Tidak. Tersedia langganan bulanan dan tahunan. Paket tahunan mendapat diskon 15%, dan Anda bisa berhenti pada akhir periode langganan.',
                ],
                [
                    'question' => 'Apakah ada training untuk tim?',
                    'answer' => 'Ada. Training dasar untuk admin, sales, dan finance termasuk dalam onboarding. Tersedia juga sesi tambahan dan panduan penggunaan.',
                ],
                [
                    'question' => 'Apakah bisa digunakan di HP sales?',
                    'answer' => 'Bisa. Aplikasi salesman dirancang mobile-first untuk cek stok, harga, rute kunjungan, check-in GPS, dan mengambil order langsung dari toko.',
                ],
                [
                    'question' => 'Bagaimana jika butuh integrasi khusus?',
                    'answer' => 'Paket Enterprise mendukung API, ERP, SSO, dan penyesuaian alur. Tim kami akan memetakan kebutuhan teknis sebelum implementasi.',
                ],
                [
                    'question' => 'Apakah ada trial gratis?',
                    'answer' => 'Ada, trial 14 hari untuk paket Starter dan Growth. Anda bisa mencoba alur order, stok, dan laporan sebelum memutuskan berlangganan.',
                ],
            ],
        ];

        $data['structuredData'] = $this->structuredData($data);

        return view('web.landing_page.index', $data);
    }

    public function privacy()
    {
        return view('web.landing_page.legal', $this->legalData(
            'Kebijakan Privasi',
            'Kebijakan Privasi DMS',
            '/landing/kebijakan-privasi',
            [
                [
                    'heading' => '1. Informasi yang kami kumpulkan',
                    'paragraphs' => [
                        'Kami dapat mengumpulkan nama, nama perusahaan, nomor WhatsApp, alamat email, jenis usaha, dan jumlah sales yang Anda kirimkan melalui formulir demo atau trial.',
                        'Data penggunaan seperti halaman yang dikunjungi dan interaksi dengan tombol CTA dapat dicatat untuk meningkatkan kualitas layanan.',
                    ],
                ],
                [
                    'heading' => '2. Cara kami menggunakan informasi',
                    'paragraphs' => [
                        'Informasi digunakan untuk menghubungi Anda terkait demo, trial, onboarding, dukungan, dan informasi produk yang relevan.',
                        'Kami tidak menjual atau menyewakan data pribadi kepada pihak ketiga untuk kepentingan pemasaran mereka.',
                    ],
                ],
                [
                    'heading' => '3. Penyimpanan dan keamanan',
                    'paragraphs' => [
                        'Data disimpan dengan kontrol akses berbasis peran dan koneksi terenkripsi. Akses dibatasi hanya untuk tim yang membutuhkan.',
                        'Kami menetapkan masa retensi data sesuai kebutuhan operasional dan kewajiban hukum yang berlaku.',
                    ],
                ],
                [
                    'heading' => '4. Berbagi data dengan pihak ketiga',
                    'paragraphs' => [
                        'Kami dapat menggunakan penyedia layanan tepercaya untuk hosting, analitik, dan komunikasi. Mereka hanya memproses data sesuai instruksi kami.',
                        'Data dapat diungkapkan apabila diwajibkan oleh hukum atau proses hukum yang sah.',
                    ],
                ],
                [
                    'heading' => '5. Hak Anda',
                    'paragraphs' => [
                        'Anda dapat meminta akses, koreksi, atau penghapusan data pribadi dengan menghubungi kami melalui email atau WhatsApp yang tercantum di halaman ini.',
                        'Permintaan akan ditindaklanjuti sesuai ketentuan perlindungan data pribadi yang berlaku di Indonesia.',
                    ],
                ],
                [
                    'heading' => '6. Hubungi kami',
                    'paragraphs' => [
                        'Pertanyaan mengenai kebijakan privasi dapat dikirim ke ' . self::CONTACT_EMAIL . '.',
                    ],
                ],
            ]
        ));
    }

    public function terms()
    {
        return view('web.landing_page.legal', $this->legalData(
            'Syarat Layanan',
            'Syarat Layanan DMS',
            '/landing/syarat-layanan',
            [
                [
                    'heading' => '1. Penerimaan syarat',
                    'paragraphs' => [
                        'Dengan mengakses halaman ini, mengajukan demo, atau menggunakan layanan DMS, Anda menyetujui syarat layanan yang berlaku.',
                    ],
                ],
                [
                    'heading' => '2. Layanan',
                    'paragraphs' => [
                        'DMS menyediakan platform berbasis langganan untuk membantu operasional distribusi, termasuk order, stok, salesman, piutang, pengiriman, dan laporan.',
                        'Fitur dan paket dapat berbeda sesuai tingkat langganan yang dipilih.',
                    ],
                ],
                [
                    'heading' => '3. Akun dan tanggung jawab pengguna',
                    'paragraphs' => [
                        'Anda bertanggung jawab menjaga kerahasiaan akun dan memastikan data yang dimasukkan akurat dan sah.',
                        'Penggunaan layanan untuk aktivitas melanggar hukum tidak diperbolehkan.',
                    ],
                ],
                [
                    'heading' => '4. Langganan dan pembayaran',
                    'paragraphs' => [
                        'Langganan tersedia dalam periode bulanan atau tahunan. Perpanjangan dilakukan sesuai paket yang dipilih.',
                        'Harga dan ketentuan dapat berubah dengan pemberitahuan sebelumnya.',
                    ],
                ],
                [
                    'heading' => '5. Pembatalan',
                    'paragraphs' => [
                        'Anda dapat berhenti berlangganan pada akhir periode aktif. Data dapat diunduh sebelum masa akses berakhir sesuai ketentuan yang berlaku.',
                    ],
                ],
                [
                    'heading' => '6. Batasan tanggung jawab',
                    'paragraphs' => [
                        'DMS berupaya menjaga ketersediaan layanan, namun tidak menjamin layanan bebas gangguan sepenuhnya. Penggunaan layanan tetap menjadi tanggung jawab pengguna.',
                    ],
                ],
            ]
        ));
    }

    private function legalData(string $title, string $heading, string $path, array $sections): array
    {
        return [
            'pageTitle' => $title . ' — DMS',
            'pageDescription' => $heading . ' untuk platform distribusi DMS.',
            'pageKeywords' => 'kebijakan privasi DMS, syarat layanan DMS',
            'canonical' => url($path),
            'brand' => 'DMS',
            'brandTagline' => 'Distribution Management System',
            'contactEmail' => self::CONTACT_EMAIL,
            'whatsappNumber' => self::WHATSAPP_NUMBER,
            'whatsappUrl' => 'https://wa.me/' . self::WHATSAPP_NUMBER,
            'legalTitle' => $heading,
            'legalUpdated' => 'Diperbarui 25 Oktober 2026',
            'legalSections' => $sections,
            'nav' => [],
        ];
    }

    private function structuredData(array $data): array
    {
        $faqEntities = [];
        foreach ($data['faqs'] as $faq) {
            $faqEntities[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'DMS',
                    'url' => url('/landing'),
                    'description' => $data['pageDescription'],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    'name' => 'DMS — Distribution Management System',
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Web, Android, iOS',
                    'description' => $data['pageDescription'],
                    'offers' => [
                        '@type' => 'Offer',
                        'priceCurrency' => 'IDR',
                        'price' => '499000',
                        'description' => 'Paket Starter, ditagih bulanan',
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqEntities,
                ],
            ],
        ];
    }
}
