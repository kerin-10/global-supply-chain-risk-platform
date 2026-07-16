<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Country;
use App\Models\CountryEconomicHistory;
use App\Models\Port;
use App\Models\PortCongestion;
use App\Models\RiskScore;
use App\Models\RiskScoreHistory;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use App\Models\SystemSetting;
use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users (Pengguna) and Profiles (Profil Pengguna)
        $admin = User::updateOrCreate(
            ['email' => 'admin@supplyrisk.com'],
            [
                'nama' => 'Administrator Sistem',
                'kata_sandi' => Hash::make('admin123'),
                'peran' => 'admin',
            ]
        );

        UserProfile::updateOrCreate(
            ['pengguna_id' => $admin->id],
            [
                'telepon' => '+628123456789',
                'departemen' => 'Analisis Risiko',
                'biodata' => 'Analis utama untuk penilaian risiko logistik global.',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@supplyrisk.com'],
            [
                'nama' => 'Pengguna Operasional Bisnis',
                'kata_sandi' => Hash::make('user123'),
                'peran' => 'user',
            ]
        );

        UserProfile::updateOrCreate(
            ['pengguna_id' => $user->id],
            [
                'telepon' => '+628987654321',
                'departemen' => 'Operasional Rantai Pasok',
                'biodata' => 'Manajer pengadaan internasional dan koordinasi logistik.',
            ]
        );

        // 2. Seed System Settings (Pengaturan Sistem)
        SystemSetting::setVal('bobot_cuaca', '0.30', 'Bobot risiko cuaca dalam nilai total (30%)');
        SystemSetting::setVal('bobot_inflasi', '0.20', 'Bobot risiko inflasi dalam nilai total (20%)');
        SystemSetting::setVal('bobot_sentimen', '0.40', 'Bobot risiko sentimen berita geopolitik dalam nilai total (40%)');
        SystemSetting::setVal('bobot_nilai_tukar', '0.10', 'Bobot risiko volatilitas nilai tukar dalam nilai total (10%)');
        SystemSetting::setVal('gnews_api_key', '', 'Kunci API GNews (biarkan kosong untuk menggunakan fallback RSS)');
        SystemSetting::setVal('sumber_sinkronisasi_berita', 'rss', 'Sumber intelijen berita (gnews atau rss)');

        // 3. Seed Positive & Negative Lexicon Words (Kata Positif & Negatif)
        $posWords = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'boost', 'success', 'recovery',
            'positive', 'strong', 'gains', 'expand', 'dynamic', 'progressive', 'surplus', 'robust',
            'rise', 'optimistic', 'safe', 'upgrade', 'advance', 'develop', 'healthy', 'efficiency',
            'peak', 'trust', 'secure', 'solution', 'benefit', 'wealth', 'cooperation', 'agreement',
            'stabilize', 'ease', 'open', 'revive', 'accelerate', 'flourish', 'innovate', 'productive'
        ];

        foreach ($posWords as $word) {
            PositiveWord::updateOrCreate(['kata' => $word]);
        }

        $negWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'conflict', 'tension', 'tariff',
            'strike', 'congestion', 'sanction', 'decrease', 'drop', 'loss', 'crash', 'decline',
            'negative', 'weak', 'deficit', 'recession', 'risk', 'threat', 'danger', 'damage',
            'shutdown', 'disruption', 'bottleneck', 'ban', 'blockade', 'protest', 'embargo', 'escalate',
            'collapse', 'shortage', 'struggle', 'uncertainty', 'halt', 'plunge', 'slowdown', 'epidemic'
        ];

        foreach ($negWords as $word) {
            NegativeWord::updateOrCreate(['kata' => $word]);
        }

        // 4. Seed Countries and Ports (250 Countries + Global Ports)
        $this->call([
            CountrySeeder::class
        ]);

        // 6. Seed Default Analysis Articles (Artikel Analisis)
        $articles = [
            [
                'judul' => 'Gangguan Geopolitik Laut Merah & Tarif Pengiriman',
                'kategori' => 'Geopolitik',
                'gambar_url' => 'https://images.unsplash.com/photo-1586528116311-ad8ed7c50a63?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Analisis jalur alternatif pengiriman di sekitar Tanjung Harapan yang memicu kenaikan tarif logistik dan inflasi bahan bakar.',
                'konten' => '<p>Ketegangan geopolitik baru-baru ini di wilayah Laut Merah memaksa banyak operator kapal untuk mengalihkan rute kapal mereka menjauhi Terusan Suez, memilih rute memutar yang lebih jauh melewati Tanjung Harapan, Afrika Selatan. Rute alternatif ini menambah durasi pelayaran selama 10 hingga 14 hari dari Asia ke Eropa Utara, yang memicu peningkatan signifikan pada biaya operasional kapal.</p><h5>Analisis Dampak:</h5><ul><li><strong>Konsumsi Bahan Bakar:</strong> Mengalami kenaikan sebesar 30-35% per perjalanan bolak-balik.</li><li><strong>Tarif Kontainer:</strong> Indeks tarif pengiriman spot melonjak hingga 150% pada koridor logistik utama.</li><li><strong>Kemacetan Pelabuhan:</strong> Jadwal kedatangan kapal yang tidak menentu mulai menimbulkan antrean di pelabuhan Eropa seperti Rotterdam dan Hamburg.</li></ul><p>Pelaku bisnis disarankan untuk menjaga tingkat persediaan cadangan (buffer stock) ekstra selama 2-3 minggu guna menghindari terhentinya proses produksi.</p>',
                'diterbitkan_pada' => Carbon::now()->subDays(4)
            ],
            [
                'judul' => 'Kekeringan Terusan Panama & Pergeseran Logistik Intermodal',
                'kategori' => 'Cuaca & Iklim',
                'gambar_url' => 'https://images.unsplash.com/photo-1542293787349-2e633d7b328a?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Kekeringan ekstrem membatasi lalu lintas harian Terusan Panama, memindahkan kargo ke jalur kereta pantai barat AS.',
                'konten' => '<p>Otoritas Terusan Panama telah mengurangi kuota transit harian kapal akibat tingkat air Danau Gatun yang berada pada level terendah dalam sejarah. Hambatan ini memaksa kapal curah untuk mengambil rute memutar yang lebih jauh atau membongkar muatan kontainer di pelabuhan Pantai Barat AS (seperti Los Angeles) untuk dikirim melalui jalur kereta api domestik.</p><p>Pergeseran ini meningkatkan volume logistik intermodal di AS secara drastis, memicu kenaikan tarif truk domestik, serta mengubah prioritas distribusi barang. Risiko rantai pasok untuk komoditas ekspor segar dari Amerika Latin (seperti kopi dan buah-buahan) berada pada tingkat yang sangat tinggi karena keterbatasan kapasitas penyimpanan dingin.</p>',
                'diterbitkan_pada' => Carbon::now()->subDays(2)
            ],
            [
                'judul' => 'Inovasi AI dalam Prediksi Kemacetan Pelabuhan',
                'kategori' => 'Teknologi',
                'gambar_url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Bagaimana kecerdasan buatan membantu memprediksi dan mengurangi waktu tunggu kapal di pelabuhan utama.',
                'konten' => '<p>Penerapan algoritma machine learning dan analitik prediktif di pelabuhan-pelabuhan utama seperti Shanghai dan Rotterdam telah berhasil mengurangi kemacetan hingga 20%. Sistem AI ini menganalisis ribuan data points mulai dari cuaca, pergerakan kapal, hingga riwayat keterlambatan untuk memberikan rekomendasi jadwal bongkar muat yang optimal.</p><h5>Manfaat Utama:</h5><ul><li><strong>Optimalisasi Rute:</strong> Memberikan panduan real-time bagi kapal yang mendekati pelabuhan untuk mengurangi waktu idle.</li><li><strong>Efisiensi Bahan Bakar:</strong> Mengurangi emisi karbon dengan meminimalkan waktu tunggu mesin menyala.</li><li><strong>Transparansi Rantai Pasok:</strong> Memberikan estimasi kedatangan yang lebih akurat kepada importir dan penyedia logistik.</li></ul>',
                'diterbitkan_pada' => Carbon::now()->subDays(6)
            ],
            [
                'judul' => 'Dampak Transisi Energi Hijau pada Biaya Pelayaran',
                'kategori' => 'Ekonomi',
                'gambar_url' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Kewajiban penggunaan bahan bakar rendah karbon mendorong perombakan infrastruktur kapal dan kenaikan biaya freight.',
                'konten' => '<p>Aturan IMO 2023 yang mewajibkan industri pelayaran maritim untuk mengurangi intensitas karbon memaksa pemilik kapal untuk beralih ke bahan bakar alternatif seperti LNG, metanol hijau, atau hidrogen. Meskipun ini merupakan langkah positif bagi lingkungan, transisi ini menyebabkan lonjakan biaya operasional jangka pendek.</p><p>Pajak karbon yang mulai diterapkan di Eropa (EU ETS) untuk pelayaran juga menambahkan premi baru pada biaya pengiriman. Pelaku industri rantai pasok harus bersiap menghadapi kenaikan biaya logistik dasar sebesar 10-15% selama masa transisi ini. Investasi pada armada yang lebih efisien menjadi kunci untuk daya saing jangka panjang.</p>',
                'diterbitkan_pada' => Carbon::now()->subDays(10)
            ],
            [
                'judul' => 'Pergeseran Pusat Manufaktur Global ke Asia Tenggara',
                'kategori' => 'Pasar Global',
                'gambar_url' => 'https://images.unsplash.com/photo-1587293852726-70cdb56c2866?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Tren "China Plus One" memperkuat posisi negara seperti Vietnam, Indonesia, dan Thailand dalam rantai pasok global.',
                'konten' => '<p>Ketidakpastian tarif perdagangan dan keinginan untuk mendiversifikasi risiko rantai pasok telah mempercepat strategi "China Plus One" di kalangan perusahaan multinasional. Kawasan Asia Tenggara kini menjadi penerima manfaat utama, dengan peningkatan investasi asing langsung di sektor manufaktur.</p><p>Vietnam memimpin di sektor elektronik, sementara Indonesia berfokus pada ekosistem kendaraan listrik (EV) berkat cadangan nikelnya. Namun, tantangan masih ada berupa infrastruktur pelabuhan dan konektivitas hinterland yang perlu terus ditingkatkan untuk mengimbangi lonjakan volume ekspor.</p>',
                'diterbitkan_pada' => Carbon::now()->subDays(15)
            ],
            [
                'judul' => 'Ancaman Keamanan Siber di Infrastruktur Logistik Maritim',
                'kategori' => 'Keamanan',
                'gambar_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Meningkatnya serangan ransomware yang menargetkan sistem operasi pelabuhan dan operator kapal logistik global.',
                'konten' => '<p>Digitalisasi rantai pasok membawa tantangan baru berupa kerentanan terhadap serangan siber. Beberapa pelabuhan besar di Australia dan Eropa baru-baru ini mengalami gangguan operasional akibat serangan ransomware yang melumpuhkan sistem manajemen terminal (TOS).</p><h5>Langkah Mitigasi:</h5><ul><li><strong>Segmentasi Jaringan:</strong> Memisahkan jaringan operasional teknologi (OT) dari jaringan IT korporat.</li><li><strong>Audit Keamanan Rutin:</strong> Melakukan penetration testing berkala pada infrastruktur pelabuhan.</li><li><strong>Pelatihan Karyawan:</strong> Meningkatkan kesadaran akan phishing dan praktik keamanan siber dasar.</li></ul>',
                'diterbitkan_pada' => Carbon::now()->subDays(18)
            ],
            [
                'judul' => 'Analisis Tren Konsolidasi Perusahaan Pelayaran Logistik',
                'kategori' => 'Ekonomi',
                'gambar_url' => 'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Bagaimana merger dan akuisisi besar-besaran membentuk ulang peta persaingan dan stabilitas harga pelayaran.',
                'konten' => '<p>Industri pelayaran logistik global semakin terkonsolidasi di tangan segelintir aliansi raksasa. Hal ini memberikan operator skala besar kekuatan yang belum pernah terjadi sebelumnya dalam menentukan tarif dan mengatur kapasitas. Konsolidasi ini membawa stabilitas harga, tetapi juga mengurangi ruang negosiasi bagi pengirim kargo kecil dan menengah.</p><p>Otoritas anti-monopoli di AS dan Eropa mulai memperhatikan dinamika pasar ini dengan lebih saksama. Diperkirakan akan ada lebih banyak regulasi yang memantau aliansi-aliansi strategis tersebut di tahun mendatang.</p>',
                'diterbitkan_pada' => Carbon::now()->subDays(22)
            ],
            [
                'judul' => 'Dampak Inflasi terhadap Daya Beli Konsumen dan Volume Kargo',
                'kategori' => 'Ekonomi',
                'gambar_url' => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?q=80&w=800&auto=format&fit=crop',
                'ringkasan' => 'Inflasi yang berkepanjangan di negara-negara Barat menyebabkan penurunan impor barang ritel dari Asia.',
                'konten' => '<p>Tekanan inflasi yang persisten di Amerika Serikat dan Eropa telah melemahkan daya beli konsumen untuk barang-barang diskresi. Akibatnya, para peritel menahan pesanan baru, yang secara langsung berdampak pada penurunan volume kargo di rute trans-Pasifik dan Asia-Eropa.</p><p>Meskipun tarif pengiriman mulai normal kembali ke level sebelum pandemi, rendahnya permintaan memaksa penyedia logistik untuk membatalkan sejumlah pelayaran (blank sailings) guna menyeimbangkan pasokan kapasitas dengan permintaan aktual.</p>',
                'diterbitkan_pada' => Carbon::now()->subDays(28)
            ]
        ];

        foreach ($articles as $art) {
            Article::updateOrCreate(
                ['judul' => $art['judul']],
                [
                    'kategori' => $art['kategori'],
                    'gambar_url' => $art['gambar_url'],
                    'ringkasan' => $art['ringkasan'],
                    'konten' => $art['konten'],
                    'penulis_id' => $admin->id,
                    'status' => 'Published',
                    'diterbitkan_pada' => $art['diterbitkan_pada']
                ]
            );
        }
    }
}
