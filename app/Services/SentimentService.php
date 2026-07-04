<?php

namespace App\Services;

use App\Models\PositiveWord;
use App\Models\NegativeWord;

class SentimentService
{
    /**
     * Jalankan analisis sentimen berbasis leksikon pada teks.
     */
    public function analisisSentimen(?string $teks): array
    {
        if (empty($teks)) {
            return [
                'sentimen' => 'Netral',
                'skor_positif' => 0,
                'skor_negatif' => 0,
                'persen_positif' => 0,
                'persen_negatif' => 0,
                'persen_netral' => 100,
                'kata_positif_ditemukan' => [],
                'kata_negatif_ditemukan' => []
            ];
        }

        // Ambil leksikon kata dari database
        $kataPositif = PositiveWord::pluck('kata')->map('strtolower')->toArray();
        $kataNegatif = NegativeWord::pluck('kata')->map('strtolower')->toArray();

        // Bersihkan teks: ubah ke huruf kecil dan hapus tanda baca
        $teksBersih = strtolower(preg_replace('/[^a-zA-Z0-9\s-]/', '', $teks));
        $daftarKata = preg_split('/\s+/', $teksBersih);

        $skorPositif = 0;
        $skorNegatif = 0;
        $kataPositifDitemukan = [];
        $kataNegatifDitemukan = [];

        foreach ($daftarKata as $kata) {
            $kata = trim($kata);
            if (empty($kata)) continue;

            if (in_array($kata, $kataPositif)) {
                $skorPositif++;
                $kataPositifDitemukan[] = $kata;
            }
            if (in_array($kata, $kataNegatif)) {
                $skorNegatif++;
                $kataNegatifDitemukan[] = $kata;
            }
        }

        $totalKecocokan = $skorPositif + $skorNegatif;
        $persenPositif = 0;
        $persenNegatif = 0;
        $persenNetral = 100;

        if ($totalKecocokan > 0) {
            $persenPositif = intval(($skorPositif / $totalKecocokan) * 100);
            $persenNegatif = intval(($skorNegatif / $totalKecocokan) * 100);
            $persenNetral = 0;

            if ($skorPositif > $skorNegatif) {
                $sentimen = 'Positif';
            } elseif ($skorNegatif > $skorPositif) {
                $sentimen = 'Negatif';
            } else {
                $sentimen = 'Netral';
                $persenNetral = 100;
            }
        } else {
            $sentimen = 'Netral';
        }

        return [
            'sentimen' => $sentimen,
            'skor_positif' => $skorPositif,
            'skor_negatif' => $skorNegatif,
            'persen_positif' => $persenPositif,
            'persen_negatif' => $persenNegatif,
            'persen_netral' => $persenNetral,
            'kata_positif_ditemukan' => array_values(array_unique($kataPositifDitemukan)),
            'kata_negatif_ditemukan' => array_values(array_unique($kataNegatifDitemukan))
        ];
    }
}
