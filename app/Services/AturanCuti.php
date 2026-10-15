<?php

namespace App\Services;

use App\Models\PengaturanCuti;

class AturanCuti
{
    private static ?array $memo = null;

    /** [kunci => nilai]. null pada batas sisa dibawa berarti TANPA BATAS. */
    public static function semua(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        try {
            $baris = PengaturanCuti::pluck('nilai', 'kunci');
        } catch (\Throwable) {
            $baris = collect(); // tabel belum dimigrasi: pakai nilai bawaan
        }

        $hasil = [];
        foreach (PengaturanCuti::BAWAAN as $kunci => $bawaan) {
            $hasil[$kunci] = $baris->has($kunci)
                ? ($baris[$kunci] === null ? null : (int) $baris[$kunci])
                : $bawaan;
        }

        return self::$memo = $hasil;
    }

    public static function jatahDasar(): int
    {
        return (int) (self::semua()[PengaturanCuti::JATAH_DASAR] ?? PengaturanCuti::BAWAAN[PengaturanCuti::JATAH_DASAR]);
    }

    /** null = tanpa batas. */
    public static function batasSisaDibawa(): ?int
    {
        return self::semua()[PengaturanCuti::BATAS_SISA_DIBAWA];
    }

    /** Sisa yang boleh dibawa ke tahun berikutnya. */
    public static function sisaYangDibawa(int $sisa): int
    {
        $batas = self::batasSisaDibawa();

        return $batas === null ? $sisa : min($batas, $sisa);
    }

    /** Batas isian koreksi "sisa tahun lalu": minimal 12 (untuk SK penangguhan), 365 bila tanpa batas. */
    public static function maksIsianSaldoLalu(): int
    {
        $batas = self::batasSisaDibawa();

        return $batas === null ? 365 : max($batas, 12);
    }

    public static function teksBatasSisa(): string
    {
        $batas = self::batasSisaDibawa();

        return $batas === null ? 'seluruhnya (tanpa batas)' : "maksimal {$batas} hari kerja";
    }

    public static function teksBatasSingkat(): string
    {
        $batas = self::batasSisaDibawa();

        return $batas === null ? 'tanpa batas' : "maks. {$batas} hari";
    }

    public static function lupakan(): void
    {
        self::$memo = null;
    }
}