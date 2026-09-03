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
        'bagian_bidang_id',
        'sub_bagian_seksi_id',
        'level_jabatan',
        'sisa_cuti_tahunan',
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
    
    /**
     * Relasi ke bagian/bidang (Parent Level)
     */
    public function bagianBidang()
    {
        return $this->belongsTo(bagianBidang::class,'bagian_bidang_id');
    }

    /**
     * Relasi ke Sub-Bagian/Seksi (child level)
     */
    public function subBagianSeksi()
    {
        return $this->belongsTo(subBagianSeksi::class, 'sub_bagian_seksi_id');
    }
}
