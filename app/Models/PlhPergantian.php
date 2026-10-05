<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlhPergantian extends Model
{
    protected $table = 'plh_pergantian';

    protected $fillable = ['pengajuan_cuti_id', 'plh_lama_id', 'plh_baru_id', 'alasan', 'dilakukan_oleh'];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanCuti::class, 'pengajuan_cuti_id');
    }

    public function lama()
    {
        return $this->belongsTo(User::class, 'plh_lama_id');
    }

    public function baru()
    {
        return $this->belongsTo(User::class, 'plh_baru_id');
    }

    public function pelaku()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}