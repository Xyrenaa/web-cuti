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