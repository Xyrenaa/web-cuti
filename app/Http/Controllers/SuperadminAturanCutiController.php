<?php

namespace App\Http\Controllers;

use App\Models\PengaturanCuti;
use App\Models\RiwayatPengaturanCuti;
use App\Services\AturanCuti;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SuperadminAturanCutiController extends Controller
{
    public function index()
    {
        $nilai        = AturanCuti::semua();
        $riwayat      = RiwayatPengaturanCuti::with('pelaku')->latest()->paginate(10)->onEachSide(1);
        $totalPegawai = User::bukanSuperadmin()->count();

        return view('superadmin.aturan-cuti.index', compact('nilai', 'riwayat', 'totalPegawai'));
    }

    public function update(Request $request)
    {
        $tanpaBatas = $request->boolean('tanpa_batas');

        $data = $request->validate([
            'jatah_cuti_dasar'  => ['required', 'integer', 'min:1', 'max:60'],
            'batas_sisa_dibawa' => ['nullable', 'integer', 'min:0', 'max:365', Rule::requiredIf(! $tanpaBatas)],
            'dasar_peraturan'   => ['required', 'string', 'max:255'],
            'pernyataan'        => ['accepted'],
            'terapkan_sekarang' => ['nullable', 'boolean'],
        ], [
            'jatah_cuti_dasar.required'  => 'Jatah cuti dasar wajib diisi.',
            'jatah_cuti_dasar.max'       => 'Jatah cuti dasar maksimal 60 hari kerja.',
            'batas_sisa_dibawa.required' => 'Isi batas sisa cuti, atau centang "Tanpa batas".',
            'dasar_peraturan.required'   => 'Dasar peraturan wajib diisi (nomor atau nama surat/peraturan).',
            'pernyataan.accepted'        => 'Anda harus menyatakan bahwa peraturan ini benar-benar telah berubah.',
        ]);

        $lama = AturanCuti::semua();
        $baru = [
            PengaturanCuti::JATAH_DASAR       => (int) $data['jatah_cuti_dasar'],
            PengaturanCuti::BATAS_SISA_DIBAWA => $tanpaBatas ? null : (int) $data['batas_sisa_dibawa'],
        ];

        $berubah = collect($baru)->filter(fn ($nilai, $kunci) => $nilai !== $lama[$kunci]);

        if ($berubah->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ada nilai yang berubah dari aturan yang berlaku.');
        }

        $selisih  = $baru[PengaturanCuti::JATAH_DASAR] - $lama[PengaturanCuti::JATAH_DASAR];
        $terapkan = $request->boolean('terapkan_sekarang') && $berubah->has(PengaturanCuti::JATAH_DASAR);

        if ($terapkan && $selisih <= 0) {
            return back()->withInput()->with(
                'error',
                'Penerapan langsung hanya untuk KENAIKAN jatah. Penurunan berlaku mulai Tutup Tahun berikutnya agar sisa cuti pegawai tidak menjadi negatif.'
            );
        }

        $terdampak = null;

        DB::transaction(function () use ($berubah, $lama, $data, $request, $terapkan, $selisih, &$terdampak) {
            if ($terapkan) {
                $dasarLama = (int) $lama[PengaturanCuti::JATAH_DASAR];
                $terdampak = User::bukanSuperadmin()->count();

                // Naikkan jatah tahun berjalan semua pegawai sebesar selisih. Cuti terpakai (riwayat pengajuan
                // + koreksi_terpakai) tidak disentuh, jadi sisa cuti ikut naik sebesar selisih yang sama.
                User::bukanSuperadmin()->update([
                    'jatah_cuti' => DB::raw('COALESCE(jatah_cuti, ' . $dasarLama . ') + ' . (int) $selisih),
                ]);
            }

            foreach ($berubah as $kunci => $nilaiBaru) {
                $untukJatah = $terapkan && $kunci === PengaturanCuti::JATAH_DASAR;

                PengaturanCuti::updateOrCreate(
                    ['kunci' => $kunci],
                    ['nilai' => $nilaiBaru, 'diubah_oleh' => $request->user()->id]
                );

                RiwayatPengaturanCuti::create([
                    'kunci'                    => $kunci,
                    'nilai_lama'               => $lama[$kunci] === null ? null : (string) $lama[$kunci],
                    'nilai_baru'               => $nilaiBaru === null ? null : (string) $nilaiBaru,
                    'diterapkan_ke_pegawai'    => $untukJatah,
                    'jumlah_pegawai_terdampak' => $untukJatah ? $terdampak : null,
                    'dasar_peraturan'          => $data['dasar_peraturan'],
                    'pernyataan_pada'          => now(),
                    'dilakukan_oleh'           => $request->user()->id,
                ]);
            }
        });

        AturanCuti::lupakan();

        Log::info('Superadmin mengubah aturan cuti', [
            'superadmin_id' => $request->user()->id,
            'kunci'         => $berubah->keys()->all(),
            'diterapkan_ke' => $terdampak,
        ]);

        $pesan = 'Aturan cuti berhasil diperbarui dan tercatat di riwayat.';
        if ($terdampak !== null) {
            $pesan .= " Jatah tahun berjalan {$terdampak} pegawai dinaikkan {$selisih} hari; cuti yang sudah terpakai tidak berubah.";
        }

        return redirect()->route('superadmin.aturan-cuti.index')->with('success', $pesan);
    }
}