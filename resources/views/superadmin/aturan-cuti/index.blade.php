<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Superadmin</div>
        <h2 class="font-bold text-3xl text-white leading-tight">Aturan Cuti</h2>
    </x-slot>

    @php
        $tampil = fn ($v) => $v === null ? 'Tanpa batas' : $v . ' hari kerja';
        $adaOld = session()->hasOldInput('jatah_cuti_dasar');
        $tanpaBatasAwal = $adaOld ? (bool) old('tanpa_batas') : $nilai['batas_sisa_cuti_dibawa'] === null;
        $batasAwal = $adaOld ? old('batas_sisa_dibawa') : ($nilai['batas_sisa_cuti_dibawa'] ?? '');
        $input = 'mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400';
        $label = 'block text-sm font-semibold text-gray-700';
        $flash = [
            ['success', 'bg-green-100 border-green-400 text-green-800'],
            ['warning', 'bg-amber-100 border-amber-400 text-amber-800'],
            ['error',   'bg-red-100 border-red-400 text-red-800'],
        ];
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @foreach($flash as [$kunci, $kelas])
                @if(session($kunci))
                    <div class="border px-4 py-3 rounded {{ $kelas }}">{{ session($kunci) }}</div>
                @endif
            @endforeach

            <div class="bg-blue-50 border border-blue-200 text-blue-900 text-sm rounded-xl px-5 py-4 leading-relaxed">
                Perubahan di sini berlaku untuk <strong>perhitungan berikutnya</strong>. Cuti yang sudah disetujui tidak dihitung ulang.
                Mengubah jatah dasar secara bawaan <strong>tidak mengubah jatah pegawai yang sudah ada</strong>; nilai baru dipakai saat
                <strong>Tutup Tahun</strong> berikutnya dan untuk pegawai baru. Centang opsi "Terapkan sekarang" di bawah bila kenaikan
                harus langsung berlaku. Koreksi per pegawai tetap lewat Rekap.
            </div>

            {{-- ===== FORM ===== --}}
            <form id="form-aturan" method="POST" action="{{ route('superadmin.aturan-cuti.update') }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6"
                  x-data="{ tanpa: {{ $tanpaBatasAwal ? 'true' : 'false' }} }">
                @csrf @method('PUT')

                <h3 class="text-lg font-bold text-gray-800">Pengaturan Umum</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $label }}">Jatah cuti tahunan dasar (hari kerja/tahun)</label>
                        <input type="number" name="jatah_cuti_dasar" min="1" max="60" required
                               value="{{ old('jatah_cuti_dasar', $nilai['jatah_cuti_dasar']) }}" class="{{ $input }}">
                        <p class="text-xs text-gray-400 mt-1">Berlaku sama untuk PNS dan PPPK.</p>
                        @error('jatah_cuti_dasar')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                <label class="mt-3 flex items-start gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="terapkan_sekarang" value="1" @checked(old('terapkan_sekarang'))
                                   class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                Terapkan sekarang ke jatah tahun berjalan <strong>{{ $totalPegawai }} pegawai</strong> yang sudah ada
                                (hanya untuk <em>kenaikan</em>). Cuti yang sudah terpakai tidak berubah, contoh: sisa 7 dari 12 menjadi sisa 10 dari 15.
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="{{ $label }}">Batas sisa cuti dibawa ke tahun berikutnya (hari kerja)</label>
                        <input type="number" name="batas_sisa_dibawa" min="0" max="365"
                               :disabled="tanpa" :required="!tanpa"
                               value="{{ $batasAwal }}" class="{{ $input }}">
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="tanpa_batas" value="1" x-model="tanpa" @checked($tanpaBatasAwal)
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Tanpa batas (seluruh sisa dibawa)
                        </label>
                        @error('batas_sisa_dibawa')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 space-y-4">
                    <div>
                        <label class="{{ $label }}">Dasar peraturan</label>
                        <input type="text" name="dasar_peraturan" value="{{ old('dasar_peraturan') }}" required
                               placeholder="mis. Surat Edaran Kepala BKN No. ... Tahun ..." class="{{ $input }}">
                        @error('dasar_peraturan')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <label class="flex items-start gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="pernyataan" value="1" required
                               class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>Saya menyatakan bahwa peraturan di atas benar-benar telah berubah dan berlaku di instansi.</span>
                    </label>
                    @error('pernyataan')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                </div>

                <div class="pt-1">
                    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>

            {{-- ===== RIWAYAT ===== --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Perubahan</h3>
                    <p class="text-xs text-gray-400">Hanya terlihat oleh Superadmin.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Waktu</th>
                                <th class="px-4 py-3 text-left">Aturan</th>
                                <th class="px-4 py-3 text-left">Perubahan</th>
                                <th class="px-4 py-3 text-left">Dasar Peraturan</th>
                                <th class="px-4 py-3 text-left">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($riwayat as $r)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $r->pernyataan_pada->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ \App\Models\PengaturanCuti::LABEL[$r->kunci] ?? $r->kunci }}</td>
                                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                        {{ $tampil($r->nilai_lama === null ? null : (int) $r->nilai_lama) }} →
                                        <strong>{{ $tampil($r->nilai_baru === null ? null : (int) $r->nilai_baru) }}</strong>
                                            @if($r->diterapkan_ke_pegawai)
                                            <div class="text-[11px] text-indigo-600 font-semibold">Diterapkan ke {{ $r->jumlah_pegawai_terdampak }} pegawai</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">{{ $r->dasar_peraturan }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $r->pelaku->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada perubahan aturan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-paginasi :paginator="$riwayat" />
            </div>
        </div>
    </div>

    <script>
        const awal = @js(['jatah' => $nilai['jatah_cuti_dasar'], 'batas' => $nilai['batas_sisa_cuti_dibawa']]);
        const fmt  = (v) => v === null ? 'Tanpa batas' : v + ' hari kerja';

        document.getElementById('form-aturan').addEventListener('submit', function (e) {
            // Cadangan: kalau popup tidak termuat, kirim biasa (server tetap memvalidasi).
            if (typeof konfirmasiSubmit !== 'function' || typeof Swal === 'undefined') {
                return;
            }

            e.preventDefault();

            const jatah = parseInt(this.elements['jatah_cuti_dasar'].value, 10);
            const tanpa = this.elements['tanpa_batas'].checked;
            const batas = tanpa ? null : parseInt(this.elements['batas_sisa_dibawa'].value, 10);

            const baris = [];
            if (jatah !== awal.jatah) baris.push('Jatah cuti dasar: <b>' + fmt(awal.jatah) + '</b> → <b>' + fmt(jatah) + '</b>');
            if (batas !== awal.batas) baris.push('Batas sisa dibawa: <b>' + fmt(awal.batas) + '</b> → <b>' + fmt(batas) + '</b>');
                        const terapkan = this.elements['terapkan_sekarang'].checked;

            if (terapkan && jatah <= awal.jatah) {
                Swal.fire({ icon: 'warning', title: 'Tidak bisa diterapkan sekarang',
                    text: 'Penerapan langsung hanya untuk kenaikan jatah. Penurunan berlaku mulai Tutup Tahun berikutnya.',
                    confirmButtonColor: '#2A65F3' });
                return;
            }

            if (terapkan && jatah !== awal.jatah) {
                baris.push('<b>Diterapkan sekarang</b> ke seluruh pegawai: jatah tahun berjalan +' + (jatah - awal.jatah) + ' hari (cuti terpakai tidak berubah)');
            }

            if (!baris.length) {
                Swal.fire({ icon: 'info', title: 'Tidak ada perubahan', text: 'Nilai yang diisi sama dengan aturan yang berlaku.', confirmButtonColor: '#2A65F3' });
                return;
            }

            konfirmasiSubmit(this, {
                icon: 'warning',
                title: 'Simpan Perubahan Aturan?',
                html: '<div style="text-align:left;font-size:14px;line-height:1.7">'
                    + baris.map(function (b) { return '<div>' + b + '</div>'; }).join('')
                    + '<div style="margin-top:12px;opacity:.75">Dasar: ' + escHtml(this.elements['dasar_peraturan'].value.trim()) + '</div></div>',
                confirmButtonText: 'Ya, Peraturan Memang Berubah',
            });
        });
    </script>
</x-superadmin-layout>