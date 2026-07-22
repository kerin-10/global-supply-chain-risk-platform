<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Models\Article;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Models\CurrencyRate;
use App\Models\Watchlist;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    

    /**
     * Halaman utama dashboard – kartu ringkasan global.
     */
    public function index()
    {
        $negaraList  = Country::with('currentRiskScore')->get();
        $watchlist   = Auth::user()->favoriteCountries()->with('currentRiskScore')->get();
        $totalBerita = NewsCache::count();
        $totalRisiko = RiskScore::whereIn('tingkat_risiko', ['Sedang','Tinggi'])->count();

        $stats = [
            'total_negara'   => $negaraList->count(),
            'risiko_tinggi'  => RiskScore::where('tingkat_risiko', 'Tinggi')->count(),
            'risiko_sedang'  => RiskScore::where('tingkat_risiko', 'Sedang')->count(),
            'risiko_rendah'  => RiskScore::where('tingkat_risiko', 'Rendah')->count(),
            'total_berita'   => $totalBerita,
            'total_pelabuhan'=> Port::count(),
        ];

        return view('dashboard.index', compact('negaraList', 'watchlist', 'stats'));
    }

    /**
     * Halaman peta cuaca global.
     */
    public function weather()
    {
        $negaraList = Country::with('currentRiskScore')
            ->whereNotNull('lintang')
            ->whereNotNull('bujur')
            ->get();

        return view('dashboard.weather', compact('negaraList'));
    }

    /**
     * Halaman dashboard pelabuhan global (Peta + Kemacetan).
     */
    public function ports()
    {
        $pelabuhanList = Port::with(['latestCongestion', 'country'])->get();
        $negaraList    = Country::orderBy('nama')->get();

        return view('dashboard.ports', compact('pelabuhanList', 'negaraList'));
    }

    /**
     * Halaman nilai tukar mata uang & grafik tren.
     */
    public function currency()
    {
        $negaraList   = Country::orderBy('nama')->get();
        $ratesCache   = CurrencyRate::where('mata_uang_asal', 'USD')->get();

        return view('dashboard.currency', compact('negaraList', 'ratesCache'));
    }

    /**
     * Halaman intelijen berita & analisis sentimen.
     */
    public function news()
{
    $negaraList = Country::orderBy('nama')->get();

    $beritaTerbaru = NewsCache::with('country')
        ->orderBy('diterbitkan_pada', 'desc')
        ->paginate(20);

    $jumlahPositif = NewsCache::where('sentimen', 'Positif')->count();

    $jumlahNetral = NewsCache::where('sentimen', 'Netral')->count();

    $jumlahNegatif = NewsCache::where('sentimen', 'Negatif')->count();

    return view('dashboard.news', compact(
        'negaraList',
        'beritaTerbaru',
        'jumlahPositif',
        'jumlahNetral',
        'jumlahNegatif'
    ));
}

    /**
     * Halaman perbandingan dua negara.
     */
    public function compare()
    {
        $negaraList = Country::with('currentRiskScore')->orderBy('nama')->get();
        return view('dashboard.compare', compact('negaraList'));
    }

    /**
     * Halaman visualisasi grafik data.
     */
    public function visualization()
{
    $negaraList = Country::with([
        'economicHistories',
        'currentRiskScore'
    ])->orderBy('nama')->get();



    $firstCountry = $negaraList->first();

    $years = [];
    $gdp = [];
    $inflasi = [];

    if ($firstCountry) {
        foreach ($firstCountry->economicHistories as $row) {
            $years[] = $row->tahun;
            $gdp[] = $row->pdb;
            $inflasi[] = $row->inflasi;
        }
    }

    return view('dashboard.visualization', compact(
        'negaraList',
        'years',
        'gdp',
        'inflasi',
        'firstCountry'
    ));
}

    /**
     * Halaman daftar pantau (watchlist / favorit negara pengguna).
     */
    public function watchlist()
    {
        $watchlist = Auth::user()->favoriteCountries()
            ->with('currentRiskScore')
            ->get();

        $negaraList = Country::with('currentRiskScore')->orderBy('nama')->get();

        return view('dashboard.watchlist', compact('watchlist', 'negaraList'));
    }

    /**
     * Toggle watchlist (tambah/hapus negara favorit).
     */
    public function toggleWatchlist(Request $request)
    {
        $request->validate(['negara_id' => 'required|integer|exists:negara,id']);

        $pengguna  = Auth::user();
        $negaraId = $request->negara_id;

        $existing = Watchlist::where('pengguna_id', $pengguna->id)
                             ->where('negara_id', $negaraId)
                             ->first();

        if ($existing) {
            $existing->delete();
            $aksi = 'dihapus';
        } else {
            Watchlist::create([
                'pengguna_id' => $pengguna->id,
                'negara_id'   => $negaraId,
            ]);
            $aksi = 'ditambahkan';
        }

        return response()->json(['status' => 'sukses', 'aksi' => $aksi]);
    }

    /**
     * Halaman artikel analisis global.
     */
    public function articles()
    {
        $artikelList = Article::with('author')
            ->where('status', 'Published')
            ->orderBy('diterbitkan_pada', 'desc')
            ->paginate(6);

        return view('articles.index', compact('artikelList'));
    }

    /**
     * Detail artikel analisis.
     */
    public function articleDetail($id)
    {
        $artikel = Article::with('author')
            ->where('status', 'Published')
            ->findOrFail($id);

        $artikelLainnya = Article::with('author')
            ->where('status', 'Published')
            ->where('id', '!=', $id)
            ->orderBy('diterbitkan_pada', 'desc')
            ->take(3)->get();

        return view('articles.show', compact('artikel', 'artikelLainnya'));
    }
    public function countryDetail($id)
{
    $negara = Country::with([
        'currentRiskScore',
        'economicHistories',
        'ports',
        'newsCaches'
    ])->findOrFail($id);

    return view('dashboard.country-detail', compact('negara'));
}
}
