<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Services\ExternalApiService;

class SyncCountryEconomyCommand extends Command
{
    protected $signature = 'countries:economy';

    protected $description = 'Sinkronisasi data ekonomi seluruh negara dari World Bank';

    protected ExternalApiService $api;

    public function __construct(ExternalApiService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle()
    {
        $countries = Country::whereNotNull('kode_iso3')->get();

        foreach ($countries as $country) {

            $this->info("Sync {$country->nama}");

            $economy = $this->api->ambilEkonomiWorldBank($country->kode_iso3);
            if ($country->kode_iso3 == 'IDN') {
               
            }

            if (!$economy) {
            $this->warn("Gagal mengambil data: {$country->nama}");
            continue;
            }

            if (!empty($economy['pdb'])) {
                $item = collect($economy['pdb'])
            ->sortByDesc('tahun')
            ->first();

                $country->pdb = $item['nilai'] ?? $country->pdb;
            }

            if (!empty($economy['inflasi'])) {
               $item = collect($economy['inflasi'])
                ->sortByDesc('tahun')
                ->first();

                $country->inflasi = $item['nilai'] ?? $country->inflasi;
            }

            if (!empty($economy['populasi'])) {
                $item = collect($economy['populasi'])
                ->sortByDesc('tahun')
                ->first();

                $country->populasi = $item['nilai'] ?? $country->populasi;
            }

            if (!empty($economy['ekspor'])) {
                $item = collect($economy['ekspor'])
                    ->sortByDesc('tahun')
                    ->first();

                $country->nilai_ekspor = $item['nilai'] ?? $country->nilai_ekspor;
            }

            if (!empty($economy['impor'])) {
                $item = collect($economy['impor'])
                    ->sortByDesc('tahun')
                    ->first();

                $country->nilai_impor = $item['nilai'] ?? $country->nilai_impor;
            }

            $country->save();
            
            $this->info("Berhasil: {$country->nama}");
        }

        $this->info("Selesai.");
    }
}