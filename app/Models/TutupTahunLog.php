<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutupTahunLog extends Model
{
    protected $fillable = ['tahun_ditutup', 'jumlah_pegawai', 'dilakukan_oleh'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}