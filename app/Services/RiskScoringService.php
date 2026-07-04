<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\RiskScoreHistory;
use App\Models\SystemSetting;
use Carbon\Carbon;

class RiskScoringService
{
    /**
     * Menghitung skor risiko rantai pasok untuk negara tertentu.
     */
    public function hitungRisikoNegara(Country $negara): RiskScore
    {
        // 1. Hitung Risiko Cuaca (Skala 0-100)
        $suhu = $negara->cuaca_suhu;
        $curahHujan = $negara->cuaca_curah_hujan;
        $kecepatanAngin = $negara->cuaca_kecepatan_angin;
        $risikoBadai = $negara->cuaca_risiko_badai ?? 10.0;

        // Risiko Suhu
        $risikoSuhu = 0;
        if ($suhu === null) {
            $risikoSuhu = 30;
        } elseif ($suhu < 5 || $suhu > 40) {
            $risikoSuhu = 100;
        } elseif ($suhu < 15) {
            $risikoSuhu = intval(((15 - $suhu) / 10) * 100);
        } elseif ($suhu > 28) {
            $risikoSuhu = intval((($suhu - 28) / 12) * 100);
        }

        // Risiko Curah Hujan (Rain > 15mm/h dianggap risiko tinggi/badai)
        $risikoCurahHujan = $curahHujan === null ? 0 : min(100, intval(($curahHujan / 15) * 100));

        // Risiko Angin (Wind > 45km/h dianggap angin kencang/badai)
        $risikoAngin = $kecepatanAngin === null ? 0 : min(100, intval(($kecepatanAngin / 45) * 100));

        // Risiko Cuaca Gabungan
        $skorCuaca = intval((0.3 * $risikoSuhu) + (0.3 * $risikoCurahHujan) + (0.2 * $risikoAngin) + (0.2 * $risikoBadai));

        // 2. Hitung Risiko Inflasi (Skala 0-100)
        $inflasi = $negara->inflasi;
        if ($inflasi < 0) {
            $skorInflasi = 50; // Deflasi - indikasi stagnansi ekonomi
        } elseif ($inflasi <= 3.0) {
            $skorInflasi = 15; // Sangat stabil
        } elseif ($inflasi <= 6.0) {
            $skorInflasi = 40; // Sedang
        } elseif ($inflasi <= 10.0) {
            $skorInflasi = 70; // Tinggi
        } else {
            $skorInflasi = 95; // Inflasi ekstrem / Krisis
        }

        // 3. Hitung Risiko Nilai Tukar (Skala 0-100)
        $mataUang = $negara->kode_mata_uang;
        $skorKurs = 30; // Default

        // Klasifikasikan risiko mata uang berdasarkan stabilitas global
        if (in_array($mataUang, ['USD', 'EUR', 'JPY', 'SGD'])) {
            $skorKurs = 15; // Sangat stabil
        } elseif (in_array($mataUang, ['AUD', 'CNY', 'GBP'])) {
            $skorKurs = 30; // Cukup stabil
        } elseif (in_array($mataUang, ['IDR', 'BRL', 'INR'])) {
            $skorKurs = 50; // Sedikit volatil (negara berkembang)
        } else {
            $skorKurs = 65; // Volatilitas tinggi
        }

        // 4. Hitung Risiko Sentimen Berita (Skala 0-100)
        $beritaCaches = $negara->newsCaches()->take(8)->get();
        $skorSentimenBerita = 35; // Default

        if ($beritaCaches->count() > 0) {
            $totalPositif = 0;
            $totalNegatif = 0;

            foreach ($beritaCaches as $berita) {
                $totalPositif += $berita->skor_sentimen_positif;
                $totalNegatif += $berita->skor_sentimen_negatif;
            }

            $totalKataSentimen = $totalPositif + $totalNegatif;
            if ($totalKataSentimen > 0) {
                // Formula: Persentase kata negatif terhadap total kata sentimen yang muncul
                $skorSentimenBerita = intval(($totalNegatif / ($totalPositif + $totalNegatif + 1)) * 100);
            }
        }

        // 5. Gabungkan Berdasarkan Bobot Pengaturan Sistem
        $bobotCuaca = (double)SystemSetting::getVal('bobot_cuaca', '0.30');
        $bobotInflasi = (double)SystemSetting::getVal('bobot_inflasi', '0.20');
        $bobotKurs = (double)SystemSetting::getVal('bobot_nilai_tukar', '0.10');
        $bobotSentimen = (double)SystemSetting::getVal('bobot_sentimen', '0.40');

        $totalSkor = ($bobotCuaca * $skorCuaca) 
                   + ($bobotInflasi * $skorInflasi) 
                   + ($bobotKurs * $skorKurs) 
                   + ($bobotSentimen * $skorSentimenBerita);
                   
        $totalSkor = max(0, min(100, intval($totalSkor)));

        // Klasifikasikan tingkat risiko
        if ($totalSkor < 35) {
            $tingkatRisiko = 'Rendah';
        } elseif ($totalSkor < 60) {
            $tingkatRisiko = 'Sedang';
        } else {
            $tingkatRisiko = 'Tinggi';
        }

        // Simpan ke database (Skor Risiko Saat Ini)
        $skorModel = RiskScore::updateOrCreate(
            ['negara_id' => $negara->id],
            [
                'risiko_cuaca' => $skorCuaca,
                'risiko_inflasi' => $skorInflasi,
                'risiko_nilai_tukar' => $skorKurs,
                'risiko_sentimen_berita' => $skorSentimenBerita,
                'total_risiko' => $totalSkor,
                'tingkat_risiko' => $tingkatRisiko
            ]
        );

        // Catat riwayat log tren risiko
        RiskScoreHistory::create([
            'negara_id' => $negara->id,
            'risiko_cuaca' => $skorCuaca,
            'risiko_inflasi' => $skorInflasi,
            'risiko_nilai_tukar' => $skorKurs,
            'risiko_sentimen_berita' => $skorSentimenBerita,
            'total_risiko' => $totalSkor,
            'dihitung_pada' => Carbon::now()
        ]);

        return $skorModel;
    }
}
