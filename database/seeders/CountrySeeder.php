<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Country;
use App\Models\Port;
use App\Models\PortCongestion;
use App\Models\RiskScore;
use Carbon\Carbon;
use App\Services\ExternalApiService;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai sinkronisasi 250 Negara dari REST Countries API...');

        // 1. Ambil 250 Negara
        $response = Http::withoutVerifying()->timeout(30)->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');
        if ($response->successful()) {
            $countries = $response->json();
            $count = 0;
            foreach ($countries as $c) {
                if (empty($c['cca2'])) continue;
                
                $currencyCode = null;
                $currencyName = null;
                if (!empty($c['currencies'])) {
                    $firstCurr = reset($c['currencies']);
                    $currencyCode = key($c['currencies']);
                    $currencyName = $firstCurr['name'] ?? null;
                }

                $languages = null;
                if (!empty($c['languages'])) {
                    $languages = implode(', ', array_values($c['languages']));
                }

                $lat = $c['latlng'][0] ?? null;
                $lng = $c['latlng'][1] ?? null;

                $countryModel = Country::updateOrCreate(
                    ['kode_iso2' => $c['cca2']],
                    [
                        'nama' => $c['name']['common'] ?? '',
                        'kode_iso3' => $c['cca3'] ?? null,
                        'wilayah' => $c['region'] ?? null,
                        'ibu_kota' => !empty($c['capital']) ? $c['capital'][0] : null,
                        'kode_mata_uang' => $currencyCode,
                        'nama_mata_uang' => $currencyName,
                        'bahasa' => $languages,
                        'lintang' => $lat,
                        'bujur' => $lng,
                        'bendera_url' => $c['flag'] ?? null,
                        'populasi' => $c['population'] ?? 0,
                        'luas_wilayah' => $c['area'] ?? 0,
                    ]
                );

                // Seed dummy risk score if not exists
                RiskScore::firstOrCreate(
                    ['negara_id' => $countryModel->id],
                    [
                        'risiko_cuaca' => rand(10, 50),
                        'risiko_inflasi' => rand(10, 50),
                        'risiko_nilai_tukar' => rand(10, 50),
                        'risiko_sentimen_berita' => rand(10, 50),
                        'total_risiko' => rand(20, 60),
                        'tingkat_risiko' => 'Sedang'
                    ]
                );
                
                $count++;
            }
            $this->command->info("Berhasil menyimpan $count negara.");
        } else {
            $this->command->error('Gagal mengambil data dari REST Countries API.');
        }

        // 2. Ambil Ribuan Pelabuhan (Tanpa perlu klik di UI)
        $this->command->info('Memulai sinkronisasi Pelabuhan Global dari Taylor Jordan API...');
        
        // Panggil fungsi sinkronisasi yang sama dengan di controller
        $countryController = app(\App\Http\Controllers\CountryApiController::class);
        $countryController->syncGlobalPorts(new \Illuminate\Http\Request());
        
        $this->command->info('Sinkronisasi Pelabuhan Global selesai!');
    }
}