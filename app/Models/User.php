<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

     /**
     * Tentukan status kepegawaian dari struktur NIP 18 digit, sesuai aturan
     * BKN (PP 49/2018 & Perka BKN 22/2007): digit ke-13—14 pada NIP PNS
     * selalu bulan pengangkatan CPNS (01–12), sedangkan pada NI PPPK itu
     * kode frekuensi pengangkatan yang selalu mulai dari 21 ke atas.
     * Return null kalau NIP-nya tidak 18 digit murni (kasus lama/tidak
     * standar) — biarkan diisi manual oleh admin, jangan salah tebak.
     */
    public static function statusKepegawaianDariNip(?string $nip): ?string
    {
        $digit = preg_replace('/\D/', '', (string) $nip);

        if (strlen($digit) !== 18) {
            return null;
        }

        $kodeTmt = (int) substr($digit, 12, 2);

        return ($kodeTmt >= 1 && $kodeTmt <= 12) ? 'PNS' : 'PPPK';
    }
        /**
     * Pecah saldo cuti ala Excel. Sisa tahun lalu dipakai lebih dulu.
     */
    public static function hitungSaldo(int $jatah, int $saldoLalu, int $terpakai): array
    {
        $saldoLalu     = max(0, min($saldoLalu, $jatah));
        $jatahBerjalan = $jatah - $saldoLalu;
        $dariLalu      = min($terpakai, $saldoLalu);
        $dariBerjalan  = $terpakai - $dariLalu;

        return [
            'saldo_lalu'       => $saldoLalu,
            'dipakai_lalu'     => $dariLalu,
            'sisa_lalu'        => $saldoLalu - $dariLalu,
            'jatah_berjalan'   => $jatahBerjalan,
            'dipakai_berjalan' => $dariBerjalan,
            'sisa_berjalan'    => $jatahBerjalan - $dariBerjalan,
            'total_sisa'       => $jatah - $terpakai,
        ];
    }
    
        /**
     * Satu pintu untuk semua perhitungan saldo (rekap, detail, ekspor, tutup tahun, informasi).
     * - koreksi_terpakai = selisih antara angka sheet JATAH CUTI dan riwayat yang tercatat.
     * - Cuti Besar: hak cuti tahunan tahun berjalan hangus, sisa tahun lalu tetap ada.
     */
    public static function saldoDari(self $user, int $terpakaiTercatat, bool $cutiBesar = false): array
    {
        $saldoLalu = (int) $user->saldo_tahun_lalu;
        $jatah     = (int) ($user->jatah_cuti ?? 12);

        if ($cutiBesar) {
            $jatah = min($jatah, $saldoLalu);
        }

        $terpakai = max(0, $terpakaiTercatat + (int) $user->koreksi_terpakai);

        return array_merge(self::hitungSaldo($jatah, $saldoLalu, $terpakai), [
            'kuota'      => $jatah,
            'terpakai'   => $terpakai,
            'cuti_besar' => $cutiBesar,
        ]);
    }

    public function punyaCutiBesar(int $tahun): bool
    {
        return $this->pengajuanCutis()
            ->where('approval_step', 8)
            ->whereYear('tanggal_mulai', $tahun)
            ->whereHas('jenisCuti', fn ($q) => $q->where('nama_cuti', 'Cuti Besar'))
            ->exists();
    }

        /** Pengajuan cuti milik Kepala lain di mana user ini ditunjuk sebagai PLH-nya. */
    public function pengajuanPlh()
    {
        return $this->hasMany(PengajuanCuti::class, 'plh_user_id');
    }

    /** Penugasan PLH yang SEDANG berlaku hari ini. */
    public function penugasanPlhAktif()
    {
        return $this->pengajuanPlh()->plhAktifPada()->with('user');
    }

    public function sedangMenjadiPlh(): bool
    {
        return $this->penugasanPlhAktif()->exists();
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'jenis_kelamin',
        'bagian_bidang_id',
        'sub_bagian_seksi_id',
        'level_jabatan',
        'jatah_cuti',
        'status_kepegawaian',
    ];
    public const STRUKTUR_ORGANISASI = [
        'Bagian Tata Usaha' => [
            'Sub Bagian Umum dan Kepegawaian',
            'Sub Bagian Perencanaan dan Keuangan'
        ],
        'Bidang Pelayanan dan Pengoperasian Bandar Udara' => [
            'Seksi Fasilitas dan Pelayanan Bandar Udara',
            'Seksi Pengoperasian Bandar Udara'
        ],
        'Bidang Keamanan, Angkutan Udara dan Kelaikudaraan' => [
            'Seksi Keamanan Penerbangan & Pelayanan Darurat',
            'Seksi Angkutan Udara, Kelaikudaraan & Pengoperasian Pesawat Udara'
        ]
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =====================================================================
    // SUPERADMIN & PLH/PLT
    // =====================================================================

    /** Role kepala yang jabatannya bisa digantikan lewat Plh/Plt. */
    public const ROLE_KEPALA = [
        'Kepala Kantor',
        'Kepala TU',
        'Kepala Bidang',
        'Kepala Sub-Bagian',
        'Kepala Seksi',
    ];

    public const ROLE_BISA_DIGANTIKAN = [
        'Kepala TU',
        'Kepala Bidang',
        'Kepala Sub-Bagian',
        'Kepala Seksi',
    ];

    public function isSuperadmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    /**
     * Sembunyikan akun Superadmin dari daftar/rekap pegawai. Sengaja scope lokal,
     * BUKAN global scope: global scope akan ikut menyaring proses login Superadmin itu sendiri.
     */
    public function scopeBukanSuperadmin($query)
    {
        return $query->whereDoesntHave('roles', fn ($r) => $r->where('name', 'Superadmin'));
    }

    /**
     * Identitas jabatan kepala milik user ini: [role, bagian_bidang_id, sub_bagian_seksi_id].
     * Null kalau bukan kepala. Dipakai untuk mencocokkan dengan tabel penugasan_pejabat.
     */
     public function jabatanKepala(): ?array
    {
        foreach (self::ROLE_BISA_DIGANTIKAN as $role) {
            if (! $this->hasRole($role)) {
                continue;
            }

            return match ($role) {
                'Kepala TU', 'Kepala Bidang' => [$role, $this->bagian_bidang_id, null],
                default /* Kasubag, Kasi */  => [$role, $this->bagian_bidang_id, $this->sub_bagian_seksi_id],
            };
        }

        return null;
    }

    /** Penugasan di mana user ini ditunjuk sebagai pengganti. */
    public function penugasanSebagaiPengganti()
    {
        return $this->hasMany(PenugasanPejabat::class, 'pengganti_id');
    }

    /** Penugasan di mana user ini adalah kepala yang digantikan. */
    public function penugasanSebagaiDefinitif()
    {
        return $this->hasMany(PenugasanPejabat::class, 'pejabat_definitif_id');
    }

    /** Penugasan Plh/Plt yang sedang berlaku hari ini dengan user ini sebagai pengganti (null jika tidak ada). */
    public function penugasanAktif(): ?PenugasanPejabat
    {
        return $this->penugasanSebagaiPengganti()->berlaku()->latest('id')->first();
    }

    /**
     * Apakah jabatan kepala user ini sedang dipegang orang lain (Plh/Plt berlaku)?
     * Kalau ya, user ini tidak boleh meng-approve selama periodenya.
     */
    public function sedangDigantikan(): bool
    {
        $jabatan = $this->jabatanKepala();
        if (! $jabatan) {
            return false;
        }

        return PenugasanPejabat::berlaku()->untukJabatan(...$jabatan)->exists();
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function pengajuanCutis()
    {
        return $this->hasMany(PengajuanCuti::class, 'user_id');
    }
    /**
     * Relasi ke tabel bagian_bidangs
     */
    public function bagianBidang()
    {
        return $this->belongsTo(BagianBidang::class, 'bagian_bidang_id');
    }

    /**
     * Relasi ke tabel sub_bagian_seksis
     */
    public function subBagianSeksi()
    {
        return $this->belongsTo(SubBagianSeksi::class, 'sub_bagian_seksi_id');
    }
}