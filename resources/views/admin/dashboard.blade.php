<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Beranda</div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Beranda
        </h2>
    </x-slot>

    <div class="pt-12 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
            
            <!-- Welcome Banner -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end px-2 sm:px-0">
                <div class="mb-6 md:mb-0">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Selamat Datang Kembali, {{ Auth::user()->name }}
                    </h1>
                    <p class="text-base text-gray-500 max-w-2xl leading-relaxed">Kelola persetujuan dan rekapitulasi data cuti pegawai Kantor Otoritas Bandar Udara Wilayah III Juanda hari ini.</p>
                </div>
                
                <!-- Waktu Saat Ini -->
                <div class="text-left md:text-right" 
                     x-data="{ time: '{{ now()->format('H:i:s') }}' }" 
                     x-init="setInterval(() => { 
                        let d = new Date(); 
                        time = d.getHours().toString().padStart(2, '0') + ':' + 
                               d.getMinutes().toString().padStart(2, '0') + ':' + 
                               d.getSeconds().toString().padStart(2, '0'); 
                     }, 1000)">
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mb-1.5">Waktu Saat Ini</p>
                    <div class="flex items-baseline md:justify-end gap-1.5">
                        <p class="text-4xl font-light text-gray-800 tracking-tight" x-text="time"></p>
                        <span class="text-sm font-medium text-gray-500">WIB</span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards (Data Statis) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col justify-between group">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-sm font-medium text-gray-600">Pengajuan Baru</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-500 text-[10px] px-2.5 py-1 rounded-md font-medium">Perlu Verifikasi</span>
                    </div>
                    <div class="text-4xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $pengajuanBaru }}</div>
                </div>
                
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col justify-between group">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-sm font-medium text-gray-600">Menunggu Persetujuan</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-500 text-[10px] px-2.5 py-1 rounded-md font-medium">Pending</span>
                    </div>
                    <div class="text-4xl font-semibold text-gray-900 group-hover:text-gray-700 transition-colors">{{ $menungguPersetujuan }}</div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col justify-between group">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-sm font-medium text-gray-600">Disetujui Bulan Ini</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-500 text-[10px] px-2.5 py-1 rounded-md font-medium">Selesai</span>
                    </div>
                    <div class="text-4xl font-semibold text-gray-900 group-hover:text-gray-700 transition-colors">{{ $disetujuiBulanIni }}</div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col justify-between group">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-sm font-medium text-gray-600">Ditolak Bulan Ini</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-500 text-[10px] px-2.5 py-1 rounded-md font-medium">Ditolak</span>
                    </div>
                    <div class="text-4xl font-semibold text-gray-900 group-hover:text-gray-700 transition-colors">{{ $ditolakBulanIni }}</div>
                </div>
            </div>

            <!-- Table Section (Data Statis) -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-6">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="font-semibold text-gray-800 text-base">Pengajuan Cuti Terbaru (Butuh Tindakan)</h3>
                    <a href="{{ route('admin.approval.index') }}" class="text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition">
                    Lihat Semua Antrean
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/80 text-gray-500 text-[11px] font-semibold border-b border-gray-100 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 whitespace-nowrap">NO</th>
                                <th class="px-6 py-4 whitespace-nowrap">NAMA PEGAWAI</th>
                                <th class="px-6 py-4 whitespace-nowrap">NIP</th>
                                <th class="px-6 py-4 whitespace-nowrap">JENIS CUTI</th>
                                <th class="px-6 py-4 whitespace-nowrap">TANGGAL PENGAJUAN</th>
                                <th class="px-6 py-4 whitespace-nowrap">DURASI</th>
                                <th class="px-6 py-4 text-center whitespace-nowrap">STATUS</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            
                            <!-- DATA ASLI DARI DATABASE -->
                            @forelse($antreanCuti as $index => $pengajuan)
                            <tr class="hover:bg-gray-50/50 transition duration-150">
                                <td class="px-6 py-4 text-gray-500">{{ $antreanCuti->firstItem() + $index }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $pengajuan->user->name ?? 'User Dihapus' }}</td>
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $pengajuan->user->nip ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $pengajuan->jenisCuti->nama_cuti ?? 'Cuti Tahunan' }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-700">{{ $pengajuan->durasi_hari }} Hari</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ $pengajuan->status_pengajuan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.approval.show', $pengajuan->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm inline-flex items-center group">
                                        Detail 
                                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    Hebat! Tidak ada antrean pengajuan yang membutuhkan verifikasi Anda saat ini. 🎉
                                </td>
                            </tr>
                            @endforelse
                            
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $antreanCuti->links() }}
                </div>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>