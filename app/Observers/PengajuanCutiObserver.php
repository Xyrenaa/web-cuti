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

         // PLH baru ditunjuk / diganti -> kabari PLH-nya.
        if ($pengajuan->wasChanged('plh_user_id') && $pengajuan->plh_user_id) {
            $this->kabariPlhDitunjuk($pengajuan);

            // PLH pertama kali dipilih saat step 7: gerbang Admin terbuka, baru Admin dikabari.
            if ((int) $pengajuan->approval_step === 7 && ! $pengajuan->getOriginal('plh_user_id')) {
                $this->kabariMejaTujuan($pengajuan);
            }
        }

        if (! $pengajuan->wasChanged('approval_step')) {
            return;
        }

        $stepLama = (int) $pengajuan->getOriginal('approval_step');
        $stepBaru = (int) $pengajuan->approval_step;

        $this->kabariPemohon($pengajuan, $stepLama, $stepBaru);
        $this->kabariMejaTujuan($pengajuan);
        $this->kabariTunjukPlh($pengajuan, $stepBaru);       
    }

        /** Begitu pengajuan Kepala (bukan Kakan) sampai step 7, minta dia menunjuk PLH. */
    protected function kabariTunjukPlh(PengajuanCuti $pengajuan, int $stepBaru): void
    {
        if ($stepBaru !== 7 || $pengajuan->plh_user_id || ! $pengajuan->butuhPlh()) {
            return;
        }

        $pengajuan->user->notify(new StatusCutiNotification(
            'Silakan Tunjuk PLH',
            "Pengajuan cuti {$pengajuan->kode_pengajuan} sudah melewati seluruh persetujuan. Silakan tunjuk PLH (Pelaksana Harian) selama Anda cuti agar Admin dapat memfinalisasi pengajuan Anda.",
            [
                'tipe'           => 'aksi',
                'kode_pengajuan' => $pengajuan->kode_pengajuan,
                'pengajuan_id'   => $pengajuan->id,
                'url'            => route('pegawai.detail', $pengajuan->id),
            ]
        ));
    }
    /** Notifikasi ke pegawai yang ditunjuk menjadi PLH. */
    protected function kabariPlhDitunjuk(PengajuanCuti $pengajuan): void
    {
        $plh    = $pengajuan->plh;
        $kepala = $pengajuan->user;

        if (! $plh || ! $kepala) {
            return;
        }

        $mulai   = \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->translatedFormat('d F Y');
        $selesai = \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->translatedFormat('d F Y');

        $olehSuperadmin = Auth::user()?->hasRole('Superadmin');

        $pesan = $olehSuperadmin
            ? "Superadmin menetapkan Anda sebagai PLH (Pelaksana Harian) menggantikan {$kepala->name} pada {$mulai} s.d. {$selesai}."
            : "{$kepala->name} menunjuk Anda sebagai PLH (Pelaksana Harian) pada {$mulai} s.d. {$selesai}. Penunjukan baru berlaku setelah pengajuan cuti beliau disetujui final.";

        $plh->notify(new StatusCutiNotification(
            'Anda Ditunjuk Sebagai PLH',
            $pesan,
            [
                'tipe'           => 'status',
                'kode_pengajuan' => $pengajuan->kode_pengajuan,
                'pengajuan_id'   => $pengajuan->id,
                'url'            => route('dashboard'),
            ]
        ));
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

                // Step 7 milik Kepala yang wajib PLH tapi belum menunjuk: jangan dulu kabari Admin
                // (finalisasi diblokir sampai PLH dipilih). Admin dikabari setelah PLH ditunjuk.
        if ($step === 7 && $pengajuan->butuhPlh() && ! $pengajuan->plh_user_id) {
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