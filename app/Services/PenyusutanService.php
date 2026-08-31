<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\MonthlyDepreciation;
use App\Models\AssetDepreciationSetting;

class PenyusutanService
{
    /**
     * Generate 1 bulan penyusutan aset
     * Dipanggil manual (opsi 1)
     */
    public function generateBulanan(
        Asset $asset,
        AssetDepreciationSetting $setting,
        int $userId
    ): MonthlyDepreciation {

         // =========================
        // 0. Cek status disposal
        // =========================
        if ($setting->is_disposed) {
            throw new \Exception('Aset sudah di-disposal. Penyusutan tidak dapat dilanjutkan.');
        }

        // =========================
        // 1. Tentukan periode
        // =========================
        $last = MonthlyDepreciation::where('asset_id', $asset->id)
            ->orderByDesc('periode')
            ->first();

        $periode = $last
            ? Carbon::parse($last->periode)->addMonth()->startOfMonth()
            : Carbon::parse($setting->tgl_mulai_pakai)->addMonth()->startOfMonth();

        // Cegah dobel penyusutan
        if (
            MonthlyDepreciation::where('asset_id', $asset->id)
                ->where('periode', $periode)
                ->exists()
        ) {
            throw new \Exception('Penyusutan untuk periode ini sudah ada.');
        }

        // =========================
        // 2. Nilai buku awal
        // =========================
        $nilaiBukuAwal = $last
            ? $last->nilai_buku_akhir
            : $setting->harga_perolehan;

        if ($nilaiBukuAwal <= 0) {
            throw new \Exception('Nilai buku aset sudah nol.');
        }

        // =========================
        // 3. Hitung beban penyusutan
        // =========================
        if ($setting->metode === 'GARIS_LURUS') {
            $beban = $this->hitungGarisLurus($setting);
        } else {
            $beban = $this->hitungSaldoMenurun($nilaiBukuAwal, $setting);
        }

        // =========================
        // 4. Hitung nilai akhir
        // =========================
        $nilaiBukuAkhir = max(0, $nilaiBukuAwal - $beban);

        $akumulasi = ($last?->akumulasi_sd_bulan ?? 0) + $beban;

        // =========================
        // 5. Simpan ke DB
        // =========================
        return MonthlyDepreciation::create([
            'asset_id'            => $asset->id,
            'periode'            => $periode,
            'metode'             => $setting->metode,
            'beban_bulan'        => $beban,
            'akumulasi_sd_bulan' => $akumulasi,
            'nilai_buku_akhir'   => $nilaiBukuAkhir,
            'user_id'            => $userId,
        ]);
    }

    // ==================================================
    // RUMUS PENYUSUTAN
    // ==================================================

    /**
     * Metode Garis Lurus
     */
    protected function hitungGarisLurus(AssetDepreciationSetting $setting): float
    {
        $umurBulan = $setting->umur_bulan
            ?? ($setting->djpKelompok->masa_manfaat_tahun * 12);

        $nilaiSisa = $setting->nilai_sisa ?? 0;

        return round(
            ($setting->harga_perolehan - $nilaiSisa) / $umurBulan,
            2
        );
    }

    /**
     * Metode Saldo Menurun
     */
    protected function hitungSaldoMenurun(
        float $nilaiBukuAwal,
        AssetDepreciationSetting $setting
    ): float {
        $tarifTahunan = $setting->djpKelompok->tarif_sm_percent;

        return round(
            ($nilaiBukuAwal * ($tarifTahunan / 100)) / 12,
            2
        );
    }
}
