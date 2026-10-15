<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutupTahunLog extends Model
{
    protected $fillable = ['tahun_ditutup', 'jumlah_pegawai', 'aturan', 'dilakukan_oleh'];

    protected function casts(): array
    {
        return ['aturan' => 'array'];
    }
}