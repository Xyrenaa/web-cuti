<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanCuti extends Model
{
    protected $table = 'pengaturan_cuti';

    public const JATAH_DASAR       = 'jatah_cuti_dasar';
    public const BATAS_SISA_DIBAWA = 'batas_sisa_cuti_dibawa';

    /** Nilai bawaan bila barisnya belum ada (sama dengan aturan lama di kode). */
    public const BAWAAN = [
        self::JATAH_DASAR       => 12,
        self::BATAS_SISA_DIBAWA => 6,
    ];

    public const LABEL = [
        self::JATAH_DASAR       => 'Jatah cuti tahunan dasar',
        self::BATAS_SISA_DIBAWA => 'Batas sisa cuti dibawa ke tahun berikutnya',
    ];

    protected $fillable = ['kunci', 'nilai', 'diubah_oleh'];
}