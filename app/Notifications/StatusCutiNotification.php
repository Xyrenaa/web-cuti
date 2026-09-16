<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StatusCutiNotification extends Notification
{
    use Queueable;

    /**
     * $meta menampung konteks tambahan: tipe notifikasi, kode pengajuan,
     * id pengajuan, dan URL tujuan saat notifikasi diklik.
     * Dibuat opsional supaya pemanggilan lama (judul, pesan) tetap jalan.
     */
    public function __construct(
        protected string $judul,
        protected string $pesan,
        protected array $meta = []
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return array_merge([
            'judul'          => $this->judul,
            'pesan'          => $this->pesan,
            'tipe'           => 'umum', // aksi|status|ttd|final|tolak|revisi|batal
            'kode_pengajuan' => null,
            'pengajuan_id'   => null,
            'url'            => null,
        ], $this->meta);
    }
}