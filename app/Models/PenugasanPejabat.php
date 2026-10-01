<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenugasanPejabat extends Model
{
    protected $table = 'penugasan_pejabat';

    public const JENIS_PLH = 'PLH';
    public const JENIS_PLT = 'PLT';

    public const STATUS_AKTIF     = 'aktif';
    public const STATUS_SELESAI   = 'selesai';
    public const STATUS_DICABUT   = 'dicabut';
    public const STATUS_DIALIHKAN = 'dialihkan';

    protected $fillable = [
        'jenis', 'jabatan_role', 'bagian_bidang_id', 'sub_bagian_seksi_id',
        'pejabat_definitif_id', 'pengganti_id',
        'tanggal_mulai', 'tanggal_selesai',
        'status', 'nomor_surat', 'alasan', 'keterangan_akhir', 'berakhir_pada',
        'dialihkan_dari_id', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
            'berakhir_pada'   => 'datetime',
        ];
    }

    // ---------- Relasi ----------

    public function pengganti(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengganti_id');
    }

    public function pejabatDefinitif(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pejabat_definitif_id');
    }

    public function bagianBidang(): BelongsTo
    {
        return $this->belongsTo(BagianBidang::class, 'bagian_bidang_id');
    }

    public function subBagianSeksi(): BelongsTo
    {
        return $this->belongsTo(SubBagianSeksi::class, 'sub_bagian_seksi_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function dialihkanDari(): BelongsTo
    {
        return $this->belongsTo(self::class, 'dialihkan_dari_id');
    }

    // ---------- Scope ----------

    /**
     * Penugasan yang BERLAKU hari ini: status aktif DAN tanggalnya sedang jalan.
     * Plh yang tanggal_selesai-nya sudah lewat otomatis tidak lagi berlaku,
     * tanpa perlu scheduler — cukup dihitung dari tanggal.
     */
    public function scopeBerlaku(Builder $q, $tanggal = null): Builder
    {
        $tanggal = ($tanggal ? \Carbon\Carbon::parse($tanggal) : now())->toDateString();

        return $q->where('status', self::STATUS_AKTIF)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->where(function ($w) use ($tanggal) {
                $w->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', $tanggal);
            });
    }

    /** Masih berstatus aktif (termasuk yang terjadwal / sudah lewat tanggal tapi belum ditutup). */
    public function scopeStatusAktif(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_AKTIF);
    }

    /** Cari penugasan untuk satu jabatan (dipakai sistem approval). */
    public function scopeUntukJabatan(Builder $q, string $role, ?int $bagianId = null, ?int $subId = null): Builder
    {
        return $q->where('jabatan_role', $role)
            ->where('bagian_bidang_id', $bagianId)
            ->where('sub_bagian_seksi_id', $subId);
    }

    // ---------- Helper ----------

    /**
     * Siapa yang memegang meja jabatan ini hari ini? Null = tidak ada penugasan
     * (artinya pejabat definitif yang bertindak seperti biasa).
     */
    public static function penggantiAktifUntuk(string $role, ?int $bagianId = null, ?int $subId = null, $tanggal = null): ?User
    {
        return static::berlaku($tanggal)
            ->untukJabatan($role, $bagianId, $subId)
            ->with('pengganti')
            ->latest('id')
            ->first()?->pengganti;
    }

    public function getLabelJenisAttribute(): string
    {
        return $this->jenis === self::JENIS_PLT ? 'Pelaksana Tugas (Plt)' : 'Pelaksana Harian (Plh)';
    }

    /** Status yang tampil ke pengguna: aktif yang tanggalnya lewat dianggap selesai. */
    public function getStatusEfektifAttribute(): string
    {
        if ($this->status !== self::STATUS_AKTIF) {
            return $this->status;
        }
        if ($this->tanggal_selesai && $this->tanggal_selesai->lt(now()->startOfDay())) {
            return self::STATUS_SELESAI;
        }
        if ($this->tanggal_mulai->gt(now()->startOfDay())) {
            return 'terjadwal';
        }
        return self::STATUS_AKTIF;
    }

    public function getNamaJabatanAttribute(): string
    {
        $unit = $this->subBagianSeksi?->nama ?? $this->bagianBidang?->nama;

        return $unit ? "{$this->jabatan_role} — {$unit}" : $this->jabatan_role;
    }
}