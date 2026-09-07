<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanCuti extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'bukti_pendukung' => 'array',
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
    'approval_step',     
    'status_pengajuan'  
];

    public function jenisCuti() {
        return $this->belongsTo(JenisCuti::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
