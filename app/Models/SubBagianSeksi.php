<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubBagianSeksi extends Model
{
    use HasFactory;

    protected $fillable = ['bagian_bidang_id', 'nama'];

    //Relasi 1 Sub-Bagian/Seksi Dimiliki Oleh 1 Bagian/Bidang
    public function bagianBidang()
    {
        return $this->belongsTo(BagianBidang::class, 'bagian_bidang_id');
    }

    //Relasi 2 Sub-Bagian/Seksi Memiliki Banyak Pegawai (user)
    public function users()
    {
        return $this->hasMany(User::class, 'sub_bagian_seksi_id');
    }
}
