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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'bagian_bidang',
        'sub_bagian_seksi',
        'tingkat_jabatan',
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
}
