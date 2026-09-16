<?php

namespace App\Observers;

use App\Models\PengajuanCuti;
use App\Models\User;
use App\Notifications\StatusCutiNotification;
use Illuminate\Support\Facades\Auth;

class PengajuanCutiObserver
{
    protected array $labelStep = [
        0  => 'Ditolak',
        1  => 'Kepala Seksi',
        2  => 'Kepala Bidang',
        3  => 'Verifikasi Admin Kepegawaian',
        4  => 'Kepala Sub-Bagian',
        5  => 'Kepala Tata Usaha',
        6  => 'Kepala Kantor',
        7  => 'Finalisasi Admin Kepegawaian',
        8  => 'Selesai / Disetujui',
        9  => 'Perlu Direvisi',
        10 => 'Dibatalkan',
    ];

    public function created(PengajuanCuti $pengajuan): void
    {
        $pengajuan->user?->notify(new StatusCutiNotification(
            'Pengajuan Cuti Terkirim',
            "Pengajuan {$pengajuan->kode_pengajuan} berhasil dikirim dan kini menunggu {$this->label($pengajuan->approval_step)}.",
            [
                'tipe'           => 'status',
                'kode_pengajuan' => $pengajuan->kode_pengajuan,
                'pengajuan_id'   => $pengajuan->id,
                'url'            => route('pegawai.detail', $pengajuan->id),
            ]
        ));

        $this->kabariMejaTujuan($pengajuan);
    }

    public function updated(PengajuanCuti $pengajuan): void
    {
        // Notifikasi "surat sudah ditandatangani" dipicu dari perubahan kolom
        // dokumen_ttd, bukan dari controller — jadi otomatis ikut kalau nanti
        // ada alur upload ttd yang baru.
        if ($pengajuan->wasChanged('dokumen_ttd')) {
            $this->kabariSuratDitandatangani($pengajuan);
        }

        if (! $pengajuan->wasChanged('approval_step')) {
            return;
        }

        $stepLama = (int) $pengajuan->getOriginal('approval_step');
        $stepBaru = (int) $pengajuan->approval_step;

        $this->kabariPemohon($pengajuan, $stepLama, $stepBaru);
        $this->kabariMejaTujuan($pengajuan);
    }

    /** Notifikasi ke pegawai pemilik pengajuan. */
    protected function kabariPemohon(PengajuanCuti $pengajuan, int $stepLama, int $stepBaru): void
    {
        $pemohon = $pengajuan->user;
        if (! $pemohon) {
            return;
        }

        $kode   = $pengajuan->kode_pengajuan;
        $pelaku = Auth::user()?->name ?? $this->label($stepLama);

        [$judul, $pesan, $tipe] = match ($stepBaru) {
            0 => [
                'Pengajuan Cuti Ditolak',
                "Pengajuan {$kode} ditolak oleh {$pelaku}. Silakan buka detail pengajuan untuk melihat keterangannya.",
                'tolak',
            ],
            8 => [
                'Cuti Anda Telah Disetujui',
                "Pengajuan {$kode} telah disetujui seluruh pejabat dan difinalisasi Admin Kepegawaian. Surat cuti versi final sudah bisa diunduh.",
                'final',
            ],
            9 => [
                'Pengajuan Perlu Direvisi',
                "Pengajuan {$kode} dikembalikan oleh {$pelaku} untuk diperbaiki. Silakan periksa catatan revisinya.",
                'revisi',
            ],
            // Step 10 = dibatalkan pegawai sendiri, tidak perlu dinotifikasi ke dirinya.
            10 => [null, null, null],
            default => [
                'Status Pengajuan Diperbarui',
                "Pengajuan {$kode} telah disetujui {$pelaku} ({$this->label($stepLama)}) dan kini menunggu {$this->label($stepBaru)}.",
                'status',
            ],
        };

        if ($judul === null) {
            return;
        }

        $pemohon->notify(new StatusCutiNotification($judul, $pesan, [
            'tipe'           => $tipe,
            'kode_pengajuan' => $kode,
            'pengajuan_id'   => $pengajuan->id,
            'url'            => route('pegawai.detail', $pengajuan->id),
        ]));
    }

