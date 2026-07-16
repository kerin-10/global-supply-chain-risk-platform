<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExternalApiService;
use App\Models\Country;
use Illuminate\Support\Facades\Http;

class SyncCountriesCommand extends Command
{
    protected $signature = 'countries:sync';

    protected $description = 'Sinkronisasi seluruh negara dari REST Countries API';

    public function handle()
    {
        $this->info('Mengambil data negara...');

        $response = Http::get(
        'https://countries.dev/countries'
        );

      

        if (!$response->successful()) {
            $this->error('Gagal mengambil data REST Countries');
            return;
        }

      

        $countries = $response->json();



        foreach ($countries as $country) {

    $currencyCode = null;
    $currencyName = null;

    if (!empty($country['currencies'])) {
        $currency = $country['currencies'][0];

        $currencyCode = $currency['code'] ?? null;
        $currencyName = $currency['name'] ?? null;
    }

    $language = null;

    if (!empty($country['languages'])) {
        $language = implode(', ', array_column($country['languages'], 'name'));
    }

    Country::updateOrCreate(

        [
            'kode_iso2' => $country['alpha2Code']
        ],

        [

            'nama' => $country['name'] ?? null,

            'kode_iso3' => $country['alpha3Code'] ?? null,

            'wilayah' => $country['region'] ?? null,

            'ibu_kota' => $country['capital'] ?? null,

            'kode_mata_uang' => $currencyCode,

            'nama_mata_uang' => $currencyName,

            'bahasa' => $language,

            'populasi' => $country['population'] ?? 0,

            'lintang' => $country['latlng'][0] ?? null,

            'bujur' => $country['latlng'][1] ?? null,

        ]

    );
}
        $this->info('Sinkronisasi selesai.');
    }
}