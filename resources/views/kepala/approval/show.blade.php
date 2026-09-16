<x-app-layout>

    <!-- 1. BANNER BIRU FULL-WIDTH -->
    <div class="w-full pt-6 pb-8 bg-[#2A65F3]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-1 text-sm font-medium text-blue-100/80">
                <a href="{{ route('dashboard') }}" class="transition hover:text-white">Beranda</a> /
                <a href="{{ route('kepala.approval.index') }}" class="transition hover:text-white">Approval Cuti</a> /
                <span class="text-white">Detail Pengajuan</span>
            </div>
            <h2 class="text-3xl font-bold text-white">Detail Pengajuan Cuti</h2>
        </div>
    </div>

    @php
        $step = $data->approval_step ?? 0;
        $halted = in_array($step, [0, 10]); // 0 = Ditolak, 10 = Dibatalkan

        // Tahap mana yang boleh diproses oleh masing-masing role, dan step berikutnya kalau disetujui
        $mapPeran = [
            'Kepala Seksi'      => 1,
            'Kepala Bidang'     => 2,
            'Kepala Sub-Bagian' => 4,
            'Kepala TU'         => 5,
            'Kepala Kantor'     => 6,
        ];
        $bisaAksi = false;
        $user = auth()->user();
        foreach ($mapPeran as $peran => $stepPeran) {
            if ($user && $user->hasRole($peran) && $step == $stepPeran) {
                $bisaAksi = true;
                break;
            }
        }

        $bukti = is_array($data->bukti_pendukung)
            ? $data->bukti_pendukung
            : (json_decode($data->bukti_pendukung ?? '[]', true) ?: []);
    @endphp

    <div class="py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            <!-- KIRI: Informasi Detail -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Card: Rincian Cuti -->
                <div class="p-7 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <h3 class="mb-6 text-lg font-bold text-gray-900">Rincian Cuti</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Nomor pengajuan</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->kode_pengajuan }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tanggal pengajuan</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('l, d F Y, H:i') }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Jenis Cuti</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->jenisCuti->nama_cuti ?? '-' }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Durasi</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->durasi_hari }} Hari</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_mulai)->translatedFormat('l, d F Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Selesai</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_selesai)->translatedFormat('l, d F Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Aktif Kembali</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_selesai)->addDay()->translatedFormat('l, d F Y') }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Card: Keterangan -->
                <div class="p-7 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <h3 class="mb-6 text-lg font-bold text-gray-900">Keterangan</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                            <dd class="text-sm font-bold leading-relaxed text-gray-900 md:col-span-2">{{ $data->alasan }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Alamat Selama Cuti</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->lokasi ?? '-' }}</dd>
                        </div>
                    </div>
                    {{-- "Kontak Darurat" & "Pengganti Tugas" sengaja tidak ditampilkan dulu:
                         belum ada kolomnya di tabel pengajuan_cutis dan belum dikumpulkan
                         di form pengajuan. Lihat catatan di bawah jawaban ini. --}}
                </div>

                                <!-- Card: Lampiran -->
                <div class="p-7 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">Lampiran</h3>

                    @php $surat = $data->surat_aktif; @endphp

                    <div class="space-y-3">
                        {{-- Hanya versi TERBARU yang ditampilkan. Versi lama sengaja
                             disembunyikan agar pejabat di atasnya tidak salah unduh
                             berkas yang belum lengkap tanda tangannya. Riwayat penuh
                             tetap tersimpan di kolom dokumen_ttd & bisa dilihat Admin. --}}
                        @if($surat['file'])
                        <div class="flex items-center justify-between p-4 transition border rounded-xl
                            {{ $surat['is_ttd'] ? 'border-green-200 bg-green-50/50 hover:bg-green-50' : 'border-gray-200 bg-gray-50/50 hover:bg-gray-50' }}">
                            <div class="flex items-center min-w-0 space-x-3">
                                @if($surat['is_ttd'])
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                    <svg class="flex-shrink-0 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                @endif
                                <div class="min-w-0">
                                    <span class="block text-sm font-bold text-gray-800 truncate">{{ basename($surat['file']) }}</span>
                                    <p class="text-xs text-gray-500">
                                        {{ $surat['label'] }} &middot; {{ $surat['oleh'] }} ({{ $surat['peran'] }})
                                        @if($surat['waktu'])
                                            &middot; {{ \Carbon\Carbon::parse($surat['waktu'])->translatedFormat('d M Y, H:i') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/'.$surat['file']) }}" target="_blank"
                               class="flex-shrink-0 ml-3 text-sm font-bold transition {{ $surat['is_ttd'] ? 'text-green-700 hover:text-green-900' : 'text-[#2a64f5] hover:text-blue-800' }}">
                                Unduh File
                            </a>
                        </div>
                        @endif

                        @foreach($bukti as $file)
                        <div class="flex items-center justify-between p-4 transition border border-gray-200 bg-gray-50/50 hover:bg-gray-50 rounded-xl">
                            <div class="flex items-center min-w-0 space-x-3">
                                <svg class="flex-shrink-0 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span class="text-sm font-bold text-gray-800 truncate">{{ basename($file) }} <span class="font-normal text-gray-400">(bukti pendukung)</span></span>
                            </div>
                            <a href="{{ asset('storage/'.$file) }}" target="_blank" class="flex-shrink-0 ml-3 text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition">Unduh File</a>
                        </div>
                        @endforeach

                        @if(!empty($data->dokumen_ttd))
                        <div class="pt-3 mt-3 border-t border-dashed border-gray-200">
                            <p class="mb-2 text-xs font-bold tracking-wide text-gray-400 uppercase">Dokumen Bertanda Tangan</p>
                            @foreach($data->dokumen_ttd as $ttd)
                            <div class="flex items-center justify-between p-4 mb-2 transition border border-green-200 bg-green-50/50 hover:bg-green-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <span class="text-sm font-bold text-gray-800">{{ basename($ttd['file']) }}</span>
                                        <p class="text-xs text-gray-500">Ditandatangani {{ $ttd['nama'] ?? '-' }} ({{ $ttd['peran'] ?? '-' }}) &middot; {{ \Carbon\Carbon::parse($ttd['waktu'])->translatedFormat('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/'.$ttd['file']) }}" target="_blank" class="flex-shrink-0 text-sm font-bold text-green-700 hover:text-green-900 transition">Unduh File</a>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KANAN: Timeline & Aksi -->
            <div class="lg:col-span-1">
                <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <h3 class="pb-3 mb-4 text-lg font-bold text-gray-800 border-b border-gray-100">Alur Persetujuan</h3>

                    <div class="relative pb-2 mt-4 ml-3 space-y-6 border-l-2 border-gray-200">

                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full bg-[#2A65F3] ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">Pengajuan Dikirim</h3>
                            <p class="mt-1 text-xs text-gray-500">Oleh: {{ $data->user->name ?? 'Pegawai' }}</p>
                        </div>

                        @php
                            $rows = [
                                1 => 'Persetujuan Kepala Seksi',
                                2 => 'Persetujuan Kepala Bidang',
                                3 => 'Verifikasi Admin',
                                4 => 'Persetujuan Kepala Sub-Bagian',
                                5 => 'Persetujuan Kepala TU',
                                6 => 'Persetujuan Kepala Kantor',
                                7 => 'Finalisasi Admin',
                            ];
                        @endphp

                        @foreach($rows as $n => $label)
                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $step > $n ? 'bg-[#2A65F3]' : ($step == $n ? ($halted ? 'bg-red-500' : 'bg-yellow-400') : 'bg-gray-200') }} ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">{{ $label }}</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $step > $n ? 'Telah Disetujui' : ($step == $n ? ($halted ? 'Dihentikan' : 'Menunggu Tindakan') : 'Menunggu Tahap Sebelumnya') }}
                            </p>
                        </div>
                        @endforeach

                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $step >= 8 ? 'bg-[#2A65F3]' : 'bg-gray-200' }} ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">{{ $halted ? 'Pengajuan Dihentikan' : 'Cuti Disetujui' }}</h3>
                        </div>
                    </div>

                    @if($bisaAksi)
                    <div class="flex flex-col gap-3 pt-6 mt-8 border-t border-gray-100">
                        <form id="form-approve" action="{{ route('kepala.approval.approve', $data->id) }}" method="POST" enctype="multipart/form-data" class="w-full">
                            @csrf @method('PUT')

                            <label class="block mb-1 text-xs font-semibold text-gray-600">
                                Upload Dokumen yang Sudah Ditandatangani <span class="font-normal text-gray-400">(opsional)</span>
                            </label>
                            <input type="file" name="dokumen_ttd" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="block w-full mb-3 text-xs text-gray-600 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2A65F3] hover:file:bg-blue-100">

                            <button type="button" onclick="konfirmasiAksi('approve')" class="w-full px-6 py-2.5 text-sm font-semibold text-white transition bg-[#2A65F3] rounded-lg shadow-sm hover:bg-blue-700">
                                Setujui & Teruskan
                            </button>
                        </form>
                        <form id="form-revisi" action="{{ route('kepala.approval.revisi', $data->id) }}" method="POST" class="w-full">
                            @csrf @method('PUT')
                            <button type="button" onclick="konfirmasiAksi('revisi')" class="w-full px-6 py-2.5 text-sm font-semibold text-yellow-700 transition bg-white border border-yellow-300 rounded-lg shadow-sm hover:bg-yellow-50 hover:border-yellow-400">
                                Kembalikan (Revisi)
                            </button>
                        </form>
                        <form id="form-reject" action="{{ route('kepala.approval.reject', $data->id) }}" method="POST" class="w-full">
                            @csrf @method('PUT')
                            <button type="button" onclick="konfirmasiAksi('reject')" class="w-full px-6 py-2.5 text-sm font-semibold text-red-600 transition bg-white border border-red-200 rounded-lg hover:bg-red-50 hover:border-red-300">
                                Tolak Permanen
                            </button>
                        </form>
                    </div>
                    @else
                    <p class="pt-6 mt-8 text-sm italic text-center text-gray-400 border-t border-gray-100">
                        Pengajuan ini bukan/belum di meja Anda saat ini.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiAksi(jenis) {
            let config = {};
            if (jenis === 'approve') {
                config = { title: "Setujui Pengajuan?", text: "Berkas akan diteruskan ke tahap selanjutnya.", icon: "question", confirmButtonText: "Ya, Setujui", confirmButtonColor: "#2A65F3" };
            } else if (jenis === 'revisi') {
                config = { title: "Kembalikan Berkas?", text: "Pegawai harus merevisi dokumen ini.", icon: "warning", confirmButtonText: "Ya, Kembalikan", confirmButtonColor: "#eab308" };
            } else if (jenis === 'reject') {
                config = { title: "Tolak Permanen?", text: "Pengajuan ini tidak dapat diproses lagi.", icon: "error", confirmButtonText: "Ya, Tolak", confirmButtonColor: "#dc2626" };
            }
            Swal.fire({
                ...config, showCancelButton: true, cancelButtonText: "Batal", cancelButtonColor: "#6b7280", reverseButtons: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100', title: 'text-xl font-bold text-gray-800' }
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('form-' + jenis).submit();
            });
        }
    </script>
</x-app-layout>