    /** Notifikasi ke pemohon saat ada versi surat baru yang ditandatangani. */
    protected function kabariSuratDitandatangani(PengajuanCuti $pengajuan): void
    {
        $ttd = $pengajuan->ttd_terakhir;

        if (! $ttd || ! $pengajuan->user) {
            return;
        }

        $penandatangan = $ttd['nama'] ?? 'Pejabat terkait';
        $peran         = $ttd['peran'] ?? '-';

        $pengajuan->user->notify(new StatusCutiNotification(
            'Surat Cuti Telah Ditandatangani',
            "Surat pengajuan {$pengajuan->kode_pengajuan} telah ditandatangani oleh {$penandatangan} ({$peran}). Versi terbaru sudah tersedia di halaman detail.",
            [
                'tipe'           => 'ttd',
                'kode_pengajuan' => $pengajuan->kode_pengajuan,
                'pengajuan_id'   => $pengajuan->id,
                'url'            => route('pegawai.detail', $pengajuan->id),
            ]
        ));
    }

    /** Notifikasi ke pejabat/admin yang mejanya sedang dituju. */
    protected function kabariMejaTujuan(PengajuanCuti $pengajuan): void
    {
        $step        = (int) $pengajuan->approval_step;
        $namaPemohon = $pengajuan->user->name ?? 'Pegawai';
        $kode        = $pengajuan->kode_pengajuan;

        // Pembatalan: informatif ke Admin, bukan permintaan tindakan.
        if ($step === 10) {
            $this->kirim(
                $this->penerima($pengajuan, 3),
                $pengajuan,
                'Pengajuan Cuti Dibatalkan',
                "{$namaPemohon} membatalkan pengajuan cuti {$kode}. Tidak perlu tindakan lebih lanjut.",
                'batal',
                'admin'
            );
            return;
        }

        $penerima = $this->penerima($pengajuan, $step);

        if ($penerima->isEmpty()) {
            return;
        }

        $judul = match ($step) {
            3       => 'Verifikasi Cuti Diperlukan',
            7       => 'Finalisasi Cuti Diperlukan',
            default => 'Pengajuan Cuti Menunggu Persetujuan Anda',
        };

        $pesan = match ($step) {
            3       => "Pengajuan cuti {$namaPemohon} ({$kode}) menunggu verifikasi Anda.",
            7       => "Pengajuan cuti {$namaPemohon} ({$kode}) menunggu persetujuan final Anda.",
            default => "Pengajuan cuti {$namaPemohon} ({$kode}) sudah masuk ke meja Anda sebagai {$this->label($step)} dan menunggu tindakan.",
        };

        $this->kirim(
            $penerima,
            $pengajuan,
            $judul,
            $pesan,
            'aksi',
            in_array($step, [3, 7], true) ? 'admin' : 'kepala'
        );
    }

    protected function kirim($penerima, PengajuanCuti $pengajuan, string $judul, string $pesan, string $tipe, string $tujuan): void
    {
        $url = $tujuan === 'admin'
            ? route('admin.approval.show', $pengajuan->id)
            : route('kepala.approval.show', $pengajuan->id);

        foreach ($penerima as $user) {
            // Jangan kirim notifikasi "butuh persetujuan Anda" ke pemohonnya sendiri
            // (kasus kepala yang mengajukan cuti untuk dirinya sendiri).
            if ($user->id === $pengajuan->user_id) {
                continue;
            }

            $user->notify(new StatusCutiNotification($judul, $pesan, [
                'tipe'           => $tipe,
                'kode_pengajuan' => $pengajuan->kode_pengajuan,
                'pengajuan_id'   => $pengajuan->id,
                'url'            => $url,
            ]));
        }
    }

    /** Siapa yang memegang meja pada step tertentu, relatif terhadap unit pemohon. */
    protected function penerima(PengajuanCuti $pengajuan, int $step)
    {
        $pemohon = $pengajuan->user;

        return match ($step) {
            1 => $pemohon?->sub_bagian_seksi_id
                ? User::role('Kepala Seksi')->where('sub_bagian_seksi_id', $pemohon->sub_bagian_seksi_id)->get()
                : collect(),
            2 => $pemohon?->bagian_bidang_id
                ? User::role('Kepala Bidang')->where('bagian_bidang_id', $pemohon->bagian_bidang_id)->get()
                : collect(),
            3, 7 => User::role('Admin Kepegawaian')->get(),
            4 => User::role('Kepala Sub-Bagian')
                ->whereHas('bagianBidang', fn ($q) => $q->where('is_tu', true))
                ->whereHas('subBagianSeksi', fn ($q) => $q->where('nama', 'like', '%Kepegawaian%'))
                ->get(),
            5 => User::role('Kepala TU')->get(),
            6 => User::role('Kepala Kantor')->get(),
            default => collect(),
        };
    }

    protected function label(?int $step): string
    {
        return $this->labelStep[$step] ?? 'tahap berikutnya';
    }
}