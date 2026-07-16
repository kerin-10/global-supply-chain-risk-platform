<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Country;
use App\Models\Port;
use App\Models\PortCongestion;
use App\Models\Article;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use App\Models\SystemSetting;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{

    /**
     * Halaman utama Admin Dashboard.
     */
    public function index()
    {
        $stats = [
            'total_pengguna'  => User::count(),
            'total_negara'    => Country::count(),
            'total_pelabuhan' => Port::count(),
            'total_artikel'   => Article::count(),
            'kata_positif'    => PositiveWord::count(),
            'kata_negatif'    => NegativeWord::count(),
            'log_api'         => ApiRequestLog::count(),
        ];

        $logTerbaru = ApiRequestLog::orderBy('diminta_pada', 'desc')->take(10)->get();
        $pengaturan = SystemSetting::all();

        // Validasi koneksi API eksternal
        $apiService = app(\App\Services\ExternalApiService::class);
        $statusKoneksi = $apiService->periksaKoneksiApi();

        return view('admin.index', compact('stats', 'logTerbaru', 'pengaturan', 'statusKoneksi'));
    }

    // =============================================
    //  MANAJEMEN PENGGUNA
    // =============================================

    public function penggunaDaftar()
    {
        $daftar = User::with('profile')->orderBy('created_at', 'desc')->get();
        return view('admin.pengguna.daftar', compact('daftar'));
    }

    public function penggunaSimpan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna',
            'kata_sandi' => 'required|string|min:6',
            'peran' => 'required|in:admin,user',
            'departemen' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'peran' => $request->peran,
        ]);

        UserProfile::create([
            'pengguna_id' => $user->id,
            'departemen' => $request->departemen,
        ]);

        return back()->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    public function penggunaUpdate(Request $request, $id)
    {
        $pengguna = User::findOrFail($id);
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email,'.$pengguna->id,
            'peran' => 'required|in:admin,user',
            'departemen' => 'nullable|string|max:255',
        ]);

        $pengguna->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'peran' => $request->peran,
        ]);

        if ($request->filled('kata_sandi')) {
            $pengguna->update(['kata_sandi' => Hash::make($request->kata_sandi)]);
        }

        UserProfile::updateOrCreate(
            ['pengguna_id' => $pengguna->id],
            ['departemen' => $request->departemen]
        );

        return back()->with('sukses', 'Pengguna berhasil diperbarui.');
    }

    public function penggunaHapus($id)
    {
        $pengguna = User::findOrFail($id);
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }
        $pengguna->delete();
        return back()->with('sukses', 'Pengguna berhasil dihapus.');
    }

    public function penggunaUbahPeran(Request $request, $id)
    {
        $pengguna = User::findOrFail($id);
        $pengguna->update(['peran' => $request->peran]);
        return back()->with('sukses', 'Peran pengguna diperbarui menjadi ' . $request->peran . '.');
    }

    // =============================================
    //  MANAJEMEN PELABUHAN
    // =============================================

    public function pelabuhanDaftar()
    {
        $daftar = Port::with(['country', 'latestCongestion'])->orderBy('nama')->get();
        $negaraList = Country::orderBy('nama')->get();
        return view('admin.pelabuhan.daftar', compact('daftar', 'negaraList'));
    }

    public function pelabuhanSimpan(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:200',
            'lintang'    => 'required|numeric',
            'bujur'      => 'required|numeric',
            'negara_id'  => 'required|exists:negara,id',
        ]);

        $negara = Country::findOrFail($request->negara_id);

        Port::create([
            'nama'          => $request->nama,
            'kode_pelabuhan'=> $request->kode_pelabuhan,
            'lintang'       => $request->lintang,
            'bujur'         => $request->bujur,
            'negara_id'     => $negara->id,
            'kode_negara'   => $negara->kode_iso2,
            'wilayah'       => $request->wilayah ?? $negara->wilayah,
            'nomor_wpi'     => $request->nomor_wpi,
        ]);

        return back()->with('sukses', 'Pelabuhan berhasil ditambahkan.');
    }

    public function pelabuhanUpdate(Request $request, $id)
    {
        $request->validate([
            'nama'       => 'required|string|max:200',
            'lintang'    => 'required|numeric',
            'bujur'      => 'required|numeric',
            'negara_id'  => 'required|exists:negara,id',
        ]);

        $pelabuhan = Port::findOrFail($id);
        $negara = Country::findOrFail($request->negara_id);

        $pelabuhan->update([
            'nama'          => $request->nama,
            'kode_pelabuhan'=> $request->kode_pelabuhan,
            'lintang'       => $request->lintang,
            'bujur'         => $request->bujur,
            'negara_id'     => $negara->id,
            'kode_negara'   => $negara->kode_iso2,
            'wilayah'       => $request->wilayah ?? $negara->wilayah,
            'nomor_wpi'     => $request->nomor_wpi,
        ]);

        return back()->with('sukses', 'Pelabuhan berhasil diperbarui.');
    }

    public function pelabuhanHapus($id)
    {
        Port::findOrFail($id)->delete();
        return back()->with('sukses', 'Pelabuhan berhasil dihapus.');
    }

    // =============================================
    //  MANAJEMEN ARTIKEL ANALISIS
    // =============================================

    public function artikelDaftar()
    {
        $daftar = Article::with('author')->orderBy('created_at', 'desc')->get();
        return view('admin.artikel.daftar', compact('daftar'));
    }

    public function artikelBuat()
    {
        return view('admin.artikel.buat');
    }

    public function artikelSimpan(Request $request)
    {
        $request->validate([
            'judul'      => 'required|string|max:255',
            'kategori'   => 'nullable|string|max:100',
            'gambar_url' => 'nullable|url',
            'ringkasan'  => 'required|string',
            'konten'     => 'required|string',
            'status'     => 'required|in:Draft,Published',
        ]);

        Article::create([
            'judul'           => $request->judul,
            'kategori'        => $request->kategori,
            'gambar_url'      => $request->gambar_url,
            'ringkasan'       => $request->ringkasan,
            'konten'          => $request->konten,
            'penulis_id'      => auth()->id(),
            'status'          => $request->status,
            'diterbitkan_pada'=> $request->status === 'Published' ? Carbon::now() : null,
        ]);

        return redirect()->route('admin.artikel.daftar')->with('sukses', 'Artikel berhasil disimpan.');
    }

    public function artikelEdit($id)
    {
        $artikel = Article::findOrFail($id);
        return view('admin.artikel.edit', compact('artikel'));
    }

    public function artikelUpdate(Request $request, $id)
    {
        $artikel = Article::findOrFail($id);
        $request->validate([
            'judul'      => 'required|string|max:255',
            'kategori'   => 'nullable|string|max:100',
            'gambar_url' => 'nullable|url',
            'ringkasan'  => 'required|string',
            'konten'     => 'required|string',
            'status'     => 'required|in:Draft,Published',
        ]);

        $diterbitkan_pada = $artikel->diterbitkan_pada;
        if ($request->status === 'Published' && !$diterbitkan_pada) {
            $diterbitkan_pada = Carbon::now();
        } elseif ($request->status === 'Draft') {
            $diterbitkan_pada = null;
        }

        $artikel->update([
            'judul'           => $request->judul,
            'kategori'        => $request->kategori,
            'gambar_url'      => $request->gambar_url,
            'ringkasan'       => $request->ringkasan,
            'konten'          => $request->konten,
            'status'          => $request->status,
            'diterbitkan_pada'=> $diterbitkan_pada,
        ]);

        return redirect()->route('admin.artikel.daftar')->with('sukses', 'Artikel berhasil diperbarui.');
    }

    public function artikelHapus($id)
    {
        Article::findOrFail($id)->delete();
        return back()->with('sukses', 'Artikel berhasil dihapus.');
    }

    // =============================================
    //  MANAJEMEN LEKSIKON SENTIMEN
    // =============================================

    public function leksikonDaftar()
    {
        $kataPositif = PositiveWord::orderBy('kata')->get();
        $kataNegatif = NegativeWord::orderBy('kata')->get();
        return view('admin.leksikon.daftar', compact('kataPositif', 'kataNegatif'));
    }

    public function leksikonTambahPositif(Request $request)
    {
        $request->validate(['kata' => 'required|string|max:100|unique:kata_positif,kata']);
        PositiveWord::create(['kata' => strtolower($request->kata)]);
        return back()->with('sukses', 'Kata positif "' . $request->kata . '" berhasil ditambahkan.');
    }

    public function leksikonTambahNegatif(Request $request)
    {
        $request->validate(['kata' => 'required|string|max:100|unique:kata_negatif,kata']);
        NegativeWord::create(['kata' => strtolower($request->kata)]);
        return back()->with('sukses', 'Kata negatif "' . $request->kata . '" berhasil ditambahkan.');
    }

    public function leksikonHapusPositif($id)
    {
        PositiveWord::findOrFail($id)->delete();
        return back()->with('sukses', 'Kata positif berhasil dihapus.');
    }

    public function leksikonHapusNegatif($id)
    {
        NegativeWord::findOrFail($id)->delete();
        return back()->with('sukses', 'Kata negatif berhasil dihapus.');
    }

    // =============================================
    //  PENGATURAN SISTEM
    // =============================================

    public function pengaturanSimpan(Request $request)
    {
        $keys = ['bobot_cuaca', 'bobot_inflasi', 'bobot_sentimen', 'bobot_nilai_tukar', 'gnews_api_key'];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                SystemSetting::setVal($key, $request->input($key));
            }
        }
        return back()->with('sukses', 'Pengaturan sistem berhasil disimpan.');
    }
}
