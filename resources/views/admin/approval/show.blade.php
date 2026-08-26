<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Beranda / Approval Cuti / Detail Pengajuan</div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Detail Pengajuan Cuti
        </h2>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                    
                    <!-- Card: Alur Persetujuan (Sesuai Alur OBU III Juanda) -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Alur Persetujuan</h3>
                        
                        <!-- Timeline -->
                        <div class="relative border-l-2 border-gray-100 ml-3 space-y-6 pb-2">
                            
                            <!-- 1. Pengajuan (Pegawai) - Selesai -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-[#1e3a8a] -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-900">Pengajuan Dikirim</h4>
                                <p class="text-xs text-gray-500 mt-0.5">15 Mar 09:12 Oleh Pegawai</p>
                            </div>
                            
                            <!-- 2. Kepala Seksi (Sedang Diproses) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-amber-400 -left-[11px] top-0 border-4 border-white shadow-sm animate-pulse"></div>
                                <h4 class="font-bold text-sm text-amber-600">Persetujuan Kepala Seksi</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Menunggu Validasi</p>
                            </div>

                            <!-- 3. Kepala Bidang (Menunggu) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-gray-300 -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-400">Persetujuan Kepala Bidang</h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">Menunggu Tahap Sebelumnya</p>
                            </div>

                            <!-- 4. Kasubag (Menunggu) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-gray-300 -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-400">Persetujuan Kasubag</h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">Menunggu Tahap Sebelumnya</p>
                            </div>

                            <!-- 5. Kepala TU (Menunggu) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-gray-300 -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-400">Persetujuan Kepala TU</h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">Menunggu Tahap Sebelumnya</p>
                            </div>

                            <!-- 6. Kepala Kantor (Menunggu) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-gray-300 -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-400">Persetujuan Kepala Kantor</h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">Menunggu Tahap Sebelumnya</p>
                            </div>

                            <!-- 7. Final -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-gray-300 -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-400">Cuti Diterima</h4>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-admin-layout>