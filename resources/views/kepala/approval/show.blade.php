<x-app-layout>
    
    <!-- 1. BANNER BIRU FULL-WIDTH -->
    <div class="w-full pt-6 pb-8 bg-[#2A65F3]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-1 text-sm font-medium text-blue-100/80">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">Beranda</a> / 
                <a href="{{ route('kepala.approval.index') }}" class="hover:text-white transition">Approval Cuti</a> / 
                <span class="text-white">Detail Pengajuan</span>
            </div>
            <!-- Judul Halaman -->
            <h2 class="text-3xl font-bold text-white">Detail Pengajuan Cuti</h2>
        </div>
    </div>

    <!-- 2. AREA KONTEN UTAMA (Card Rincian, Alur, dll) -->
    <div class="py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Rincian Cuti (Kodingan aslimu biarkan di bawah sini) -->
            <!-- ... -->
                <!-- KIRI: Informasi Detail -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card: Rincian Cuti -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Rincian Cuti</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Nomor pengajuan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">CT-2026-000148</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Tanggal pengajuan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Minggu, 15 Maret 2026, 09:12</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Jenis Cuti</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Cuti Tahunan</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Durasi</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">3 Hari</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Tanggal Mulai</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Senin, 16 Maret 2026</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Selesai</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Rabu, 18 Maret 2026</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Aktif Kembali</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Kamis, 19 Maret 2026</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Keterangan -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Keterangan</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Alasan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2 leading-relaxed">Menghadiri acara pernikahan adik kandung di luar kota.</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Alamat Selama Cuti</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Jl. Nameless No. 7 Surabaya</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Kontak Darurat</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">082133550336 (Ibu Rahma)</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Pengganti Tugas</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Liino (NIP: 199105142017041002)</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Lampiran -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-4">Lampiran</h3>
                        <div class="border border-gray-200 rounded-xl p-4 flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span class="text-sm font-bold text-gray-800">surat-undangan-keluarga.pdf</span>
                            </div>
                            <a href="#" class="text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition">Unduh File</a>
                        </div>
                    </div>

                </div>

<!-- KANAN: Timeline (View Only untuk Admin) -->
                <div class="lg:col-span-1">
                    
                    <!-- Card Alur Persetujuan -->
<div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
    <h3 class="pb-3 mb-4 text-lg font-bold text-gray-800 border-b border-gray-100">Alur Persetujuan</h3>
    
    <!-- Timeline Vertikal -->
    <div class="relative pb-2 mt-4 ml-3 space-y-6 border-l-2 border-gray-200">
        
        <!-- Step 0: Dikirim (Semua Kepala bisa lihat ini) -->
        <div class="relative pl-6">
            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full bg-blue-600 ring-4 ring-white"></span>
            <h3 class="text-sm font-semibold text-gray-800">Pengajuan Dikirim</h3>
            <p class="mt-1 text-xs text-gray-500">Oleh: {{ $data->user->name }}</p>
        </div>
        
        <!-- Step 1: Kepala Seksi (Hanya muncul jika yang login adalah Kasi atau jabatan di atasnya) -->
        @hasanyrole(['Kepala Seksi', 'Kepala Bidang', 'Kepala Sub Bagian', 'Kepala TU', 'Kepala Kantor'])
        <div class="relative pl-6">
            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 1 ? 'bg-blue-600' : 'bg-yellow-400' }} ring-4 ring-white"></span>
            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Seksi</h3>
            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 1 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
        </div>
        @endhasanyrole

        <!-- Step 2: Kepala Bidang (Kepala Seksi TIDAK BISA melihat baris ini) -->
        @hasanyrole(['Kepala Bidang', 'Kepala Sub Bagian', 'Kepala TU', 'Kepala Kantor'])
        <div class="relative pl-6">
            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 2 ? 'bg-blue-600' : 'bg-yellow-400' }} ring-4 ring-white"></span>
            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Bidang</h3>
            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 2 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
        </div>
        @endhasanyrole

        <!-- Step 3: Kepala Sub Bagian (Kasi dan Kabid TIDAK BISA melihat baris ini) -->
        @hasanyrole(['Kepala Sub Bagian', 'Kepala TU', 'Kepala Kantor'])
        <div class="relative pl-6">
            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 3 ? 'bg-blue-600' : 'bg-yellow-400' }} ring-4 ring-white"></span>
            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Sub Bagian</h3>
            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 3 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
        </div>
        @endhasanyrole
        
        <!-- Silakan copy-paste polanya untuk Kepala TU dan Kepala Kantor jika diperlukan -->
        
    </div>
</div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>