<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StatusCutiNotification extends Notification
{
    use Queueable;

    protected $judul;
    protected $pesan;

    // Menerima data judul dan pesan saat notifikasi dipanggil
    public function __construct($judul, $pesan)
    {
        $this->judul = $judul;
        $this->pesan = $pesan;
    }

    // Beritahu Laravel untuk menyimpan notifikasi ini ke Database
    public function via($notifiable)
    {
        return ['database'];
    }

    // Susun data yang akan masuk ke kolom 'data' (JSON) di database
    public function toArray($notifiable)
    {
        return [
            'judul' => $this->judul,
            'pesan' => $this->pesan,
        ];
    }
}