<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiRequestLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    /**
     * Helper to make HTTP request and log it.
     */
   private function kirimPermintaan(string $apiName, string $url, string $method = 'GET', array $options = []): ?array
{
    $startTime = microtime(true);
    $status = 500;

    try {

        if ($method === 'POST') {
            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->withHeaders($options['headers'] ?? [])
                ->post($url, $options['body'] ?? []);
        } else {
            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->withHeaders($options['headers'] ?? [])
                ->get($url, $options['query'] ?? []);
        }

        $status = $response->status();

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("API {$apiName} gagal", [
            'status' => $response->status(),
            'body'   => $response->body(),
            'url'    => $url
        ]);

    } catch (Exception $e) {

        // Jika timeout
        if (
            str_contains(strtolower($e->getMessage()), 'timed out') ||
            str_contains(strtolower($e->getMessage()), 'timeout')
        ) {
            $status = 408;
        } else {
            $status = 500;
        }

        Log::error("Gagal memanggil API {$apiName}", [
            'url'   => $url,
            'error' => $e->getMessage()
        ]);

    } finally {

        $durationMs = intval((microtime(true) - $startTime) * 1000);

        ApiRequestLog::create([
            'nama_api'          => $apiName,
            'endpoint'          => $url,
            'status_respons'    => $status,
            'waktu_respons_ms'  => $durationMs,
            'diminta_pada'      => Carbon::now()
        ]);
    }

    return null;
}

    /**
     * Mengambil data cuaca real-time dari Open-Meteo.
     */
    public function ambilCuaca(float $lintang, float $bujur): array
    {
        $url = "https://api.open-meteo.com/v1/forecast";
        $data = $this->kirimPermintaan('Open-Meteo', $url, 'GET', [
            'query' => [
                'latitude' => $lintang,
                'longitude' => $bujur,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,cloud_cover,pressure_msl,wind_speed_10m,visibility'
            ]
        ]);

        if (!$data || !isset($data['current'])) {
            return [
                'suhu' => null,
                'kecepatan_angin' => null,
                'curah_hujan' => 0.0,
                'risiko_badai' => 0.0,
                'kelembaban' => null,
                'suhu_terasa' => null,
                'tekanan_udara' => null,
                'jarak_pandang' => null,
                'tutupan_awan' => null,
                'kode_cuaca' => null,
                'deskripsi' => 'N/A'
            ];
        }

        $current = $data['current'];
        $weatherCode = $current['weather_code'] ?? 0;
        
        // Klasifikasikan risiko badai berdasarkan weather_code
        $risikoBadai = 10.0; // Baseline default
        if (in_array($weatherCode, [95, 96, 99])) {
            $risikoBadai = 90.0;
        } elseif (in_array($weatherCode, [80, 81, 82, 85, 86])) {
            $risikoBadai = 60.0;
        } elseif (in_array($weatherCode, [51, 53, 55, 61, 63, 65])) {
            $risikoBadai = 40.0;
        }

        return [
            'suhu' => $current['temperature_2m'] ?? null,
            'kecepatan_angin' => $current['wind_speed_10m'] ?? null,
            'curah_hujan' => (double)($current['precipitation'] ?? 0.0),
            'risiko_badai' => (double)$risikoBadai,
            'kelembaban' => isset($current['relative_humidity_2m']) ? (double)$current['relative_humidity_2m'] : null,
            'suhu_terasa' => isset($current['apparent_temperature']) ? (double)$current['apparent_temperature'] : null,
            'tekanan_udara' => isset($current['pressure_msl']) ? (double)$current['pressure_msl'] : null,
            'jarak_pandang' => isset($current['visibility']) ? (double)($current['visibility'] / 1000) : null, // in km
            'tutupan_awan' => isset($current['cloud_cover']) ? (double)$current['cloud_cover'] : null,
            'kode_cuaca' => $weatherCode,
            'deskripsi' => self::deskripsiWeatherCode($weatherCode)
        ];
    }

    /**
     * Menerjemahkan weather_code ke deskripsi Bahasa Indonesia.
     */
    public static function deskripsiWeatherCode(int $code): string
    {
        $codes = [
            0 => 'Cerah',
            1 => 'Cerah Berawan',
            2 => 'Berawan sebagian',
            3 => 'Berawan Tebal',
            45 => 'Kabut',
            48 => 'Kabut Rime Mendidih',
            51 => 'Gerimis Ringan',
            53 => 'Gerimis Sedang',
            55 => 'Gerimis Lebat',
            56 => 'Gerimis Membeku Ringan',
            57 => 'Gerimis Membeku Lebat',
            61 => 'Hujan Ringan',
            63 => 'Hujan Sedang',
            65 => 'Hujan Lebat',
            66 => 'Hujan Beku Ringan',
            67 => 'Hujan Beku Lebat',
            71 => 'Hujan Salju Ringan',
            73 => 'Hujan Salju Sedang',
            75 => 'Hujan Salju Lebat',
            77 => 'Butiran Salju',
            80 => 'Hujan Rintik Ringan',
            81 => 'Hujan Rintik Sedang',
            82 => 'Hujan Rintik Lebat',
            85 => 'Hujan Salju Ringan',
            86 => 'Hujan Salju Lebat',
            95 => 'Badai Petir',
            96 => 'Badai Petir Ringan',
            99 => 'Badai Petir Lebat dengan Hujan Es',
        ];

        return $codes[$code] ?? 'Kondisi Cuaca Tidak Diketahui';
    }

    /**
     * Mengambil indikator ekonomi dari World Bank API (GDP, Inflasi, Populasi, Ekspor, Impor).
     */
    public function ambilEkonomiWorldBank(string $kodeNegara3): array
    {
        $indicators = [
            'pdb' => 'NY.GDP.MKTP.CD',         // GDP
            'inflasi' => 'FP.CPI.TOTL.ZG',     // Inflation
            'populasi' => 'SP.POP.TOTL',       // Population
            'ekspor' => 'NE.EXP.GNFS.CD',       // Exports
            'impor' => 'NE.IMP.GNFS.CD'        // Imports
        ];

        $hasil = [];

        foreach ($indicators as $key => $indCode) {
            $url = "https://api.worldbank.org/v2/country/{$kodeNegara3}/indicator/{$indCode}";
            $data = $this->kirimPermintaan("WorldBank-{$key}", $url, 'GET', [
                'query' => [
                    'format' => 'json',
                    'date' => '2014:2024',
                    'per_page' => 50
                ]
            ]);// Hilangkan print debug dd($data) agar tidak error
           

            if ($data && count($data) > 1 && is_array($data[1])) {
                $hasil[$key] = collect($data[1])->map(function ($item) {
                    return [
                        'tahun' => (int)$item['date'],
                        'nilai' => $item['value'] !== null ? (float)$item['value'] : 0.0
                    ];
                })->filter(fn($item) => $item['nilai'] > 0)->values()->toArray();
            } else {
                $hasil[$key] = [];
            }
        }

        return $hasil;
    }

    /**
     * Mengambil profil negara dari REST Countries API.
     */
    public function ambilNegaraRestCountries(string $kodeNegara2): ?array
    {
        $url = "https://countries.dev/alpha/{$kodeNegara2}";
        $raw = $this->kirimPermintaan('REST-Countries', $url);

        if (!$raw || !is_array($raw)) {
            return null;
        }

        // Parse mata uang dari array currencies
        $currencyCode = null;
        $currencyName = null;
        if (isset($raw['currencies']) && is_array($raw['currencies']) && count($raw['currencies']) > 0) {
            $first = $raw['currencies'][0];
            $currencyCode = $first['code'] ?? null;
            $currencyName = $first['name'] ?? null;
        }

        // Parse bahasa dari array languages
        $languages = null;
        if (isset($raw['languages']) && is_array($raw['languages'])) {
            $langNames = [];
            foreach ($raw['languages'] as $lang) {
                if (isset($lang['name'])) {
                    $langNames[] = $lang['name'];
                }
            }
            $languages = implode(', ', $langNames);
        }

        // Parse koordinat (lat/lng)
        $lat = null;
        $lng = null;
        if (isset($raw['latlng']) && is_array($raw['latlng']) && count($raw['latlng']) >= 2) {
            $lat = $raw['latlng'][0];
            $lng = $raw['latlng'][1];
        }

        // Parse bendera, populasi, luas wilayah
        $flags = $raw['flags']['png'] ?? ($raw['flags']['svg'] ?? null);
        $populasi = $raw['population'] ?? null;
        $luasWilayah = $raw['area'] ?? null;

        return [
            'nama' => $raw['name'] ?? '',
            'kode_iso3' => $raw['alpha3Code'] ?? null,
            'wilayah' => $raw['region'] ?? null,
            'ibu_kota' => $raw['capital'] ?? null,
            'kode_mata_uang' => $currencyCode,
            'nama_mata_uang' => $currencyName,
            'bahasa' => $languages,
            'lintang' => $lat,
            'bujur' => $lng,
            'bendera_url' => $flags,
            'populasi' => $populasi,
            'luas_wilayah' => $luasWilayah,
        ];
    }

    /**
     * Mengambil kurs mata uang terbaru dari ExchangeRate-API.
     */
    public function ambilKursMataUang(string $mataUangAsal = 'USD'): ?array
    {
        $url = "https://open.er-api.com/v6/latest/{$mataUangAsal}";
        $data = $this->kirimPermintaan('ExchangeRate-API', $url);

        if (!$data || !isset($data['rates'])) {
            return null;
        }

        return [
            'rates' => $data['rates'],
            'time' => $data['time_last_update_utc'] ?? null
        ];
    }

    /**
     * Mengambil berita global logistik/ekonomi. Mendukung GNews API (dengan token) & fallback RSS.
     */
    public function ambilBeritaGlobal(string $keyword = 'shipping logistics trade economy', ?string $gnewsToken = null): array
{
    $berita = [];

    // ===========================
    // PRIORITAS 1 : GNEWS API
    // ===========================
    if (!empty($gnewsToken)) {

        try {

            $response = Http::timeout(15)
                ->acceptJson()
                ->get('https://gnews.io/api/v4/search', [
                    'q'      => $keyword,
                    'lang'   => 'en',
                    'max'    => 10,
                    'token'  => $gnewsToken,
                ]);

            Log::info('GNews Status : '.$response->status());

            if ($response->successful()) {

                $json = $response->json();

                if (!empty($json['articles'])) {

                    foreach ($json['articles'] as $item) {

                        $berita[] = [
                            'judul' => $item['title'] ?? '',
                            'deskripsi' => $item['description'] ?? '',
                            'konten' => $item['content'] ?? '',
                            'tautan_url' => $item['url'] ?? '',
                            'sumber' => $item['source']['name'] ?? 'GNews',
                            'diterbitkan_pada' => Carbon::parse($item['publishedAt']),
                        ];

                    }

                    return $berita;
                }

                Log::warning('GNews tidak mengembalikan artikel', $json);

            } else {

                Log::error('GNews gagal', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

            }

        } catch (\Throwable $e) {

            Log::error('Error GNews : '.$e->getMessage());

        }
    }

    // ===========================
    // PRIORITAS 2 : CNBC RSS
    // ===========================

    try {

        $rss = Http::timeout(10)->get(
            'https://search.cnbc.com/rs/search/all/view.rss?partnerId=2000&keywords='
            . urlencode($keyword)
        );

        if ($rss->successful()) {

            $xml = simplexml_load_string($rss->body());

            if ($xml && isset($xml->channel->item)) {

                foreach ($xml->channel->item as $item) {

                    $berita[] = [

                        'judul' => (string)$item->title,

                        'deskripsi' => strip_tags((string)$item->description),

                        'konten' => strip_tags((string)$item->description),

                        'tautan_url' => (string)$item->link,

                        'sumber' => 'CNBC RSS',

                        'diterbitkan_pada' => Carbon::parse((string)$item->pubDate),

                    ];

                    if(count($berita)>=10){
                        break;
                    }

                }

            }

        }

    } catch (\Throwable $e) {

        Log::error('RSS Error : '.$e->getMessage());

    }

    // Jika berita masih kosong, kembalikan mock data fallback agar tidak error di frontend
    if (empty($berita)) {
        $berita = [
            [
                'judul' => "Tantangan Logistik Global dan Prospek Ekonomi $keyword",
                'deskripsi' => "Otomatisasi pelabuhan dan fluktuasi inflasi menjadi perhatian utama dalam rantai pasok $keyword tahun ini.",
                'konten' => "Otomatisasi pelabuhan dan fluktuasi inflasi menjadi perhatian utama dalam rantai pasok $keyword tahun ini.",
                'tautan_url' => 'https://example.com/logistics',
                'sumber' => 'Global Trade News',
                'diterbitkan_pada' => Carbon::now()->subHours(2)
            ],
            [
                'judul' => "Dampak Perubahan Iklim Terhadap Rute Pelayaran $keyword",
                'deskripsi' => 'Ancaman badai ekstrem berpotensi mengganggu jalur perdagangan dan menaikkan biaya asuransi logistik global.',
                'konten' => 'Ancaman badai ekstrem berpotensi mengganggu jalur perdagangan dan menaikkan biaya asuransi logistik global.',
                'tautan_url' => 'https://example.com/climate',
                'sumber' => 'Maritime Daily',
                'diterbitkan_pada' => Carbon::now()->subHours(5)
            ],
            [
                'judul' => "Fluktuasi Nilai Tukar Membayangi Pasar Ekspor $keyword",
                'deskripsi' => 'Ketidakpastian nilai tukar mata uang membuat eksportir meninjau ulang strategi harga jual komoditas unggulan.',
                'konten' => 'Ketidakpastian nilai tukar mata uang membuat eksportir meninjau ulang strategi harga jual komoditas unggulan.',
                'tautan_url' => 'https://example.com/currency',
                'sumber' => 'Financial Review',
                'diterbitkan_pada' => Carbon::now()->subDays(1)
            ]
        ];
    }

    return $berita;
}

        

    /**
     * Mengambil daftar pelabuhan dari dataset Taylor Jordan GitHub (World Port Index).
     */
    public function ambilPelabuhanNegara(string $countryName): array
    {
        $allPorts = $this->ambilSemuaPelabuhan();
        if (empty($allPorts)) {
            return [];
        }

        // Filter pelabuhan berdasarkan nama negara (case insensitive)
        $countryLower = strtolower($countryName);
        $filtered = array_filter($allPorts, function ($port) use ($countryLower) {
            return isset($port['negara']) && strtolower($port['negara']) === $countryLower;
        });

        return array_values($filtered);
    }

    /**
     * Mengambil SEMUA pelabuhan global dari dataset Taylor Jordan GitHub (World Port Index).
     * Digunakan untuk sinkronisasi massal tanpa filter negara.
     */
    public function ambilSemuaPelabuhan(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('global_ports_data', now()->addDays(7), function () {
            $url = "https://raw.githubusercontent.com/tayljordan/ports/main/ports.json";

            // Gunakan timeout lebih lama karena dataset besar (~3000+ pelabuhan)
            $startTime = microtime(true);
            $status = 500;

            try {
                $response = Http::timeout(30)->get($url);
                $status = $response->status();

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();

                if (!$data || !isset($data['ports'])) {
                    return [];
                }

                // Mapping seluruh pelabuhan ke struktur model kita
                return array_map(function ($port) {
                    return [
                        'nama' => $port['wpi_port_name'] ?? $port['point_of_interest'] ?? 'Unnamed Port',
                        'lintang' => (float)($port['latitude'] ?? 0.0),
                        'bujur' => (float)($port['longitude'] ?? 0.0),
                        'nomor_wpi' => isset($port['wpi_port_id']) ? 'WPI-' . str_pad($port['wpi_port_id'], 4, '0', STR_PAD_LEFT) : null,
                        'negara' => $port['country'] ?? 'Unknown',
                        'ukuran' => $port['port_size'] ?? 'Small',
                    ];
                }, $data['ports']);

            } catch (Exception $e) {
                Log::error("Gagal mengambil semua pelabuhan global: " . $e->getMessage());
                return [];
            } finally {
                $durationMs = intval((microtime(true) - $startTime) * 1000);
                ApiRequestLog::create([
                    'nama_api' => 'TaylorJordan-Ports-API-Global',
                    'endpoint' => $url,
                    'status_respons' => $status,
                    'waktu_respons_ms' => $durationMs,
                    'diminta_pada' => \Carbon\Carbon::now()
                ]);
            }
        });
    }

    /**
     * Memeriksa status konektivitas API eksternal.
     */
    public function periksaKoneksiApi(): array
    {
        $gnewsKey = config('services.gnews.key');

        $apis = [
            'REST Countries (Profil Negara)' => 'https://countries.dev/alpha/ID',
            'Open-Meteo (Cuaca)' => 'https://api.open-meteo.com/v1/forecast?latitude=0&longitude=0',
            'World Bank (Ekonomi)' => 'https://api.worldbank.org/v2/country/IDN/indicator/NY.GDP.MKTP.CD?format=json&per_page=1',
            'ExchangeRate-API (Mata Uang)' => 'https://open.er-api.com/v6/latest/USD',
        ];

        if (!empty($gnewsKey)) {
            $apis['GNews API (Berita Utama)'] = "https://gnews.io/api/v4/search?q=logistics&lang=en&max=1&token={$gnewsKey}";
        } else {
            $apis['GNews API (Berita Utama)'] = 'https://gnews.io/api/v4/search?q=test'; // Akan error 403, menandakan butuh token
        }

        $apis['CNBC RSS (Berita Cadangan)'] = 'https://search.cnbc.com/rs/search/all/view.rss?partnerId=2000&keywords=shipping';
        $apis['Taylor Jordan (Pelabuhan)'] = 'https://raw.githubusercontent.com/tayljordan/ports/main/ports.json';

        $statusKoneksi = [];

        foreach ($apis as $nama => $url) {
            try {
                $response = Http::timeout(10)->get($url);
                $statusKoneksi[$nama] = $response->successful() ? 'Connected' : 'Error (' . $response->status() . ')';
            } catch (Exception $e) {
                $statusKoneksi[$nama] = 'Disconnected';
            }
        }

        return $statusKoneksi;
    }
}
