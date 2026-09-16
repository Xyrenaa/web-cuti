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

        // Arahkan ke halaman terkait kalau notifikasinya membawa URL tujuan.
        $tujuan = $notification->data['url'] ?? null;

        return $tujuan ? redirect($tujuan) : redirect()->back();
    }

    // Membaca semua notifikasi
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}