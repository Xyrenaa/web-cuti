<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanCuti extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['status_label'];

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
    public function getStatusLabelAttribute()
    {
        // Sesuaikan angka step di bawah ini dengan alur bisnismu (1-6 dan jalur TU 3/7/8)
        return match ($this->approval_step) {
            0 => 'Pengajuan Ditolak',
            1 => 'Menunggu Kepala Seksi',
            2 => 'Menunggu Kepala Bidang',
            3 => 'Menunggu Verifikasi Admin',
            4 => 'Menunggu Kepala Sub-Bagian',
            5 => 'Menunggu Kepala Tata Usaha',
            6 => 'Menunggu Kepala Kantor',
            7 => 'Menunggu Persetujuan Final dari Admin',
            8 => 'Pengajuan Telah Disetujui',
            9 => 'Perlu Direvisi',
            10 => 'Dibatalkan',
            default => 'Status Tidak Diketahui',
        };
    }
}    
