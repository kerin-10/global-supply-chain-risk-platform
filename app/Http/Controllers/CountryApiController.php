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
     * Mengambil daftar pelabuhan dengan pencarian nama atau negara.
     */
    public function ports(Request $request)
    {
        $query = Port::with('latestCongestion');

        if ($request->has('cari') && !empty($request->cari)) {
            $cari = $request->cari;
            $query->where(function($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('kode_negara', 'like', "%{$cari}%")
                  ->orWhere('wilayah', 'like', "%{$cari}%");
            });
        }

        if ($request->has('negara') && !empty($request->negara)) {
            $query->where('kode_negara', $request->negara);
        }

        $pelabuhanList = $query->get();

        return response()->json([
            'status' => 'sukses',
            'data' => $pelabuhanList
        ]);
    }

    /**
     * GET /api/news
     * Mengambil dan menganalisis berita terkini untuk suatu negara.
     */
    public function news(Request $request)
    {
        $request->validate([
            'kode_iso2' => 'required|string|size:2'
        ]);

        $negara = Country::where('kode_iso2', $request->kode_iso2)->first();

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

        $beritaList = NewsCache::where('negara_id', $negara->id)->orderBy('diterbitkan_pada', 'desc')->take(10)->get();

        return response()->json([
            'status' => 'sukses',
            'negara' => $negara->nama,
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

            // 1. Sinkronisasi Profil Negara dari REST Countries (bila data wilayah/koordinat kosong)
            if (empty($negara->lintang) || empty($negara->bujur) || empty($negara->kode_iso3)) {
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
                    ]);
                }
            }

            // 2. Sinkronisasi Cuaca dari Open-Meteo
            if ($negara->lintang && $negara->bujur) {
                $cuacaData = $this->apiService->ambilCuaca($negara->lintang, $negara->bujur);
                $negara->update([
                    'cuaca_suhu' => $cuacaData['suhu'],
                    'cuaca_kecepatan_angin' => $cuacaData['kecepatan_angin'],
                    'cuaca_curah_hujan' => $cuacaData['curah_hujan'],
                    'cuaca_risiko_badai' => $cuacaData['risiko_badai'],
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

    /**
     * Membantu sinkronisasi berita eksternal dan menghitung ulang skor risiko.
     */
    private function sinkronisasiBeritaDanRisiko(Country $negara)
    {
        $gnewsKey = SystemSetting::getVal('gnews_api_key', '');
        $keywordBerita = "{$negara->nama} logistics shipping trade economy";

        $beritaList = $this->apiService->ambilBeritaGlobal($keywordBerita, $gnewsKey);

        if (!empty($beritaList)) {
            // Hapus cache berita lama agar database SQLite tidak bengkak
            NewsCache::where('negara_id', $negara->id)->delete();

            foreach ($beritaList as $item) {
                // Analisis sentimen menggunakan PHP Lexicon
                $sentimenData = $this->sentimentService->analisisSentimen($item['judul'] . ' ' . $item['deskripsi']);

                NewsCache::create([
                    'negara_id' => $negara->id,
                    'judul' => $item['judul'],
                    'deskripsi' => $item['deskripsi'],
                    'konten' => $item['konten'],
                    'tautan_url' => $item['tautan_url'],
                    'sumber' => $item['sumber'],
                    'diterbitkan_pada' => $item['diterbitkan_pada'],
                    'sentimen' => $sentimenData['sentimen'],
                    'skor_sentimen_positif' => $sentimenData['skor_positif'],
                    'skor_sentimen_negatif' => $sentimenData['skor_negatif']
                ]);
            }
        }

        // Hitung ulang nilai risiko menggunakan Weighted Risk Model
        $this->riskService->hitungRisikoNegara($negara);
    }
}
