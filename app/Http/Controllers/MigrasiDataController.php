<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PegawaiImport;
use App\Imports\RekapCutiImport;

class MigrasiDataController extends Controller
{
    // Menampilkan halaman UI
    public function index()
    {
        return view('admin.migrasi.index');
    }

    // Proses tombol Upload Pegawai
    public function importPegawai(Request $request)
    {
        set_time_limit(0); // Mematikan batas waktu eksekusi
        
        $request->validate(['file_pegawai' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new PegawaiImport, $request->file('file_pegawai'));
        
        return back()->with('success', 'Data seluruh Pegawai berhasil di-import dan dibuatkan akun!');
    }

    public function importRekap(Request $request)
    {
        set_time_limit(0); // Mematikan batas waktu eksekusi
        
        $request->validate(['file_rekap' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new RekapCutiImport, $request->file('file_rekap'));
        
        return back()->with('success', 'Riwayat Rekap Cuti lama berhasil terintegrasi ke sistem!');
    }
}