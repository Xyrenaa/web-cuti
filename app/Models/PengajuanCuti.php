<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanCuti extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['status_label', 'status_group'];

    protected $casts = [
        'bukti_pendukung' => 'array',
        'dokumen_ttd' => 'array',
    ];
    protected $fillable = [
    'kode_pengajuan',
    'user_id',        
    'jenis_cuti_id', 
    'tanggal_mulai',   
    'tanggal_selesai',
    'durasi_hari',  
    'alasan',            
    'lokasi',       
    'surat_pengajuan',  
    'bukti_pendukung',
    'dokumen_ttd',
    'approval_step',
    'plh_user_id',     
    'status_pengajuan',
    'catatan_penolakan'  
];

    public function jenisCuti() {
        return $this->belongsTo(JenisCuti::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        // =====================================================================
    // FITUR PLH (Pelaksana Harian)
    // =====================================================================
    public const ROLE_WAJIB_PLH = ['Kepala Seksi', 'Kepala Sub-Bagian', 'Kepala Bidang', 'Kepala TU'];

    public function plh()
    {
        return $this->belongsTo(User::class, 'plh_user_id');
    }

    public function butuhPlh(): bool
    {
        $pemohon = $this->user;

        return $pemohon
            && ! $pemohon->hasRole('Kepala Kantor')
            && $pemohon->hasAnyRole(self::ROLE_WAJIB_PLH);
    }

    public function scopePlhAktifPada($query, $tanggal = null)
    {
        $tgl = \Carbon\Carbon::parse($tanggal ?? today())->toDateString();

        return $query->where('approval_step', 8)
            ->whereNotNull('plh_user_id')
            ->whereDate('tanggal_mulai', '<=', $tgl)
            ->whereDate('tanggal_selesai', '>=', $tgl);
    }

    public function kandidatPlh()
    {
        $pemohon = $this->user;
        $query   = User::query()->where('id', '!=', $pemohon->id);

        if ($pemohon->hasAnyRole(['Kepala Bidang', 'Kepala TU'])) {
            if (! $pemohon->bagian_bidang_id) {
                return $query->whereRaw('1 = 0');
            }
            $query->role(['Kepala Seksi', 'Kepala Sub-Bagian'])
                ->where('bagian_bidang_id', $pemohon->bagian_bidang_id);
        } elseif ($pemohon->hasAnyRole(['Kepala Seksi', 'Kepala Sub-Bagian'])) {
            if (! $pemohon->sub_bagian_seksi_id) {
                return $query->whereRaw('1 = 0');
            }
            $query->role('Pegawai')
                ->where('sub_bagian_seksi_id', $pemohon->sub_bagian_seksi_id);
        } else {
            return $query->whereRaw('1 = 0');
        }

        $query->withoutRole('Admin Kepegawaian');

        $mulai   = \Carbon\Carbon::parse($this->tanggal_mulai)->toDateString();
        $selesai = \Carbon\Carbon::parse($this->tanggal_selesai)->toDateString();
        $bentrok = fn ($q) => $q->whereNotIn('approval_step', [0, 9, 10])
            ->whereDate('tanggal_mulai', '<=', $selesai)
            ->whereDate('tanggal_selesai', '>=', $mulai);

        return $query
            ->whereDoesntHave('pengajuanCutis', $bentrok)
            ->whereDoesntHave('pengajuanPlh', fn ($q) => $bentrok($q->where('id', '!=', $this->id)));
    }

    public function getStatusLabelAttribute()
    {
        // Sesuaikan angka step di bawah ini dengan alur bisnismu (1-6 dan jalur TU 3/7/8)
        return match ($this->approval_step) {
            0 => 'Pengajuan Ditolak',
            1 => 'Menunggu Kepala Seksi',
            2 => 'Menunggu Kepala Bidang',
            3 => 'Menunggu Verifikasi Admin',
            4 => 'Menunggu Kepala Sub-Bagian',
            5 => 'Menunggu Kepala Tata Usaha',
            6 => 'Menunggu Kepala Kantor',
            7 => 'Menunggu Persetujuan Final dari Admin',
            8 => 'Pengajuan Telah Disetujui',
            9 => 'Perlu Direvisi',
            10 => 'Dibatalkan',
            default => 'Status Tidak Diketahui',
        };
    }

    /**
     * Grup status yang disederhanakan buat badge (Menunggu/Disetujui/Ditolak/Dibatalkan).
     * SELALU diturunkan dari approval_step (bukan dari kolom status_pengajuan yang
     * legacy dan tidak lagi diupdate), supaya nggak ada dua sumber kebenaran yang beda.
     */
    public function getStatusGroupAttribute()
    {
        return match ($this->approval_step) {
            8 => 'Disetujui',
            0 => 'Ditolak',
            10 => 'Dibatalkan',
            default => 'Menunggu',
        };
    }
        /**
     * Riwayat dokumen ttd, diurutkan dari tahap paling awal ke paling akhir.
     * Tidak mengandalkan urutan array mentah, karena kalau nanti ada
     * koreksi/upload ulang, urutan penyimpanannya bisa tidak kronologis.
     */
    public function getRiwayatTtdAttribute(): array
    {
        $daftar = is_array($this->dokumen_ttd) ? $this->dokumen_ttd : [];

        usort($daftar, function ($a, $b) {
            $stepA = $a['step'] ?? 0;
            $stepB = $b['step'] ?? 0;

            if ($stepA === $stepB) {
                return strcmp($a['waktu'] ?? '', $b['waktu'] ?? '');
            }

            return $stepA <=> $stepB;
        });

        return $daftar;
    }

    /**
     * Versi TERAKHIR dari surat yang sudah ditandatangani (null kalau belum ada).
     */
    public function getTtdTerakhirAttribute(): ?array
    {
        $riwayat = $this->riwayat_ttd;

        return empty($riwayat) ? null : end($riwayat);
    }

    /**
     * Surat yang berlaku saat ini bagi pejabat yang sedang memeriksa:
     * versi ttd terbaru kalau sudah ada, kalau belum ya surat asli dari pemohon.
     */
    public function getSuratAktifAttribute(): array
    {
        $ttd = $this->ttd_terakhir;

        if ($ttd) {
            return [
                'file'   => $ttd['file'] ?? null,
                'label'  => 'Surat cuti versi terbaru',
                'oleh'   => $ttd['nama'] ?? '-',
                'peran'  => $ttd['peran'] ?? '-',
                'waktu'  => $ttd['waktu'] ?? null,
                'is_ttd' => true,
            ];
        }

        return [
            'file'   => $this->surat_pengajuan,
            'label'  => 'Surat pengajuan (belum ada tanda tangan)',
            'oleh'   => $this->user->name ?? '-',
            'peran'  => 'Pemohon',
            'waktu'  => optional($this->created_at)->toDateTimeString(),
            'is_ttd' => false,
        ];
    }
}