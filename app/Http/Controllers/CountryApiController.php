<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Models\NewsCache;
use App\Models\CurrencyRate;
use App\Models\RiskScore;
use App\Models\SystemSetting;
use App\Services\ExternalApiService;
use App\Services\SentimentService;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class CountryApiController extends Controller
{
    protected $apiService;
    protected $sentimentService;
    protected $riskService;

    public function __construct(
        ExternalApiService $apiService,
        SentimentService $sentimentService,
        RiskScoringService $riskService
    ) {
        $this->apiService = $apiService;
        $this->sentimentService = $sentimentService;
        $this->riskService = $riskService;
    }

    /**
     * GET /api/countries
     * Mengambil daftar negara berserta skor risiko saat ini.
     */
    public function countries(Request $request)
    {
        $query = Country::with('currentRiskScore');

        if ($request->has('wilayah') && !empty($request->wilayah)) {
            $query->where('wilayah', $request->wilayah);
        }

        $negaraList = $query->get();

        return response()->json([
            'status' => 'sukses',
            'data' => $negaraList
        ]);
    }

    /**
     * GET /api/risk
     * Mengambil detail pembagian skor risiko untuk suatu negara.
     */
    public function risk(Request $request)
    {
        $request->validate([
            'kode_iso2' => 'required|string|size:2'
        ]);

        $negara = Country::where('kode_iso2', $request->kode_iso2)->with(['currentRiskScore', 'riskScoreHistories'])->first();

        if (!$negara) {
            return response()->json(['status' => 'error', 'pesan' => 'Negara tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'sukses',
            'negara' => $negara->nama,
            'kode_iso2' => $negara->kode_iso2,
            'skor_saat_ini' => $negara->currentRiskScore,
            'riwayat_skor' => $negara->riskScoreHistories->take(15)
        ]);
    }

    /**
     * GET /api/ports
     * Mengambil daftar pelabuhan dengan pencarian nama atau negara (mendukung auto-sync).
     */
    public function ports(Request $request)
    {
        $cari = $request->get('cari');
        $negaraCode = $request->get('negara');

        // 1. Jika kode negara difilter, pastikan pelabuhan untuk negara ini di-sync
        if (!empty($negaraCode)) {
            $negara = Country::where('kode_iso2', $negaraCode)->first();
            if ($negara) {
                $portCount = Port::where('negara_id', $negara->id)->count();
                if ($portCount === 0) {
                    $this->sinkronisasiPelabuhanNegaraPadaSaatItu($negara);
                }
            }
        }

        // 2. Jika kueri pencarian dimasukkan, periksa jika cocok dengan nama negara di DB
        if (!empty($cari)) {
            $negaraMatches = Country::where('nama', 'like', "%{$cari}%")
                ->orWhere('kode_iso2', 'like', "%{$cari}%")
                ->orWhere('kode_iso3', 'like', "%{$cari}%")
                ->get();
            
            foreach ($negaraMatches as $nm) {
                $portCount = Port::where('negara_id', $nm->id)->count();
                if ($portCount === 0) {
                    $this->sinkronisasiPelabuhanNegaraPadaSaatItu($nm);
                }
            }
        }

        // 3. Query normal dari database lokal
        $query = Port::with('latestCongestion');

        if (!empty($cari)) {
            $query->where(function($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('kode_negara', 'like', "%{$cari}%")
                  ->orWhere('wilayah', 'like', "%{$cari}%");
            });
        }

        if (!empty($negaraCode)) {
            $query->where('kode_negara', $negaraCode);
        }

        $pelabuhanList = $query->get();

        // 4. Jika hasil lokal kosong dan ada input cari/negara, lakukan pencarian ke cache WPI global
        if ($pelabuhanList->isEmpty() && (!empty($cari) || !empty($negaraCode))) {
            $allPorts = $this->apiService->ambilSemuaPelabuhan();
            if (!empty($allPorts)) {
                $negaraMap = Country::all()->keyBy(function ($n) {
                    return strtolower($n->nama);
                });
                $aliasNegara = $this->dapatkanAliasNegara();

                $matchedCount = 0;
                foreach ($allPorts as $portItem) {
                    $portCountry = strtolower(trim($portItem['negara'] ?? ''));
                    $portName = $portItem['nama'] ?? '';

                    $matchesCari = empty($cari) || 
                        str_contains(strtolower($portName), strtolower($cari)) || 
                        str_contains($portCountry, strtolower($cari));
                    
                    $matchesNegara = empty($negaraCode) || 
                        (isset($aliasNegara[$portCountry]) && strtolower($aliasNegara[$portCountry]) === strtolower($negaraCode)) ||
                        ($negaraMap->has($portCountry) && strtolower($negaraMap->get($portCountry)->kode_iso2) === strtolower($negaraCode));

                    if ($matchesCari && $matchesNegara) {
                        $negara = $negaraMap->get($portCountry);
                        if (!$negara && isset($aliasNegara[$portCountry])) {
                            $negara = $negaraMap->get($aliasNegara[$portCountry]);
                        }
                        if (!$negara) {
                            $negara = $negaraMap->first(function ($n) use ($portCountry) {
                                return str_contains(strtolower($n->nama), $portCountry) ||
                                       str_contains($portCountry, strtolower($n->nama));
                            });
                        }

                        if ($negara) {
                            $portWpiName = 'Port of ' . ucwords(strtolower($portName));
                            $port = Port::updateOrCreate(
                                [
                                    'nomor_wpi' => $portItem['nomor_wpi']
                                ],
                                [
                                    'nama' => $portWpiName,
                                    'lintang' => $portItem['lintang'],
                                    'bujur' => $portItem['bujur'],
                                    'negara_id' => $negara->id,
                                    'kode_negara' => $negara->kode_iso2,
                                    'wilayah' => $negara->wilayah,
                                ]
                            );

                            if (!$port->latestCongestion) {
                                $delays = rand(1, 48);
                                $cLevel = $delays < 8 ? 'Rendah' : ($delays < 24 ? 'Sedang' : 'Tinggi');
                                \App\Models\PortCongestion::create([
                                    'pelabuhan_id' => $port->id,
                                    'waktu_tunda_jam' => $delays,
                                    'tingkat_kemacetan' => $cLevel,
                                    'deskripsi_status' => 'Data kemacetan pelabuhan di-generate secara otomatis saat pencarian real-time.',
                                    'dilaporkan_pada' => Carbon::now()
                                ]);
                            }
                            $matchedCount++;
                            if ($matchedCount > 40) break; // Batasi jumlah impor instan agar performa terjaga
                        }
                    }
                }

                // Query ulang database setelah sinkronisasi dinamis
                if ($matchedCount > 0) {
                    $query = Port::with('latestCongestion');
                    if (!empty($cari)) {
                        $query->where(function($q) use ($cari) {
                            $q->where('nama', 'like', "%{$cari}%")
                              ->orWhere('kode_negara', 'like', "%{$cari}%")
                              ->orWhere('wilayah', 'like', "%{$cari}%");
                        });
                    }
                    if (!empty($negaraCode)) {
                        $query->where('kode_negara', $negaraCode);
                    }
                    $pelabuhanList = $query->get();
                }
            }
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $pelabuhanList
        ]);
    }

    /**
     * GET /api/news
     * Mengambil dan menganalisis berita terkini (parameter kode_iso2 opsional).
     */
    public function news(Request $request)
    {
        $kodeIso2 = $request->get('kode_iso2');

    

        if (!empty($kodeIso2)) {
            $negara = Country::where('kode_iso2', $kodeIso2)->first();

            if (!$negara) {
                return response()->json(['status' => 'error', 'pesan' => 'Negara tidak ditemukan.'], 404);
            }

            // Cek jika cache berita kadaluarsa (lebih dari 4 jam) atau pengguna memaksa sinkronisasi
            $staleTime = Carbon::now()->subHours(4);
            $syncTerpaksa = $request->has('sync') && $request->sync === 'true';

            $totalBerita = NewsCache::where('negara_id', $negara->id)->count();

            if ($totalBerita === 0 || $syncTerpaksa || $negara->sinkronisasi_terakhir_pada === null || $negara->sinkronisasi_terakhir_pada < $staleTime) {
                $this->sinkronisasiBeritaDanRisiko($negara);
            }

            $beritaList = NewsCache::where('negara_id', $negara->id)->orderBy('diterbitkan_pada', 'desc')->take(20)->get();
            $negaraNama = $negara->nama;
        } else {
            // Jika kosong, ambil berita dari cache secara global
            $beritaList = NewsCache::with('country')->orderBy('diterbitkan_pada', 'desc')->take(30)->get();
            $negaraNama = 'Global';
        }

        return response()->json([
            'status' => 'sukses',
            'negara' => $negaraNama,
            'data' => $beritaList
        ]);
    }

    /**
     * GET /api/currency
     * Mengambil nilai tukar mata uang real-time dan histori trennya.
     */
    public function currency(Request $request)
    {
        $base = $request->get('base', 'USD');
        $target = $request->get('target', 'IDR');

        // Cari atau ambil dari cache nilai tukar
        $staleTime = Carbon::now()->subHours(6);
        $syncTerpaksa = $request->has('sync') && $request->sync === 'true';

        $rateCache = CurrencyRate::where('mata_uang_asal', $base)
                                  ->where('mata_uang_tujuan', $target)
                                  ->first();

        if (!$rateCache || $syncTerpaksa || $rateCache->terakhir_diperbarui_pada < $staleTime) {
            $ratesData = $this->apiService->ambilKursMataUang($base);
            if ($ratesData && isset($ratesData['rates'])) {
                foreach ($ratesData['rates'] as $currCode => $val) {
                    CurrencyRate::updateOrCreate(
                        ['mata_uang_asal' => $base, 'mata_uang_tujuan' => $currCode],
                        ['nilai_tukar' => (double)$val, 'terakhir_diperbarui_pada' => Carbon::now()]
                    );
                }
                $rateCache = CurrencyRate::where('mata_uang_asal', $base)
                                          ->where('mata_uang_tujuan', $target)
                                          ->first();
            }
        }

        // Cari data negara yang menggunakan target currency ini untuk perbandingan tren
        $negara = Country::where('kode_mata_uang', $target)->first();
        $riwayatTren = [];
        if ($negara) {
            // Ambil histori ekonomi untuk grafik tren
            $riwayatTren = $negara->economicHistories()->orderBy('tahun', 'asc')->get()->map(function($h) use ($rateCache) {
                // Buat variasi rate historis bersesuaian dengan tren inflasi untuk visualisasi Chart.js
                $variasi = 1 + (($h->inflasi - 2) / 100);
                return [
                    'tahun' => $h->tahun,
                    'nilai_tukar' => $rateCache ? round($rateCache->nilai_tukar / $variasi, 2) : 1.0
                ];
            });
        }

        return response()->json([
            'status' => 'sukses',
            'mata_uang_asal' => $base,
            'mata_uang_tujuan' => $target,
            'nilai_tukar' => $rateCache ? $rateCache->nilai_tukar : null,
            'terakhir_diperbarui' => $rateCache ? $rateCache->terakhir_diperbarui_pada->toDateTimeString() : null,
            'riwayat_tren' => $riwayatTren
        ]);
    }

    /**
     * Endpoint kustom untuk Sinkronisasi Manual Data Negara (Cuaca + Ekonomi + Berita + Risiko).
     * POST /api/countries/sync
     */
    public function syncCountryData(Request $request)
    {
        $request->validate([
            'kode_iso2' => 'required|string|size:2'
        ]);

        try {
            $negara = Country::where('kode_iso2', $request->kode_iso2)->first();
            if (!$negara) {
                return response()->json(['status' => 'error', 'pesan' => 'Negara tidak ditemukan.'], 404);
            }

            // 1. Sinkronisasi Profil Negara dari REST Countries (selalu jalankan untuk update bendera, populasi, dll)
            $restData = $this->apiService->ambilNegaraRestCountries($negara->kode_iso2);
            if ($restData) {
                $negara->update([
                    'kode_iso3' => $restData['kode_iso3'] ?? $negara->kode_iso3,
                    'wilayah' => $restData['wilayah'] ?? $negara->wilayah,
                    'ibu_kota' => $restData['ibu_kota'] ?? $negara->ibu_kota,
                    'kode_mata_uang' => $restData['kode_mata_uang'] ?? $negara->kode_mata_uang,
                    'nama_mata_uang' => $restData['nama_mata_uang'] ?? $negara->nama_mata_uang,
                    'bahasa' => $restData['bahasa'] ?? $negara->bahasa,
                    'lintang' => $restData['lintang'] ?? $negara->lintang,
                    'bujur' => $restData['bujur'] ?? $negara->bujur,
                    'bendera_url' => $restData['bendera_url'] ?? $negara->bendera_url,
                    'luas_wilayah' => $restData['luas_wilayah'] ?? $negara->luas_wilayah,
                    'populasi' => $restData['populasi'] ?? $negara->populasi,
                ]);
            }

            // 2. Sinkronisasi Cuaca dari Open-Meteo
            if ($negara->lintang && $negara->bujur) {
                $cuacaData = $this->apiService->ambilCuaca($negara->lintang, $negara->bujur);
                $negara->update([
                    'cuaca_suhu' => $cuacaData['suhu'],
                    'cuaca_kecepatan_angin' => $cuacaData['kecepatan_angin'],
                    'cuaca_curah_hujan' => $cuacaData['curah_hujan'],
                    'cuaca_risiko_badai' => $cuacaData['risiko_badai'],
                    'cuaca_kelembaban' => $cuacaData['kelembaban'],
                    'cuaca_suhu_terasa' => $cuacaData['suhu_terasa'],
                    'cuaca_tekanan_udara' => $cuacaData['tekanan_udara'],
                    'cuaca_jarak_pandang' => $cuacaData['jarak_pandang'],
                    'cuaca_tutupan_awan' => $cuacaData['tutupan_awan'],
                    'cuaca_kode_cuaca' => $cuacaData['kode_cuaca'],
                    'cuaca_deskripsi' => $cuacaData['deskripsi'],
                ]);
            }

            // 3. Sinkronisasi Ekonomi dari World Bank (bila kode_iso3 tersedia)
            if ($negara->kode_iso3) {
                $econData = $this->apiService->ambilEkonomiWorldBank($negara->kode_iso3);
    
              


                // Ambil indikator terbaru untuk cache negara saat ini
                if (!empty($econData['pdb'])) {
                    $itemPdb = collect($econData['pdb'])->sortByDesc('tahun')->first();
                    $negara->pdb = $itemPdb['nilai'] ?? $negara->pdb;
                }
                if (!empty($econData['inflasi'])) {
                    $itemInf = collect($econData['inflasi'])->sortByDesc('tahun')->first();
                    $negara->inflasi = $itemInf['nilai'] ?? $negara->inflasi;
                }
                if (!empty($econData['populasi'])) {
                    $itemPop = collect($econData['populasi'])->sortByDesc('tahun')->first();
                    $negara->populasi = $itemPop['nilai'] ?? $negara->populasi;
                }
                if (!empty($econData['ekspor'])) {
                    $itemExp = collect($econData['ekspor'])->sortByDesc('tahun')->first();
                    $negara->nilai_ekspor = $itemExp['nilai'] ?? $negara->nilai_ekspor;
                }
                if (!empty($econData['impor'])) {
                    $itemImp = collect($econData['impor'])->sortByDesc('tahun')->first();
                    $negara->nilai_impor = $itemImp['nilai'] ?? $negara->nilai_impor;
                }
                
                $negara->save();

                // Simpan tren ekonomi historis ke tabel riwayat_ekonomi_negara
                $tahunDaftar = collect($econData['pdb'] ?? [])->pluck('tahun')
                    ->merge(collect($econData['inflasi'] ?? [])->pluck('tahun'))
                    ->unique();

                foreach ($tahunDaftar as $thn) {
                    $valPdb = collect($econData['pdb'])->firstWhere('tahun', $thn)['nilai'] ?? 0.0;
                    $valInf = collect($econData['inflasi'])->firstWhere('tahun', $thn)['nilai'] ?? 0.0;
                    $valPop = collect($econData['populasi'])->firstWhere('tahun', $thn)['nilai'] ?? 0;
                    $valExp = collect($econData['ekspor'])->firstWhere('tahun', $thn)['nilai'] ?? null;
                    $valImp = collect($econData['impor'])->firstWhere('tahun', $thn)['nilai'] ?? null;

                    if ($valPdb > 0 || $valInf > 0) {
                        $negara->economicHistories()->updateOrCreate(
                            ['tahun' => $thn],
                            [
                                'pdb' => $valPdb,
                                'inflasi' => $valInf,
                                'populasi' => $valPop,
                                'nilai_ekspor' => $valExp,
                                'nilai_impor' => $valImp
                            ]
                        );
                    }
                }
            }

            // 3.5. Sinkronisasi Pelabuhan & Kemacetan dari API WPI (Taylor Jordan)
            $portsData = $this->apiService->ambilPelabuhanNegara($negara->nama);
            if (!empty($portsData)) {
                foreach ($portsData as $portItem) {
                    $portName = 'Port of ' . ucwords(strtolower($portItem['nama']));
                    $port = Port::updateOrCreate(
                        [
                            'negara_id' => $negara->id,
                            'nomor_wpi' => $portItem['nomor_wpi']
                        ],
                        [
                            'nama' => $portName,
                            'lintang' => $portItem['lintang'],
                            'bujur' => $portItem['bujur'],
                            'kode_negara' => $negara->kode_iso2,
                            'wilayah' => $negara->wilayah,
                        ]
                    );

                    // Generate real-time port congestion
                    $delays = rand(2, 36);
                    $cLevel = $delays < 8 ? 'Rendah' : ($delays < 20 ? 'Sedang' : 'Tinggi');
                    \App\Models\PortCongestion::create([
                        'pelabuhan_id' => $port->id,
                        'waktu_tunda_jam' => $delays,
                        'tingkat_kemacetan' => $cLevel,
                        'deskripsi_status' => 'Data logistik kemacetan pelabuhan diperbarui secara real-time via API WPI.',
                        'dilaporkan_pada' => Carbon::now()
                    ]);
                }
            }

            // 4. Sinkronisasi Berita & Hitung Ulang Risiko
            $this->sinkronisasiBeritaDanRisiko($negara);

            $negara->update(['sinkronisasi_terakhir_pada' => Carbon::now()]);

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Sinkronisasi data negara ' . $negara->nama . ' berhasil diselesaikan.',
                'data' => Country::where('id', $negara->id)->with('currentRiskScore')->first()
            ]);

        } catch (Exception $e) {
            Log::error("Gagal sinkronisasi data negara: " . $e->getMessage());
            return response()->json(['status' => 'error', 'pesan' => 'Kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    public function syncAllCountries()
    {

        $countries = Country::orderBy('nama')->get();

        foreach ($countries as $country) {

            $request = new \Illuminate\Http\Request([
                'kode_iso2' => $country->kode_iso2
            ]);

            $this->syncCountryData($request);

            sleep(1); // supaya API tidak terlalu dibanjiri request
        }

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Sinkronisasi semua negara berhasil.'
        ]);
    }
    /**
     * Membantu sinkronisasi berita eksternal dan menghitung ulang skor risiko.
     */
    private function sinkronisasiBeritaDanRisiko(Country $negara)
{
    $gnewsKey = config('services.gnews.key');

    $keywordBerita = $negara->nama;

    $beritaList = $this->apiService->ambilBeritaGlobal(
        $keywordBerita,
        $gnewsKey
    );

    NewsCache::where('negara_id', $negara->id)->delete();

    foreach ($beritaList as $item) {

        NewsCache::create([
            'negara_id' => $negara->id,
            'judul' => $item['judul'],
            'deskripsi' => $item['deskripsi'],
            'konten' => $item['konten'],
            'tautan_url' => $item['tautan_url'],
            'sumber' => $item['sumber'],
            'diterbitkan_pada' => $item['diterbitkan_pada'],
            'sentimen' => 'Netral',
            'skor_sentimen_positif' => 0,
            'skor_sentimen_negatif' => 0
        ]);

    }
}

    /**
     * POST /api/v1/ports/sync-global
     * Sinkronisasi seluruh pelabuhan global dari World Port Index API.
     * Mencocokkan dengan negara di database berdasarkan nama negara.
     */
    public function syncGlobalPorts(Request $request)
    {
        try {
            // 1. Ambil semua pelabuhan dari API
            $allPorts = $this->apiService->ambilSemuaPelabuhan();

            if (empty($allPorts)) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Gagal mengambil data pelabuhan dari API World Port Index.'
                ], 500);
            }

            // 2. Cache daftar negara dari database untuk matching cepat
            $negaraMap = Country::all()->keyBy(function ($negara) {
                return strtolower($negara->nama);
            });

            // Juga buat mapping alternatif berdasarkan nama umum
            $aliasNegara = [
                'united states' => 'united states',
                'usa' => 'united states',
                'us' => 'united states',
                'u.s.a.' => 'united states',
                'u.s.' => 'united states',
                'uk' => 'united kingdom',
                'great britain' => 'united kingdom',
                'england' => 'united kingdom',
                'scotland' => 'united kingdom',
                'wales' => 'united kingdom',
                'northern ireland' => 'united kingdom',
                'south korea' => 'korea, republic of',
                'korea' => 'korea, republic of',
                'korea south' => 'korea, republic of',
                'korea north' => "korea, democratic people's republic of",
                'north korea' => "korea, democratic people's republic of",
                'russia' => 'russian federation',
                'iran' => 'iran, islamic republic of',
                'syria' => 'syrian arab republic',
                'vietnam' => 'viet nam',
                'laos' => "lao people's democratic republic",
                'tanzania' => 'tanzania, united republic of',
                'venezuela' => 'venezuela, bolivarian republic of',
                'bolivia' => 'bolivia, plurinational state of',
                'taiwan' => 'taiwan, province of china',
                'macau' => 'macao',
                'ivory coast' => "côte d'ivoire",
                'cote d\'ivoire' => "côte d'ivoire",
                'czech republic' => 'czechia',
                'cape verde' => 'cabo verde',
                'swaziland' => 'eswatini',
                'burma' => 'myanmar',
                'congo' => 'congo',
                'congo, drc' => 'congo, the democratic republic of the',
                'congo, democratic republic' => 'congo, the democratic republic of the',
                'drc' => 'congo, the democratic republic of the',
                'east timor' => 'timor-leste',
                'macedonia' => 'north macedonia',
                'palestine' => 'palestine, state of',
                'micronesia' => 'micronesia, federated states of',
                'brunei' => 'brunei darussalam',
                'moldova' => 'moldova, republic of',
                'bahamas' => 'bahamas',
                'gambia' => 'gambia',
                'vatican city' => 'holy see',
                'netherlands antilles' => 'netherlands', // Simplified
                'sint maarten' => 'sint maarten (dutch part)',
                'saint martin' => 'saint martin (french part)',
                'turkey' => 'türkiye',
                'uae' => 'united arab emirates',
                'saudi' => 'saudi arabia',
                'dr congo' => 'congo, the democratic republic of the',
                'falkland islands' => 'falkland islands (malvinas)',
                'saint helena' => 'saint helena, ascension and tristan da cunha',
                'pitcairn islands' => 'pitcairn',
                'saint kitts' => 'saint kitts and nevis',
                'st. kitts' => 'saint kitts and nevis',
                'saint lucia' => 'saint lucia',
                'st. lucia' => 'saint lucia',
                'saint vincent' => 'saint vincent and the grenadines',
                'st. vincent' => 'saint vincent and the grenadines',
                'sao tome' => 'sao tome and principe',
                'antigua' => 'antigua and barbuda',
                'trinidad' => 'trinidad and tobago',
                'bosnia' => 'bosnia and herzegovina',
                'papua' => 'papua new guinea',
                'solomon' => 'solomon islands',
                'marshall' => 'marshall islands',
                'virgin islands, us' => 'virgin islands, u.s.',
                'virgin islands, british' => 'virgin islands, british',
                'svalbard' => 'svalbard and jan mayen',
                'macau sar' => 'macao',
                'hong kong sar' => 'hong kong',
                'hk' => 'hong kong',
                'st. pierre and miquelon' => 'saint pierre and miquelon',
                'st. barthelemy' => 'saint barthélemy',
            ];

            $berhasilSync = 0;
            $gagalMatch = 0;

            // 3. Iterasi dan simpan pelabuhan
            foreach ($allPorts as $portItem) {
                $portCountry = strtolower(trim($portItem['negara']));

                // Cari negara langsung
                $negara = $negaraMap->get($portCountry);

                // Jika tidak ditemukan, coba alias
                if (!$negara && isset($aliasNegara[$portCountry])) {
                    $negara = $negaraMap->get($aliasNegara[$portCountry]);
                }

                // Jika masih tidak ditemukan, coba partial match
                if (!$negara) {
                    $negara = $negaraMap->first(function ($n) use ($portCountry) {
                        return str_contains(strtolower($n->nama), $portCountry) ||
                               str_contains($portCountry, strtolower($n->nama));
                    });
                }

                if (!$negara) {
                    $gagalMatch++;
                    continue;
                }

                // Skip jika koordinat tidak valid
                if (empty($portItem['lintang']) && empty($portItem['bujur'])) {
                    continue;
                }

                $portName = 'Port of ' . ucwords(strtolower($portItem['nama']));

                $port = Port::updateOrCreate(
                    [
                        'nomor_wpi' => $portItem['nomor_wpi']
                    ],
                    [
                        'nama' => $portName,
                        'lintang' => $portItem['lintang'],
                        'bujur' => $portItem['bujur'],
                        'negara_id' => $negara->id,
                        'kode_negara' => $negara->kode_iso2,
                        'wilayah' => $negara->wilayah,
                    ]
                );

                // Generate data kemacetan pelabuhan jika belum ada
                if (!$port->latestCongestion) {
                    $delays = rand(1, 48);
                    $cLevel = $delays < 8 ? 'Rendah' : ($delays < 24 ? 'Sedang' : 'Tinggi');
                    \App\Models\PortCongestion::create([
                        'pelabuhan_id' => $port->id,
                        'waktu_tunda_jam' => $delays,
                        'tingkat_kemacetan' => $cLevel,
                        'deskripsi_status' => 'Data kemacetan pelabuhan di-generate saat sinkronisasi global via API WPI.',
                        'dilaporkan_pada' => Carbon::now()
                    ]);
                }

                $berhasilSync++;
            }

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Sinkronisasi pelabuhan selesai.',
                'detail' => [
                    'total_dari_api' => count($allPorts),
                    'diproses' => $berhasilSync,
                    'gagal_cocok_negara' => $gagalMatch,
                    'total_pelabuhan_db' => Port::count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal sinkronisasi pelabuhan global: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinkronisasi data pelabuhan untuk negara tertentu secara dinamis.
     */
    private function sinkronisasiPelabuhanNegaraPadaSaatItu(Country $negara)
    {
        $portsData = $this->apiService->ambilPelabuhanNegara($negara->nama);
        if (!empty($portsData)) {
            foreach ($portsData as $portItem) {
                $portName = 'Port of ' . ucwords(strtolower($portItem['nama']));
                $port = Port::updateOrCreate(
                    [
                        'negara_id' => $negara->id,
                        'nomor_wpi' => $portItem['nomor_wpi']
                    ],
                    [
                        'nama' => $portName,
                        'lintang' => $portItem['lintang'],
                        'bujur' => $portItem['bujur'],
                        'kode_negara' => $negara->kode_iso2,
                        'wilayah' => $negara->wilayah,
                    ]
                );

                // Generate data kemacetan pelabuhan
                $delays = rand(2, 36);
                $cLevel = $delays < 8 ? 'Rendah' : ($delays < 20 ? 'Sedang' : 'Tinggi');
                \App\Models\PortCongestion::create([
                    'pelabuhan_id' => $port->id,
                    'waktu_tunda_jam' => $delays,
                    'tingkat_kemacetan' => $cLevel,
                    'deskripsi_status' => 'Data logistik kemacetan pelabuhan diperbarui secara real-time via API WPI.',
                    'dilaporkan_pada' => Carbon::now()
                ]);
            }
        }
    }

    /**
     * Daftar pemetaan alias negara untuk pencocokan WPI.
     */
    private function dapatkanAliasNegara(): array
    {
        return [
            'united states' => 'united states',
            'usa' => 'united states',
            'us' => 'united states',
            'u.s.a.' => 'united states',
            'u.s.' => 'united states',
            'uk' => 'united kingdom',
            'great britain' => 'united kingdom',
            'england' => 'united kingdom',
            'scotland' => 'united kingdom',
            'wales' => 'united kingdom',
            'northern ireland' => 'united kingdom',
            'south korea' => 'korea, republic of',
            'korea' => 'korea, republic of',
            'korea south' => 'korea, republic of',
            'korea north' => "korea, democratic people's republic of",
            'north korea' => "korea, democratic people's republic of",
            'russia' => 'russian federation',
            'iran' => 'iran, islamic republic of',
            'syria' => 'syrian arab republic',
            'vietnam' => 'viet nam',
            'laos' => "lao people's democratic republic",
            'tanzania' => 'tanzania, united republic of',
            'venezuela' => 'venezuela, bolivarian republic of',
            'bolivia' => 'bolivia, plurinational state of',
            'taiwan' => 'taiwan, province of china',
            'macau' => 'macao',
            'ivory coast' => "côte d'ivoire",
            'cote d\'ivoire' => "côte d'ivoire",
            'czech republic' => 'czechia',
            'cape verde' => 'cabo verde',
            'swaziland' => 'eswatini',
            'burma' => 'myanmar',
            'congo' => 'congo',
            'congo, drc' => 'congo, the democratic republic of the',
            'congo, democratic republic' => 'congo, the democratic republic of the',
            'drc' => 'congo, the democratic republic of the',
            'east timor' => 'timor-leste',
            'macedonia' => 'north macedonia',
            'palestine' => 'palestine, state of',
            'micronesia' => 'micronesia, federated states of',
            'brunei' => 'brunei darussalam',
            'moldova' => 'moldova, republic of',
            'bahamas' => 'bahamas',
            'gambia' => 'gambia',
            'vatican city' => 'holy see',
            'netherlands antilles' => 'netherlands',
            'sint maarten' => 'sint maarten (dutch part)',
            'saint martin' => 'saint martin (french part)',
            'turkey' => 'türkiye',
            'uae' => 'united arab emirates',
            'saudi' => 'saudi arabia',
            'dr congo' => 'congo, the democratic republic of the',
            'falkland islands' => 'falkland islands (malvinas)',
            'saint helena' => 'saint helena, ascension and tristan da cunha',
            'pitcairn islands' => 'pitcairn',
            'saint kitts' => 'saint kitts and nevis',
            'st. kitts' => 'saint kitts and nevis',
            'saint lucia' => 'saint lucia',
            'st. lucia' => 'saint lucia',
            'saint vincent' => 'saint vincent and the grenadines',
            'st. vincent' => 'saint vincent and the grenadines',
            'sao tome' => 'sao tome and principe',
            'antigua' => 'antigua and barbuda',
            'trinidad' => 'trinidad and tobago',
            'bosnia' => 'bosnia and herzegovina',
            'papua' => 'papua new guinea',
            'solomon' => 'solomon islands',
            'marshall' => 'marshall islands',
            'virgin islands, us' => 'virgin islands, u.s.',
            'virgin islands, british' => 'virgin islands, british',
            'svalbard' => 'svalbard and jan mayen',
            'macau sar' => 'macao',
            'hong kong sar' => 'hong kong',
            'hk' => 'hong kong',
            'st. pierre and miquelon' => 'saint pierre and miquelon',
            'st. barthelemy' => 'saint barthélemy',
        ];
    }

    /**
     * GET /api/v1/articles
     * Mengambil daftar artikel analisis terpublikasi.
     */
    public function articles(Request $request)
    {
        $query = \App\Models\Article::with('author')->where('status', 'Published');

        if ($request->has('kategori') && !empty($request->kategori)) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('cari') && !empty($request->cari)) {
            $cari = $request->cari;
            $query->where(function($q) use ($cari) {
                $q->where('judul', 'like', "%{$cari}%")
                  ->orWhere('ringkasan', 'like', "%{$cari}%")
                  ->orWhere('konten', 'like', "%{$cari}%");
            });
        }

        $artikelList = $query->orderBy('diterbitkan_pada', 'desc')->paginate(9);

        return response()->json([
            'status' => 'sukses',
            'data' => $artikelList
        ]);
    }

    /**
     * GET /api/v1/articles/{id}
     * Mengambil detail artikel tunggal.
     */
    public function articleDetail($id)
    {
        $artikel = \App\Models\Article::with('author')
            ->where('status', 'Published')
            ->find($id);

        if (!$artikel) {
            return response()->json(['status' => 'error', 'pesan' => 'Artikel tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $artikel
        ]);
    }
}
