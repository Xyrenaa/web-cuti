<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BagianBidang extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'is_tu'];

    //Relasi 1 Bagian/Bidang memiliki banyak Seksi
    public function subBagianSeksis()
    {
        return $this->hasMany(subBagianSeksi::class, 'bagian_bidang_id');
    }

    //Relasi 2 Bagian/Bidang memiliki banyak Pegawai
    public function users()
    {
        return $this->hasMany(User::class, 'bagian_bidang_id');
    }
}
