<?php

namespace App\Exports;

use App\Models\User;
use App\Models\PengajuanCuti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapCutiExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Ambil data semua pegawai
        return User::with(['bagianBidang', 'subBagianSeksi'])->get();
    }

    // Fungsi ini untuk membuat Judul Kolom (Header) di Excel
    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEGAWAI',
            'NIP',
            'DIVISI / SUBBAGIAN',
            'KUOTA TAHUNAN',
            'CUTI TERPAKAI',
            'SISA KUOTA'
        ];
    }

    // Fungsi ini untuk mengisi baris data ke dalam Excel
    public function map($user): array
    {
        // Bikin nomor urut otomatis
        static $no = 0;
        $no++;

        // Hitung cuti terpakai
        $terpakai = PengajuanCuti::where('user_id', $user->id)
            ->where('status_pengajuan', 'Disetujui')
            ->whereYear('created_at', date('Y'))
            ->sum('durasi_hari');

        $kuota = $user->jatah_cuti ?? 12;
        $divisi = $user->subBagianSeksi->nama_sub_bagian ?? $user->bagianBidang->nama_bagian ?? '-';

        return [
            $no,
            $user->name,
            $user->nip,
            $divisi,
            $kuota . ' Hari',
            $terpakai . ' Hari',
            ($kuota - $terpakai) . ' Hari'
        ];
    }
}