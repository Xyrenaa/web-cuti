<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Beranda / Rekap Cuti</div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Rekap Data Cuti Pegawai
        </h2>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- STATISTIC CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-sm font-semibold text-gray-600">Total Pegawai</span>
                        <div class="p-2 bg-blue-50 rounded-lg"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                    </div>
                    <div>
                        <div class="text-4xl font-semibold text-gray-900">{{ $totalPegawai }}</div>
                        <p class="text-xs text-gray-400 mt-1">Pegawai Aktif</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-sm font-semibold text-gray-600">Pengajuan Bulan Ini</span>
                        <div class="p-2 bg-blue-50 rounded-lg"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                    </div>
                    <div>
                        <div class="text-4xl font-semibold text-gray-900">{{ $pengajuanBulanIni }}</div>
                        <p class="text-xs text-gray-400 mt-1">Diproses & Selesai</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-sm font-semibold text-gray-600">Rata-rata Sisa Cuti</span>
                        <div class="p-2 bg-blue-50 rounded-lg"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                    </div>
                    <div>
                        <div class="text-4xl font-semibold text-gray-900">{{ $rataSisa }} Hari</div>
                        <p class="text-xs text-gray-400 mt-1">Tahun Anggaran {{ date('Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- MAIN TABLE SECTION -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                
                <!-- Form Pencarian, Filter & Sort -->
            <form action="{{ route('admin.rekap.index') }}" method="GET" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                
                <!-- Search Bar -->
                <div class="w-full lg:w-1/3 relative">
                    <label class="block text-xs font-bold text-gray-600 mb-2">Cari Pegawai</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NIP lalu tekan Enter..." class="w-full pl-10 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5">
                    </div>
                </div>
                
                <!-- Dropdown Filter & Urutkan -->
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto mt-4 lg:mt-0 items-end">
                    
                    <!-- Filter Divisi -->
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-600 mb-2">Divisi</label>
                        <select name="divisi" onchange="this.form.submit()" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                            <option value="Semua Divisi">Semua Divisi</option>
                            @foreach($daftarDivisi as $div)
                                <option value="{{ $div->id }}" {{ request('divisi') == $div->id ? 'selected' : '' }}>{{ $div->nama_bagian }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Analitik / Urutkan -->
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-600 mb-2">Analisis (Paling Sering)</label>
                        <select name="sort" onchange="this.form.submit()" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                            <option value="Terbaru">Normal (Terbaru)</option>
                            <option value="Terbanyak" {{ request('sort') == 'Terbanyak' ? 'selected' : '' }}>🔥 Paling Sering Cuti (Total)</option>
                            @foreach($daftarJenisCuti as $jenis)
                                <option value="{{ $jenis->id }}" {{ request('sort') == $jenis->id ? 'selected' : '' }}>📌 Terbanyak: {{ $jenis->nama_cuti }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Ekspor (Bawa query parameter agar filter ikut ke Excel) -->
                    <a href="{{ route('admin.rekap.export', request()->query()) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow-sm text-sm whitespace-nowrap h-[42px] flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Ekspor (XLSX)
                    </a>
                    
                </div>
            </form>

                <!-- TABLE -->
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/80 text-gray-600 text-xs font-bold border-b border-gray-100 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 whitespace-nowrap">NO</th>
                                <th class="px-6 py-4 whitespace-nowrap">Nama Pegawai</th>
                                <th class="px-6 py-4 whitespace-nowrap">NIP</th>
                                <th class="px-6 py-4 whitespace-nowrap">Divisi / Subbagian</th>
                                <th class="px-6 py-4 whitespace-nowrap text-center">Kuota Tahunan</th>
                                <th class="px-6 py-4 whitespace-nowrap text-center">Cuti Terpakai</th>
                                <th class="px-6 py-4 whitespace-nowrap text-center">Sisa Kuota</th>
                                <th class="px-6 py-4 whitespace-nowrap text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($rekaps as $index => $rekap)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-5 text-gray-500">{{ $rekaps->firstItem() + $index }}</td>
                                <td class="px-6 py-5 font-bold text-gray-900">{{ $rekap->nama }}</td>
                                <td class="px-6 py-5 text-gray-500 font-mono text-xs">{{ $rekap->nip }}</td>
                                <td class="px-6 py-5 text-gray-600">{{ $rekap->divisi }}</td>
                                <td class="px-6 py-5 text-gray-500">{{ $rekap->kuota }} Hari</td>
                                
                                <!-- Warna merah untuk cuti terpakai -->
                                <td class="px-6 py-5 font-bold text-red-500">{{ $rekap->terpakai }} Hari</td>
                                
                                <!-- Warna hijau untuk sisa kuota -->
                                <td class="px-6 py-5 font-bold text-emerald-500">{{ $rekap->sisa }} Hari</td>
                                
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.rekap.show', $rekap->id) }}" class="text-[#2a64f5] hover:text-blue-800 font-bold text-sm">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500 font-medium">Belum ada data pegawai.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-6 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                    <div>Menampilkan {{ $rekaps->firstItem() ?? 0 }} sampai {{ $rekaps->lastItem() ?? 0 }} dari {{ $rekaps->total() }} data</div>
                    
                    <div class="flex items-center space-x-2 mt-4 md:mt-0">
                        @if ($rekaps->onFirstPage())
                            <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">Sebelumnya</span>
                        @else
                            <a href="{{ $rekaps->previousPageUrl() }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Sebelumnya</a>
                        @endif

                        @foreach ($rekaps->getUrlRange(1, $rekaps->lastPage()) as $page => $url)
                            @if ($page == $rekaps->currentPage())
                                <span class="px-4 py-2 bg-[#2a64f5] text-white rounded-xl font-bold shadow-md shadow-blue-500/20">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($rekaps->hasMorePages())
                            <a href="{{ $rekaps->nextPageUrl() }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Selanjutnya</a>
                        @else
                            <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">Selanjutnya</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>