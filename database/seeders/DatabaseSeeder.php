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

        // 4. Seed Countries (Negara) - 10 Countries
        $countriesData = [
            [
                'nama' => 'Germany', 'kode_iso2' => 'DE', 'kode_iso3' => 'DEU', 'wilayah' => 'Eropa',
                'ibu_kota' => 'Berlin', 'kode_mata_uang' => 'EUR', 'nama_mata_uang' => 'Euro',
                'bahasa' => 'Jerman', 'populasi' => 83200000, 'pdb' => 4260000000000,
                'inflasi' => 2.3, 'nilai_ekspor' => 1650000000000, 'nilai_impor' => 1420000000000,
                'lintang' => 51.1657, 'bujur' => 10.4515
            ],
            [
                'nama' => 'China', 'kode_iso2' => 'CN', 'kode_iso3' => 'CHN', 'wilayah' => 'Asia',
                'ibu_kota' => 'Beijing', 'kode_mata_uang' => 'CNY', 'nama_mata_uang' => 'Yuan Renminbi',
                'bahasa' => 'Mandarin', 'populasi' => 1412000000, 'pdb' => 17730000000000,
                'inflasi' => 0.7, 'nilai_ekspor' => 3590000000000, 'nilai_impor' => 2680000000000,
                'lintang' => 35.8617, 'bujur' => 104.1954
            ],
            [
                'nama' => 'Indonesia', 'kode_iso2' => 'ID', 'kode_iso3' => 'IDN', 'wilayah' => 'Asia',
                'ibu_kota' => 'Jakarta', 'kode_mata_uang' => 'IDR', 'nama_mata_uang' => 'Rupiah',
                'bahasa' => 'Indonesia', 'populasi' => 273800000, 'pdb' => 1186000000000,
                'inflasi' => 2.6, 'nilai_ekspor' => 292000000000, 'nilai_impor' => 237000000000,
                'lintang' => -0.7893, 'bujur' => 113.9213
            ],
            [
                'nama' => 'Australia', 'kode_iso2' => 'AU', 'kode_iso3' => 'AUS', 'wilayah' => 'Oseania',
                'ibu_kota' => 'Canberra', 'kode_mata_uang' => 'AUD', 'nama_mata_uang' => 'Dolar Australia',
                'bahasa' => 'Inggris', 'populasi' => 25680000, 'pdb' => 1540000000000,
                'inflasi' => 3.6, 'nilai_ekspor' => 340000000000, 'nilai_impor' => 280000000000,
                'lintang' => -25.2744, 'bujur' => 133.7751
            ],
            [
                'nama' => 'United States', 'kode_iso2' => 'US', 'kode_iso3' => 'USA', 'wilayah' => 'Amerika',
                'ibu_kota' => 'Washington D.C.', 'kode_mata_uang' => 'USD', 'nama_mata_uang' => 'Dolar AS',
                'bahasa' => 'Inggris', 'populasi' => 331900000, 'pdb' => 23320000000000,
                'inflasi' => 3.1, 'nilai_ekspor' => 1750000000000, 'nilai_impor' => 2930000000000,
                'lintang' => 37.0902, 'bujur' => -95.7129
            ],
            [
                'nama' => 'Singapore', 'kode_iso2' => 'SG', 'kode_iso3' => 'SGP', 'wilayah' => 'Asia',
                'ibu_kota' => 'Singapura', 'kode_mata_uang' => 'SGD', 'nama_mata_uang' => 'Dolar Singapura',
                'bahasa' => 'Inggris, Melayu, Mandarin, Tamil', 'populasi' => 5450000, 'pdb' => 396800000000,
                'inflasi' => 4.8, 'nilai_ekspor' => 450000000000, 'nilai_impor' => 390000000000,
                'lintang' => 1.3521, 'bujur' => 103.8198
            ],
            [
                'nama' => 'Japan', 'kode_iso2' => 'JP', 'kode_iso3' => 'JPN', 'wilayah' => 'Asia',
                'ibu_kota' => 'Tokyo', 'kode_mata_uang' => 'JPY', 'nama_mata_uang' => 'Yen',
                'bahasa' => 'Jepang', 'populasi' => 125700000, 'pdb' => 4940000000000,
                'inflasi' => 2.5, 'nilai_ekspor' => 710000000000, 'nilai_impor' => 760000000000,
                'lintang' => 36.2048, 'bujur' => 138.2529
            ],
            [
                'nama' => 'Brazil', 'kode_iso2' => 'BR', 'kode_iso3' => 'BRA', 'wilayah' => 'Amerika',
                'ibu_kota' => 'Brasilia', 'kode_mata_uang' => 'BRL', 'nama_mata_uang' => 'Real Brasil',
                'bahasa' => 'Portugis', 'populasi' => 214300000, 'pdb' => 1608000000000,
                'inflasi' => 4.6, 'nilai_ekspor' => 280000000000, 'nilai_impor' => 220000000000,
                'lintang' => -14.2350, 'bujur' => -51.9253
            ],
            [
                'nama' => 'India', 'kode_iso2' => 'IN', 'kode_iso3' => 'IND', 'wilayah' => 'Asia',
                'ibu_kota' => 'New Delhi', 'kode_mata_uang' => 'INR', 'nama_mata_uang' => 'Rupee India',
                'bahasa' => 'Hindi, Inggris', 'populasi' => 1408000000, 'pdb' => 3176000000000,
                'inflasi' => 5.4, 'nilai_ekspor' => 390000000000, 'nilai_impor' => 570000000000,
                'lintang' => 20.5937, 'bujur' => 78.9629
            ],
            [
                'nama' => 'Netherlands', 'kode_iso2' => 'NL', 'kode_iso3' => 'NLD', 'wilayah' => 'Eropa',
                'ibu_kota' => 'Amsterdam', 'kode_mata_uang' => 'EUR', 'nama_mata_uang' => 'Euro',
                'bahasa' => 'Belanda', 'populasi' => 17530000, 'pdb' => 1018000000000,
                'inflasi' => 3.8, 'nilai_ekspor' => 690000000000, 'nilai_impor' => 610000000000,
                'lintang' => 52.1326, 'bujur' => 5.2913
            ]
        ];

        foreach ($countriesData as $cData) {
            $country = Country::updateOrCreate(['kode_iso2' => $cData['kode_iso2']], $cData);

            // Seed historical economic indicators (2020-2024)
            for ($year = 2020; $year <= 2024; $year++) {
                $factor = 1 + (($year - 2020) * 0.03) + (rand(-2, 2) / 100);
                CountryEconomicHistory::updateOrCreate(
                    [
                        'negara_id' => $country->id,
                        'tahun' => $year
                    ],
                    [
                        'pdb' => $country->pdb * $factor,
                        'inflasi' => max(0.1, $country->inflasi + (($year - 2022) * 0.8) + (rand(-1, 1) * 0.3)),
                        'populasi' => intval($country->populasi * (1 + (($year - 2020) * 0.005))),
                        'nilai_ekspor' => $country->nilai_ekspor * $factor,
                        'nilai_impor' => $country->nilai_impor * $factor,
                    ]
                );
            }

            // Seed initial mock current risk scores (Skor Risiko)
            $wRisk = rand(15, 65);
            $iRisk = rand(20, 80);
            $cRisk = rand(10, 50);
            $sRisk = rand(25, 75);
            $totRisk = intval((0.3 * $wRisk) + (0.2 * $iRisk) + (0.1 * $cRisk) + (0.4 * $sRisk));
            $level = $totRisk < 35 ? 'Rendah' : ($totRisk < 60 ? 'Sedang' : 'Tinggi');

            RiskScore::updateOrCreate(
                ['negara_id' => $country->id],
                [
                    'risiko_cuaca' => $wRisk,
                    'risiko_inflasi' => $iRisk,
                    'risiko_nilai_tukar' => $cRisk,
                    'risiko_sentimen_berita' => $sRisk,
                    'total_risiko' => $totRisk,
                    'tingkat_risiko' => $level
                ]
            );

            // Seed historical risk logs
            for ($i = 30; $i >= 0; $i -= 5) {
                $calcAt = Carbon::now()->subDays($i);
                RiskScoreHistory::create([
                    'negara_id' => $country->id,
                    'risiko_cuaca' => max(10, $wRisk + rand(-10, 10)),
                    'risiko_inflasi' => $iRisk,
                    'risiko_nilai_tukar' => max(10, $cRisk + rand(-5, 5)),
                    'risiko_sentimen_berita' => max(10, $sRisk + rand(-15, 15)),
                    'total_risiko' => max(10, $totRisk + rand(-8, 8)),
                    'dihitung_pada' => $calcAt
                ]);
            }
        }

        // 5. Seed Ports (Pelabuhan) - 10 Major World Ports
        $portsData = [
            ['nama' => 'Port of Rotterdam', 'lintang' => 51.9489, 'bujur' => 4.1372, 'country_code' => 'NL', 'wilayah' => 'Eropa', 'nomor_wpi' => 'WPI-001'],
            ['nama' => 'Port of Singapore', 'lintang' => 1.2740, 'bujur' => 103.8400, 'country_code' => 'SG', 'wilayah' => 'Asia', 'nomor_wpi' => 'WPI-002'],
            ['nama' => 'Port of Shanghai', 'lintang' => 30.6200, 'bujur' => 122.0600, 'country_code' => 'CN', 'wilayah' => 'Asia', 'nomor_wpi' => 'WPI-003'],
            ['nama' => 'Port of Tanjung Priok', 'lintang' => -6.1000, 'bujur' => 106.8900, 'country_code' => 'ID', 'wilayah' => 'Asia', 'nomor_wpi' => 'WPI-004'],
            ['nama' => 'Port of Hamburg', 'lintang' => 53.5300, 'bujur' => 9.9400, 'country_code' => 'DE', 'wilayah' => 'Eropa', 'nomor_wpi' => 'WPI-005'],
            ['nama' => 'Port of Sydney', 'lintang' => -33.8500, 'bujur' => 151.2100, 'country_code' => 'AU', 'wilayah' => 'Oseania', 'nomor_wpi' => 'WPI-006'],
            ['nama' => 'Port of Los Angeles', 'lintang' => 33.7400, 'bujur' => -118.2600, 'country_code' => 'US', 'wilayah' => 'Amerika', 'nomor_wpi' => 'WPI-007'],
            ['nama' => 'Port of Tokyo', 'lintang' => 35.6200, 'bujur' => 139.7800, 'country_code' => 'JP', 'wilayah' => 'Asia', 'nomor_wpi' => 'WPI-008'],
            ['nama' => 'Port of Santos', 'lintang' => -23.9600, 'bujur' => -46.3000, 'country_code' => 'BR', 'wilayah' => 'Amerika', 'nomor_wpi' => 'WPI-009'],
            ['nama' => 'Port of Mumbai', 'lintang' => 18.9400, 'bujur' => 72.8400, 'country_code' => 'IN', 'wilayah' => 'Asia', 'nomor_wpi' => 'WPI-010']
        ];

        foreach ($portsData as $pData) {
            $country = Country::where('kode_iso2', $pData['country_code'])->first();
            if ($country) {
                $port = Port::updateOrCreate(
                    ['nomor_wpi' => $pData['nomor_wpi']],
                    [
                        'nama' => $pData['nama'],
                        'lintang' => $pData['lintang'],
                        'bujur' => $pData['bujur'],
                        'negara_id' => $country->id,
                        'kode_negara' => $country->kode_iso2,
                        'wilayah' => $pData['wilayah']
                    ]
                );

                // Seed dummy port congestion (Kemacetan Pelabuhan)
                $delays = rand(0, 36);
                $cLevel = $delays < 6 ? 'Rendah' : ($delays < 18 ? 'Sedang' : 'Tinggi');
                PortCongestion::create([
                    'pelabuhan_id' => $port->id,
                    'waktu_tunda_jam' => $delays,
                    'tingkat_kemacetan' => $cLevel,
                    'deskripsi_status' => 'Operasional normal dengan sedikit penyesuaian jadwal karena kapasitas musiman.',
                    'dilaporkan_pada' => Carbon::now()
                ]);
            }
        }

        // 6. Seed Default Analysis Articles (Artikel Analisis)
        Article::updateOrCreate(
            ['judul' => 'Gangguan Geopolitik Laut Merah & Tarif Pengiriman'],
            [
                'ringkasan' => 'Analisis jalur alternatif pengiriman di sekitar Tanjung Harapan yang memicu kenaikan tarif logistik dan inflasi bahan bakar.',
                'konten' => '<p>Ketegangan geopolitik baru-baru ini di wilayah Laut Merah memaksa banyak operator kapal untuk mengalihkan rute kapal mereka menjauhi Terusan Suez, memilih rute memutar yang lebih jauh melewati Tanjung Harapan, Afrika Selatan. Rute alternatif ini menambah durasi pelayaran selama 10 hingga 14 hari dari Asia ke Eropa Utara, yang memicu peningkatan signifikan pada biaya operasional kapal.</p><h5>Analisis Dampak:</h5><ul><li><strong>Konsumsi Bahan Bakar:</strong> Mengalami kenaikan sebesar 30-35% per perjalanan bolak-balik.</li><li><strong>Tarif Kontainer:</strong> Indeks tarif pengiriman spot melonjak hingga 150% pada koridor logistik utama.</li><li><strong>Kemacetan Pelabuhan:</strong> Jadwal kedatangan kapal yang tidak menentu mulai menimbulkan antrean di pelabuhan Eropa seperti Rotterdam dan Hamburg.</li></ul><p>Pelaku bisnis disarankan untuk menjaga tingkat persediaan cadangan (buffer stock) ekstra selama 2-3 minggu guna menghindari terhentinya proses produksi.</p>',
                'penulis_id' => $admin->id,
                'status' => 'Published',
                'diterbitkan_pada' => Carbon::now()->subDays(4)
            ]
        );

        Article::updateOrCreate(
            ['judul' => 'Kekeringan Terusan Panama & Pergeseran Logistik Intermodal'],
            [
                'ringkasan' => 'Kekeringan ekstrem membatasi lalu lintas harian Terusan Panama, memindahkan kargo ke jalur kereta pantai barat AS.',
                'konten' => '<p>Otoritas Terusan Panama telah mengurangi kuota transit harian kapal akibat tingkat air Danau Gatun yang berada pada level terendah dalam sejarah. Hambatan ini memaksa kapal curah untuk mengambil rute memutar yang lebih jauh atau membongkar muatan kontainer di pelabuhan Pantai Barat AS (seperti Los Angeles) untuk dikirim melalui jalur kereta api domestik.</p><p>Pergeseran ini meningkatkan volume logistik intermodal di AS secara drastis, memicu kenaikan tarif truk domestik, serta mengubah prioritas distribusi barang. Risiko rantai pasok untuk komoditas ekspor segar dari Amerika Latin (seperti kopi dan buah-buahan) berada pada tingkat yang sangat tinggi karena keterbatasan kapasitas penyimpanan dingin.</p>',
                'penulis_id' => $admin->id,
                'status' => 'Published',
                'diterbitkan_pada' => Carbon::now()->subDays(2)
            ]
        );
    }
}
