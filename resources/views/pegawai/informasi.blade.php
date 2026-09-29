<x-app-layout>
    <!-- Header Banner -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Informasi Cuti</div>
        <h1 class="text-white text-3xl font-bold">Informasi & Jatah Cuti</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM KIRI: Kuota & Statistik -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Card Jatah Cuti -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Sisa Kuota Cuti Anda
                    </h3>
                    
                    <div class="text-center mb-6">
                        <span class="text-5xl font-extrabold text-blue-600">{{ $totalJatah }}</span>
                        <span class="text-gray-500 font-medium ml-1">Hari</span>
                    </div>

                                        <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Sisa Tahun Ini ({{ date('Y') }})</span>
                            <span class="font-bold text-gray-900">{{ $jatahTahunIni }} Hari</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Sisa Tahun Lalu</span>
                            <span class="font-bold text-green-600">+ {{ $jatahTahunLalu }} Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Card Statistik Pengajuan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Statistik Pengajuan</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-center">
                            <div class="text-2xl font-bold text-gray-800">{{ $statistik['total_diajukan'] }}</div>
                            <div class="text-xs font-semibold text-gray-500 uppercase mt-1">Total Diajukan</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-center">
                            <div class="text-2xl font-bold text-green-700">{{ $statistik['disetujui'] }}</div>
                            <div class="text-xs font-semibold text-green-600 uppercase mt-1">Disetujui</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 text-center col-span-2">
                            <div class="text-2xl font-bold text-yellow-700">{{ $statistik['menunggu'] }}</div>
                            <div class="text-xs font-semibold text-yellow-600 uppercase mt-1">Menunggu Persetujuan</div>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('pengajuan.index') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-3 rounded-xl transition shadow-md">
                    + Ajukan Cuti Sekarang
                </a>
            </div>

            <!-- KOLOM KANAN: Informasi, Panduan & Alur -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Panduan & Tata Cara Cuti</h2>
                    
                    <div class="space-y-8">
                        <!-- Alur Pengajuan -->
                                                <!-- Alur Pengajuan -->
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-4 flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                                Alur Persetujuan
                            </h3>
                            <div class="flex flex-wrap gap-2 items-center text-xs ml-2 md:ml-10">
                                <div class="bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg font-medium">1. Pegawai Mengajukan</div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <div class="bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg font-medium">2. Kepala Seksi</div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <div class="bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg font-medium">3. Kepala Bidang</div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <div class="bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg font-medium">4. Verifikasi Admin Kepegawaian</div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <div class="bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg font-medium">5. Kepala Sub Bagian &amp; Kepala TU</div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <div class="bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg font-medium">6. Kepala Kantor (Keputusan)</div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <div class="bg-blue-50 border border-blue-200 px-3 py-2 rounded-lg font-bold text-blue-700">7. Finalisasi Admin &amp; Terbit Surat</div>
                            </div>
                            <p class="text-xs text-gray-400 mt-3 ml-2 md:ml-10">Pejabat struktural (mis. Kepala Seksi, Kepala Bidang) yang mengajukan cuti sendiri otomatis melewati tahap approval jabatannya sendiri, sesuai posisinya di alur ini.</p>
                        </div>

                                                <!-- Syarat & Ketentuan -->
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3 flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                                Syarat & Ketentuan Umum
                            </h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-2 md:ml-10 leading-relaxed text-sm">
                                <li><strong>Cuti Tahunan</strong> baru menjadi hak setelah bekerja paling kurang 1 tahun terus-menerus, sebanyak 12 hari kerja per tahun.</li>
                                <li>Sisa <strong>Cuti Tahunan</strong> tahun lalu yang tidak digunakan dapat dibawa maksimal <strong>6 hari kerja</strong> ke tahun berikutnya (kecuali ada penangguhan resmi oleh Kepala Kantor, maksimal 12 hari).</li>
                                <li>Setiap pengajuan <strong>Cuti Sakit</strong> di sistem ini wajib melampirkan surat keterangan dokter, sesuai kolom lampiran pada form.</li>
                                <li><strong>Cuti Karena Alasan Penting</strong> dan <strong>Cuti Melahirkan</strong> tidak mengurangi jatah Cuti Tahunan Anda.</li>
                                <li><strong>Cuti Besar</strong> menghanguskan hak Cuti Tahunan pada tahun cuti besar tersebut diambil.</li>
                                <li>Pegawai berstatus PPPK hanya dapat mengajukan Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, dan Cuti Bersama.</li>
                            </ul>
                        </div>

                        <!-- Ringkasan Tiap Jenis Cuti -->
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3 flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                                Ringkasan Jenis Cuti
                            </h3>
                            <div class="overflow-x-auto ml-2 md:ml-10">
                                <table class="min-w-full text-xs border border-gray-200">
                                    <thead class="bg-gray-50 text-gray-600 uppercase">
                                        <tr>
                                            <th class="px-3 py-2 text-left border border-gray-200">Jenis Cuti</th>
                                            <th class="px-3 py-2 text-left border border-gray-200">Lama Maksimal</th>
                                            <th class="px-3 py-2 text-left border border-gray-200">Syarat Utama</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700">
                                        <tr>
                                            <td class="px-3 py-2 border border-gray-200 font-semibold">Cuti Tahunan</td>
                                            <td class="px-3 py-2 border border-gray-200">12 hari kerja/tahun</td>
                                            <td class="px-3 py-2 border border-gray-200">Sudah bekerja ≥ 1 tahun terus-menerus.</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 border border-gray-200 font-semibold">Cuti Sakit</td>
                                            <td class="px-3 py-2 border border-gray-200">1 tahun, dapat diperpanjang 6 bulan</td>
                                            <td class="px-3 py-2 border border-gray-200">Surat keterangan dokter.</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 border border-gray-200 font-semibold">Cuti Alasan Penting</td>
                                            <td class="px-3 py-2 border border-gray-200">1 bulan</td>
                                            <td class="px-3 py-2 border border-gray-200">Keluarga inti sakit keras/meninggal, menikah, istri melahirkan/caesar, atau musibah bencana/kebakaran.</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 border border-gray-200 font-semibold">Cuti Besar</td>
                                            <td class="px-3 py-2 border border-gray-200">3 bulan</td>
                                            <td class="px-3 py-2 border border-gray-200">Sudah bekerja ≥ 5 tahun terus-menerus (kecuali ibadah haji pertama kali). Menghanguskan Cuti Tahunan tahun berjalan.</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 border border-gray-200 font-semibold">Cuti Melahirkan</td>
                                            <td class="px-3 py-2 border border-gray-200">3 bulan</td>
                                            <td class="px-3 py-2 border border-gray-200">Untuk anak ke-1 s/d ke-3. Anak ke-4 dan seterusnya memakai Cuti Besar dengan lama yang sama.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 ml-2 md:ml-10">Ringkasan berdasarkan Peraturan BKN Nomor 7 Tahun 2021. Hubungi bagian kepegawaian untuk kasus khusus.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>