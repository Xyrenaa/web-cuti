<?php

namespace App\Support;

use RuntimeException;

/**
 * Captcha angka berbentuk abstrak untuk halaman login.
 *
 * - Gambar dibuat dengan PHP GD dari garis-garis buatan sendiri (tanpa file font,
 *   tanpa paket tambahan) lalu dilengkungkan, diberi gangguan garis & titik.
 * - Jawaban TIDAK pernah dikirim ke browser: di session hanya disimpan hash HMAC
 *   dan batas waktu. Gambar dikirim sebagai data-URI, jadi tidak ada endpoint
 *   gambar yang bisa diminta berulang untuk "menebak" kode yang sama.
 * - Sekali pakai: setiap pengecekan (benar maupun salah) menghapus kode lama.
 */
class Captcha
{
    public const SESSION_KEY = 'captcha_login';

    private const LEBAR = 150;
    private const TINGGI = 48;
    private const SKALA = 3; // render 3x lebih besar lalu diperkecil => tepi halus

    public static function aktif(): bool
    {
        return (bool) config('captcha.enabled', true);
    }

    /** Buat kode baru, simpan hash-nya di session, kembalikan gambar (data-URI). */
    public static function segarkan(): string
    {
        $kode = self::buatKode();
        session()->put(self::SESSION_KEY, self::payload($kode));

        return self::gambar($kode);
    }

    /** Cek jawaban pengguna. Selalu menghabiskan kode (sekali pakai). */
    public static function cocok(?string $jawaban): bool
    {
        $tersimpan = session()->pull(self::SESSION_KEY);

        if (! is_array($tersimpan) || ! isset($tersimpan['hash'], $tersimpan['exp'])) {
            return false;
        }

        if (time() > (int) $tersimpan['exp']) {
            return false;
        }

        $jawaban = preg_replace('/\D/', '', (string) $jawaban);

        if ($jawaban === '') {
            return false;
        }

        return hash_equals($tersimpan['hash'], self::hash($jawaban));
    }

    /**
     * Isi session untuk sebuah kode. Dipisah supaya test bisa menyiapkan
     * session captcha tanpa harus membaca gambar.
     */
    public static function payload(string $kode): array
    {
        return [
            'hash' => self::hash($kode),
            'exp' => time() + (int) config('captcha.ttl', 300),
        ];
    }

    private static function hash(string $kode): string
    {
        return hash_hmac('sha256', $kode, (string) config('app.key'));
    }

    private static function buatKode(): string
    {
        $kode = '';
        for ($i = 0; $i < (int) config('captcha.length', 5); $i++) {
            $kode .= random_int(0, 9);
        }

        return $kode;
    }

    // ------------------------------------------------------------------
    // Penggambaran
    // ------------------------------------------------------------------

    /** Bentuk tiap angka: daftar goresan, koordinat 0..1 (y ke bawah). */
    private static function bentukAngka(string $d): array
    {
        return match ($d) {
            '0' => [self::busur(.5, .5, .5, .5, 0, 360)],
            '1' => [[[.2, .22], [.55, 0], [.55, 1]]],
            '2' => [array_merge(self::busur(.5, .3, .5, .3, 170, 400), [[0, 1], [1, 1]])],
            '3' => [
                self::busur(.5, .26, .45, .26, 210, 450),
                self::busur(.5, .74, .5, .26, 270, 520),
            ],
            '4' => [[[.75, 0], [0, .7], [1, .7]], [[.72, .15], [.72, 1]]],
            '5' => [
                [[.9, 0], [.15, 0], [.1, .45]],
                self::busur(.48, .68, .5, .32, 230, 500),
            ],
            '6' => [
                self::bezier([.9, .05], [.05, .15], [.02, .68]),
                self::busur(.5, .68, .5, .32, 0, 360),
            ],
            '7' => [[[0, 0], [1, 0], [.35, 1]]],
            '8' => [
                self::busur(.5, .27, .4, .27, 0, 360),
                self::busur(.5, .74, .5, .27, 0, 360),
            ],
            '9' => [
                self::busur(.5, .32, .5, .32, 0, 360),
                self::bezier([.98, .32], [.95, .88], [.1, .96]),
            ],
        };
    }

