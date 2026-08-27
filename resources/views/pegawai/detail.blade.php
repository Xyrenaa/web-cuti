<x-app-layout>
    <!-- Header Biru (Diperbarui dengan Tombol Kembali) -->
   <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">
            <a href="{{ route('pengajuan.riwayat') }}" class="hover:underline">Riwayat Pengajuan</a> / Detail
        </div>
        <h1 class="text-white text-3xl font-bold">Detail Pengajuan Cuti</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
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
                        <!-- Step 1: Dikirim -->
                        <div class="relative pl-6">
                            <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-[#2A65F3] ring-4 ring-white"></span>
                            <h4 class="text-sm font-bold text-gray-900">Pengajuan Dikirim</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $pengajuan->created_at->format('d M H:i') }}</p>
                        </div>

                        <!-- Step 2: Menunggu Persetujuan / Proses -->
                        <div class="relative pl-6">
                            @php
                                $isProses = str_contains($pengajuan->status_pengajuan, 'Menunggu');
                                $isSelesai = in_array($pengajuan->status_pengajuan, ['Disetujui', 'Ditolak']);
                                $dotColor = ($isProses || $isSelesai) ? 'bg-[#2A65F3]' : 'bg-gray-300';
                            @endphp
                            <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $dotColor }} ring-4 ring-white"></span>
                            <h4 class="text-sm font-bold {{ ($isProses || $isSelesai) ? 'text-gray-900' : 'text-gray-400' }}">Proses Persetujuan</h4>
                            <p class="text-xs {{ ($isProses || $isSelesai) ? 'text-blue-600 font-semibold' : 'text-gray-400' }} mt-1">
                                {{ $isProses ? $pengajuan->status_pengajuan : ($isSelesai ? 'Telah diproses' : 'Menunggu antrean') }}
                            </p>
                        </div>

                        <!-- Step 3: Hasil Akhir -->
                        <div class="relative pl-6">
                            @php
                                $finalColor = 'bg-gray-300';
                                $finalText = 'text-gray-400';
                                if($pengajuan->status_pengajuan == 'Disetujui') { $finalColor = 'bg-green-500'; $finalText = 'text-green-600 font-bold'; }
                                if($pengajuan->status_pengajuan == 'Ditolak') { $finalColor = 'bg-red-500'; $finalText = 'text-red-600 font-bold'; }
                            @endphp
                            <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $finalColor }} ring-4 ring-white"></span>
                            <h4 class="text-sm font-bold {{ $isSelesai ? 'text-gray-900' : 'text-gray-400' }}">Keputusan Akhir</h4>
                            <p class="text-xs {{ $finalText }} mt-1">
                                {{ $isSelesai ? 'Pengajuan ' . $pengajuan->status_pengajuan : 'Belum ada keputusan' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="max-w-6xl mx-auto mt-8">
            <a href="{{ route('pengajuan.riwayat') }}" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-100 transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </div>
</x-app-layout>