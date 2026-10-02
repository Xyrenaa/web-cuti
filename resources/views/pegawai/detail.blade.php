<x-app-layout>
    <!-- Header Biru (Diperbarui dengan Tombol Kembali) -->
   <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">
            <a href="{{ route('pengajuan.riwayat') }}" class="hover:underline">Riwayat Pengajuan</a> / Detail
        </div>
        <h1 class="text-white text-3xl font-bold">Detail Pengajuan Cuti</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
        @if(session('success'))
            <div class="max-w-6xl mx-auto mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="max-w-6xl mx-auto mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">{{ session('error') }}</div>
        @endif
        @error('plh_user_id')
            <div class="max-w-6xl mx-auto mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">{{ $message }}</div>
        @enderror
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM KIRI: Rincian & Lampiran -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Kotak Rincian Cuti (Tetap sama) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 tracking-wider uppercase border-b border-gray-100 pb-3 mb-4">Rincian Cuti</h3>
                    <table class="w-full text-sm text-gray-600">
                        <tbody class="space-y-3">
                            <tr><td class="py-2 w-1/3">Jenis Cuti</td><td class="py-2 font-semibold text-gray-900">{{ $pengajuan->jenisCuti->nama_cuti ?? '-' }}</td></tr>
                            <tr><td class="py-2">Tanggal Pengajuan</td><td class="py-2 font-semibold text-gray-900">{{ $pengajuan->created_at->translatedFormat('l, d F Y, H:i') }}</td></tr>
                            <tr><td class="py-2">Durasi Cuti</td><td class="py-2 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d M Y') }}</td></tr>
                            <tr><td class="py-2">Lokasi Selama Cuti</td><td class="py-2 font-semibold text-gray-900">{{ $pengajuan->lokasi }}</td></tr>
                            <tr><td class="py-2 align-top">Alasan</td><td class="py-2 font-semibold text-gray-900">{{ $pengajuan->alasan }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kotak Lampiran Dokumen (Diperbarui logika potong nama) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 tracking-wider uppercase border-b border-gray-100 pb-3 mb-4">Dokumen Lampiran</h3>
                    <div class="space-y-3">
                        
                        <!-- Lampiran Wajib -->
                        <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-100 rounded-lg">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-[#2A65F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <div>
                                    <span class="text-sm font-semibold text-gray-800 block">Surat Pengajuan (Wajib)</span>
                                    <!-- Logika pemotong string jika ada format waktu di depannya -->
                                    <span class="text-xs text-gray-500">{{ Str::after(basename($pengajuan->surat_pengajuan), '_wajib_') ?: basename($pengajuan->surat_pengajuan) }}</span>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $pengajuan->surat_pengajuan) }}" target="_blank" download class="text-[#2A65F3] text-sm font-semibold hover:underline">Download</a>
                        </div>

                        <!-- Render Array Multi-Lampiran (Bukti Pendukung) -->
                        @if($pengajuan->bukti_pendukung)
                            @foreach($pengajuan->bukti_pendukung as $key => $bukti)
                            <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800 block">Bukti Pendukung #{{ $loop->iteration }}</span>
                                        <span class="text-xs text-gray-500">{{ Str::after(basename($bukti), '_opsi' . $key . '_') ?: basename($bukti) }}</span>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $bukti) }}" target="_blank" download class="text-gray-600 text-sm font-semibold hover:text-[#2A65F3] hover:underline">Download</a>
                            </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
            <!-- KOLOM KANAN: Timeline Status -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-8">
                    <h3 class="text-sm font-bold text-gray-800 tracking-wider uppercase border-b border-gray-100 pb-3 mb-6">Status Persetujuan</h3>
                    
                    <div class="relative border-l-2 border-gray-200 ml-3 space-y-8">
                        @php
                            // Satu sumber kebenaran: approval_step. Disamakan persis dengan
                            // $stepFinal di PengajuanController::batal() supaya tombol batal
                            // di bawah konsisten dengan validasi server-nya.
                            $step = $pengajuan->approval_step;
                            $isFinal = in_array($step, [0, 8, 10]);
                        @endphp

                        <!-- Step 1: Dikirim -->
                        <div class="relative pl-6">
                            <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-[#2A65F3] ring-4 ring-white"></span>
                            <h4 class="text-sm font-bold text-gray-900">Pengajuan Dikirim</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $pengajuan->created_at->format('d M H:i') }}</p>
                        </div>

                        <!-- Step 2: Proses Persetujuan -->
                        <div class="relative pl-6">
                            <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-[#2A65F3] ring-4 ring-white"></span>
                            <h4 class="text-sm font-bold text-gray-900">Proses Persetujuan</h4>
                            <p class="text-xs text-blue-600 font-semibold mt-1">
                                {{ $pengajuan->status_label }}
                            </p>
                        </div>

                        <!-- Step 3: Hasil Akhir -->
                        <div class="relative pl-6">
                            @php
                                $finalColor = 'bg-gray-300';
                                $finalText = 'text-gray-400';
                                if ($step === 8) { $finalColor = 'bg-green-500'; $finalText = 'text-green-600 font-bold'; }
                                if ($step === 0) { $finalColor = 'bg-red-500'; $finalText = 'text-red-600 font-bold'; }
                                if ($step === 10) { $finalColor = 'bg-gray-500'; $finalText = 'text-gray-600 font-bold'; }
                            @endphp
                            <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $finalColor }} ring-4 ring-white"></span>
                            <h4 class="text-sm font-bold {{ $isFinal ? 'text-gray-900' : 'text-gray-400' }}">Keputusan Akhir</h4>
                            <p class="text-xs {{ $finalText }} mt-1">
                                {{ $isFinal ? $pengajuan->status_label : 'Belum ada keputusan' }}
                            </p>
                        </div>
                    </div>

                    {{-- ===== PLH (Pelaksana Harian) ===== --}}
                    @if($pengajuan->butuhPlh())
                        @if($step === 7)
                            <div class="mt-6 p-4 rounded-lg border bg-blue-50 border-blue-200">
                                <p class="text-xs font-bold uppercase tracking-wide text-blue-700 mb-1">Tunjuk PLH</p>
                                <p class="text-xs text-blue-800 mb-3">
                                    Pengajuan Anda sudah melewati semua persetujuan. Pilih PLH (Pelaksana Harian) yang menggantikan tugas Anda
                                    selama cuti agar Admin dapat memfinalisasi.
                                </p>

                                @if($kandidatPlh->isEmpty())
                                    <p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded p-3">
                                        Tidak ada pegawai yang dapat dipilih (belum ada pegawai di unit Anda, atau semuanya sedang cuti pada tanggal yang sama).
                                        Hubungi Admin Kepegawaian.
                                    </p>
                                @else
                                    <form action="{{ route('pengajuan.plh.store', $pengajuan->id) }}" method="POST">
                                        @csrf
                                        <select name="plh_user_id" required
                                            class="w-full mb-3 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">-- Pilih PLH --</option>
                                            @foreach($kandidatPlh as $kandidat)
                                                <option value="{{ $kandidat->id }}" @selected((int) $pengajuan->plh_user_id === $kandidat->id)>
                                                    {{ $kandidat->name }}{{ $kandidat->subBagianSeksi ? ' - ' . $kandidat->subBagianSeksi->nama : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-white transition bg-[#2A65F3] rounded-lg hover:bg-blue-700">
                                            {{ $pengajuan->plh_user_id ? 'Ganti PLH' : 'Tunjuk PLH' }}
                                        </button>
                                    </form>
                                @endif

                                @if($pengajuan->plh)
                                    <p class="mt-3 text-xs text-gray-600">PLH saat ini: <span class="font-semibold">{{ $pengajuan->plh->name }}</span></p>
                                @endif
                            </div>
                        @elseif($pengajuan->plh)
                            <div class="mt-6 p-4 rounded-lg border bg-gray-50 border-gray-200">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">PLH Selama Cuti</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $pengajuan->plh->name }}</p>
                            </div>
                        @endif
                    @endif

                    @if($pengajuan->catatan_penolakan)
                    <div class="mt-6 p-4 rounded-lg border {{ $step === 0 ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200' }}">
                        <p class="text-xs font-bold uppercase tracking-wide {{ $step === 0 ? 'text-red-700' : 'text-amber-700' }} mb-1">
                            {{ $step === 0 ? 'Alasan Penolakan' : 'Catatan Revisi dari Atasan' }}
                        </p>
                        <p class="text-sm {{ $step === 0 ? 'text-red-800' : 'text-amber-800' }}">{{ $pengajuan->catatan_penolakan }}</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
        <div class="max-w-6xl mx-auto mt-8 flex items-center gap-4">
            <!-- Tombol Kembali -->
            <a href="{{ route('pengajuan.riwayat') }}" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-100 transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>

            <!-- Tombol Batalkan hanya muncul kalau belum di step final (sinkron dengan $isFinal di atas) -->
            @if(!$isFinal)
            <form id="form-batal-detail" action="{{ route('pengajuan.batal', $pengajuan->id) }}" method="POST">
                @csrf
                <button type="button" onclick="konfirmasiBatalDetail()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
                    Batalkan Pengajuan
                </button>
            </form>
            @endif
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiBatalDetail() {
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                confirmButtonColor: '#dc2626',
                cancelButtonText: 'Kembali',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100', title: 'text-xl font-bold text-gray-800' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-batal-detail').submit();
                }
            });
        }
    </script>
</x-app-layout>