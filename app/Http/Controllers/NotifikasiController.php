<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    // Membaca satu notifikasi
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        
        if ($notification->unread()) {
            $notification->markAsRead();
        }

        // Opsional: Jika kamu ingin redirect ke halaman pengajuan setelah diklik
        // return redirect()->route('pegawai.riwayat'); 
        
        return redirect()->back();
    }

    // Membaca semua notifikasi
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}