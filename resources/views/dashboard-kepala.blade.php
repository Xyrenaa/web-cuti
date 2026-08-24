<x-app-layout>
    <!-- Pita Biru Header -->
    <div class="bg-[#2A65F3] pt-8 pb-10 px-4 sm:px-6 lg:px-8 shadow-sm">
        <div class="max-w-7xl mx-auto">
            <p class="text-blue-100 text-sm font-medium mb-1">Sistem Layanan Cuti (PELITA)</p>
            <h1 class="text-3xl font-bold text-white">Beranda</h1>
        </div>
    </div>

    <!-- Area Konten Utama (Posisi relatif untuk menampung watermark) -->
    <div class="relative py-8 min-h-screen bg-gray-50/50">
        
        <!-- Watermark Logo Otban Samar di Background -->
        <div class="absolute inset-0 flex justify-center items-center z-0 pointer-events-none overflow-hidden">
            <img src="{{ asset('img/Logo Otban.png') }}" class="w-[500px] opacity-[0.03]" alt="Watermark">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Kotak Selamat Datang -->
            <div class="bg-[#F4F7FF] rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border border-blue-100 shadow-sm">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Selamat Datang Kembali</h2>
                    <p class="text-gray-500 mt-1 text-sm">Kelola pengajuan cuti pegawai Kantor Otoritas Bandar Udara Wilayah III Juanda secara real-time.</p>
                </div>
                <!-- Bagian Waktu Real-time dengan Alpine.js -->
                <div class="mt-4 md:mt-0 text-right" 
                     x-data="{ 
                        waktu: '{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}',
                        updateTime() {
                            const now = new Date();
                            const jam = String(now.getHours()).padStart(2, '0');
                            const menit = String(now.getMinutes()).padStart(2, '0');
                            this.waktu = jam + ':' + menit;
                        }
                     }" 
                     x-init="setInterval(() => updateTime(), 1000)">
                    
                    <p class="text-xs font-bold text-[#2A65F3] uppercase tracking-wider">Waktu Saat Ini</p>
                    <p class="text-xl font-bold text-gray-800"><span x-text="waktu"></span> WIB</p>
                </div>
            </div>

            <!-- Grid 4 Kartu Statistik -->
           <!-- Statistik Terpadu (Sleek Enterprise Style) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-8 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                    
                    <!-- Statistik 1: Menunggu Tindakan (Fokus Utama) -->
                    <div class="p-6 hover:bg-gray-50/80 transition duration-200">
                        <div class="flex items-center gap-2 mb-2">
                            <!-- Indikator titik berkedip pelan (subtle pulse) -->
                            <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                            <h3 class="text-[11px] font-bold text-gray-500 tracking-widest uppercase">Menunggu Tindakan</h3>
                        </div>
                        <div class="flex items-baseline gap-2 mt-3">
                            <h4 class="text-4xl font-extrabold text-gray-800 tracking-tight">12</h4>
                            <span class="text-sm font-medium text-gray-500">Berkas</span>
                        </div>
                    </div>

                    <!-- Statistik 2: Disetujui -->
                    <div class="p-6 hover:bg-gray-50/80 transition duration-200">
                        <h3 class="text-[11px] font-bold text-gray-500 tracking-widest uppercase mb-2">Disetujui Bulan Ini</h3>
                        <div class="flex items-baseline gap-2 mt-3">
                            <h4 class="text-4xl font-extrabold text-gray-800 tracking-tight">45</h4>
                            <span class="text-sm font-semibold text-green-600 flex items-center bg-green-50 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                Tuntas
                            </span>
                        </div>
                    </div>

                    <!-- Statistik 3: Ditolak / Revisi -->
                    <div class="p-6 hover:bg-gray-50/80 transition duration-200">
                        <h3 class="text-[11px] font-bold text-gray-500 tracking-widest uppercase mb-2">Ditolak / Revisi</h3>
                        <div class="flex items-baseline gap-2 mt-3">
                            <h4 class="text-4xl font-extrabold text-gray-800 tracking-tight">3</h4>
                            <span class="text-sm font-medium text-gray-500">Berkas</span>
                        </div>
                    </div>

                    <!-- Statistik 4: Total Pegawai (Dengan highlight warna brand) -->
                    <div class="p-6 bg-blue-50/30 hover:bg-blue-50/60 transition duration-200">
                        <h3 class="text-[11px] font-bold text-[#2A65F3]/70 tracking-widest uppercase mb-2">Total Pegawai Aktif</h3>
                        <div class="flex items-baseline gap-2 mt-3">
                            <h4 class="text-4xl font-extrabold text-[#2A65F3] tracking-tight">218</h4>
                            <span class="text-sm font-medium text-[#2A65F3]/70">Personel</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tabel Persetujuan Terkini -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Persetujuan Terkini Menunggu Tindakan</h3>
                    <a href="#" class="text-sm font-semibold text-[#2A65F3] hover:text-blue-800 flex items-center gap-1">
                        Lihat Semua <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 font-bold bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4">NAMA PEGAWAI</th>
                                <th class="px-6 py-4">JENIS CUTI</th>
                                <th class="px-6 py-4">TANGGAL PENGAJUAN</th>
                                <th class="px-6 py-4 text-center">DURASI</th>
                                <th class="px-6 py-4 text-center">STATUS</th>
                                <th class="px-6 py-4 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <!-- Baris 1 -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">Ahmad Subarjo</p>
                                    <p class="text-xs text-gray-400">NIP: 19820412 201103 1 002</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600">Cuti Tahunan</td>
                                <td class="px-6 py-4 text-gray-600">Senin, 15 Maret 2026</td>
                                <td class="px-6 py-4 text-center text-gray-600">3 Hari</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Menunggu</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="#" class="font-bold text-[#2A65F3] hover:text-blue-800">Tinjau Berkas</a>
                                </td>
                            </tr>
                            
                            <!-- Baris 2 -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">Siti Aminah</p>
                                    <p class="text-xs text-gray-400">NIP: 19820412 201103 1 002</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600">Cuti Sakit</td>
                                <td class="px-6 py-4 text-gray-600">Minggu, 14 Maret 2026</td>
                                <td class="px-6 py-4 text-center text-gray-600">2 Hari</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Menunggu</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="#" class="font-bold text-[#2A65F3] hover:text-blue-800">Tinjau Berkas</a>
                                </td>
                            </tr>

                            <!-- Baris 3 -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">Eko Purwanto</p>
                                    <p class="text-xs text-gray-400">NIP: 19820412 201103 1 002</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600">Cuti Tahunan</td>
                                <td class="px-6 py-4 text-gray-600">Sabtu, 13 Maret 2026</td>
                                <td class="px-6 py-4 text-center text-gray-600">5 Hari</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Menunggu</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="#" class="font-bold text-[#2A65F3] hover:text-blue-800">Tinjau Berkas</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>