    private static function busur(float $cx, float $cy, float $rx, float $ry, float $dari, float $sampai): array
    {
        $titik = [];
        $langkah = 28;
        for ($i = 0; $i <= $langkah; $i++) {
            $t = deg2rad($dari + ($sampai - $dari) * $i / $langkah);
            $titik[] = [$cx + $rx * cos($t), $cy + $ry * sin($t)];
        }

        return $titik;
    }

    private static function bezier(array $a, array $b, array $c): array
    {
        $titik = [];
        for ($i = 0; $i <= 20; $i++) {
            $t = $i / 20;
            $titik[] = [
                (1 - $t) ** 2 * $a[0] + 2 * (1 - $t) * $t * $b[0] + $t ** 2 * $c[0],
                (1 - $t) ** 2 * $a[1] + 2 * (1 - $t) * $t * $b[1] + $t ** 2 * $c[1],
            ];
        }

        return $titik;
    }

    public static function gambar(string $kode): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException(
                'Captcha butuh ekstensi PHP GD. Aktifkan ekstensi gd di php.ini, atau set CAPTCHA_ENABLED=false di .env.'
            );
        }

        $S = self::SKALA;
        $W = self::LEBAR * $S;
        $H = self::TINGGI * $S;
        $u = $S * self::TINGGI / 64; // satuan tebal/jitter, ikut mengecil

        $palet = [[18, 44, 120], [8, 96, 128], [88, 36, 112], [30, 30, 60], [120, 40, 60], [10, 110, 90]];

        $img = imagecreatetruecolor($W, $H);
        self::latar($img, $W, $H);

        // --- angka ---
        $n = strlen($kode);
        $margin = $W * 0.07;
        $jarak = ($W - 2 * $margin) / $n;

        for ($i = 0; $i < $n; $i++) {
            $tinggi = $H * mt_rand(54, 68) / 100;
            $lebar = $tinggi * mt_rand(46, 60) / 100;
            $cx = $margin + $jarak * ($i + 0.5) + mt_rand(-3, 3) * $u;
            $cy = $H / 2 + mt_rand(-4, 4) * $u;
            $sudut = deg2rad(mt_rand(-24, 24));
            $miring = mt_rand(-25, 25) / 100;
            $tebal = $u * mt_rand(27, 36) / 10;

            $w = $palet[array_rand($palet)];
            $warna = imagecolorallocate($img, $w[0], $w[1], $w[2]);

            foreach (self::bentukAngka($kode[$i]) as $goresan) {
                $dunia = [];
                foreach ($goresan as [$px, $py]) {
                    $x = ($px - .5) * $lebar;
                    $y = ($py - .5) * $tinggi;
                    $x += $y * $miring;
                    $dunia[] = [
                        $cx + $x * cos($sudut) - $y * sin($sudut),
                        $cy + $x * sin($sudut) + $y * cos($sudut),
                    ];
                }
                self::goresan($img, $dunia, $tebal, $warna);
            }
        }

        // --- lengkungkan seluruh gambar (bikin bentuk jadi abstrak) ---
        $img = self::gelombang($img, $W, $H, $palet);

        // --- gangguan di atas gambar ---
        self::gangguan($img, $W, $H, $palet);

        // --- perkecil dengan resampling ---
        $akhir = imagecreatetruecolor(self::LEBAR, self::TINGGI);
        imagecopyresampled($akhir, $img, 0, 0, 0, 0, self::LEBAR, self::TINGGI, $W, $H);
        imagedestroy($img);

        ob_start();
        imagepng($akhir, null, 6);
        $png = (string) ob_get_clean();
        imagedestroy($akhir);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    private static function latar($img, int $W, int $H): void
    {
        $r = mt_rand(232, 244);
        $g = mt_rand(236, 246);
        $b = mt_rand(244, 252);

        for ($y = 0; $y < $H; $y++) {
            $f = 1 - 0.07 * ($y / $H);
            imageline($img, 0, $y, $W, $y, imagecolorallocate($img, (int) ($r * $f), (int) ($g * $f), (int) ($b * $f)));
        }
    }

    /** Gambar goresan tebal dengan ujung bulat: cap lingkaran di sepanjang garis. */
    private static function goresan($img, array $titik, float $tebal, int $warna): void
    {
        $d = max(2, (int) round($tebal));
        $gap = max(1.0, $tebal / 3);

        for ($i = 0; $i < count($titik) - 1; $i++) {
            [$x1, $y1] = $titik[$i];
            [$x2, $y2] = $titik[$i + 1];
            $panjang = hypot($x2 - $x1, $y2 - $y1);
            $langkah = max(1, (int) ceil($panjang / $gap));

            for ($s = 0; $s <= $langkah; $s++) {
                $t = $s / $langkah;
                imagefilledellipse($img, (int) ($x1 + ($x2 - $x1) * $t), (int) ($y1 + ($y2 - $y1) * $t), $d, $d, $warna);
            }
        }
    }

    /** Pelengkungan sinus pada kolom lalu baris. */
    private static function gelombang($src, int $W, int $H, array $palet)
    {
        $u = self::SKALA * self::TINGGI / 64;   // untuk amplitudo (vertikal)
        $ux = self::SKALA * self::LEBAR / 200;  // untuk periode (horizontal)

        $tmp = imagecreatetruecolor($W, $H);
        imagecopy($tmp, $src, 0, 0, 0, 0, $W, $H);
        $fase = mt_rand(0, 628) / 100;
        $amp = mt_rand(15, 26) / 10 * $u;
        $per = mt_rand(55, 85) * $ux;
        for ($x = 0; $x < $W; $x++) {
            $dy = (int) round(sin($x / $per * 2 * M_PI + $fase) * $amp);
            imagecopy($tmp, $src, $x, $dy, $x, 0, 1, $H);
        }

        $out = imagecreatetruecolor($W, $H);
        imagecopy($out, $tmp, 0, 0, 0, 0, $W, $H);
        $fase = mt_rand(0, 628) / 100;
        $amp = mt_rand(8, 16) / 10 * $u;
        $per = mt_rand(30, 50) * $u;
        for ($y = 0; $y < $H; $y++) {
            $dx = (int) round(sin($y / $per * 2 * M_PI + $fase) * $amp);
            imagecopy($out, $tmp, $dx, $y, 0, $y, $W, 1);
        }

        imagedestroy($src);
        imagedestroy($tmp);

        return $out;
    }

    private static function gangguan($img, int $W, int $H, array $palet): void
    {
        $u = self::SKALA * self::TINGGI / 64;

        // garis lengkung memotong angka
        for ($k = 0; $k < 3; $k++) {
            $w = $palet[array_rand($palet)];
            $warna = imagecolorallocatealpha($img, $w[0], $w[1], $w[2], mt_rand(55, 80));
            $y0 = mt_rand((int) ($H * .15), (int) ($H * .85));
            $a = mt_rand(5, 12) * $u;
            $f = mt_rand(15, 40) / 10;
            $p = mt_rand(0, 628) / 100;
            $titik = [];
            for ($x = 0; $x <= $W; $x += 4) {
                $titik[] = [$x, $y0 + sin($x / $W * $f * 2 * M_PI + $p) * $a];
            }
            self::goresan($img, $titik, $u * mt_rand(9, 14) / 10, $warna);
        }

        // beberapa garis lurus
        for ($k = 0; $k < 3; $k++) {
            $w = $palet[array_rand($palet)];
            $warna = imagecolorallocatealpha($img, $w[0], $w[1], $w[2], mt_rand(30, 60));
            imagesetthickness($img, max(1, (int) round($u)));
            imageline($img, mt_rand(0, $W), mt_rand(0, $H), mt_rand(0, $W), mt_rand(0, $H), $warna);
        }
        imagesetthickness($img, 1);

        // titik-titik bintik
        for ($k = 0; $k < 90; $k++) {
            $w = $palet[array_rand($palet)];
            $warna = imagecolorallocatealpha($img, $w[0], $w[1], $w[2], mt_rand(30, 90));
            $d = mt_rand(1, 3) * $u / 2;
            imagefilledellipse($img, mt_rand(0, $W), mt_rand(0, $H), (int) $d, (int) $d, $warna);
        }
    }
}