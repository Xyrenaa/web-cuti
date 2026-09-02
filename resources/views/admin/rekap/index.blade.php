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
                        <h4 class="text-3xl font-bold text-gray-900">142</h4>
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
                        <h4 class="text-3xl font-bold text-gray-900">28</h4>
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
                        <h4 class="text-3xl font-bold text-gray-900">11.4 Hari</h4>
                        <p class="text-xs text-gray-400 mt-1">Tahun Anggaran {{ date('Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- MAIN TABLE SECTION -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                
                <!-- Toolbar: Search, Filter, & Export -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
                    
                    <!-- Search Input -->
                    <div class="w-full md:w-1/3">
                        <label class="text-xs font-semibold text-gray-500 mb-1 block">Cari Pegawai</label>
                        <div class="relative">
                            <input type="text" placeholder="Cari nama, NIP atau divisi..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-end space-x-3 w-full md:w-auto">
                        
                        <!-- AlpineJS Dropdown Filter & Sort -->
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 flex items-center transition shadow-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Filter & Urutkan
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 z-50 p-4">
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Cuti</label>
                                    <select class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                                        <option>Semua Jenis Cuti</option>
                                        <option>Cuti Tahunan</option>
                                        <option>Cuti Melahirkan</option>
                                        <option>Cuti Sakit</option>
                                        <option>Cuti Besar</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Urutkan Berdasarkan</label>
                                    <select class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                                        <option>Nama (A-Z)</option>
                                        <option>Paling Sering Cuti</option>
                                        <option>Sisa Cuti Terdikit</option>
                                        <option>Sisa Cuti Terbanyak</option>
                                    </select>
                                </div>
                                <button class="w-full bg-[#2a64f5] text-white py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Terapkan Filter</button>
                            </div>
                        </div>

                        <!-- Export Button -->
                        <a href="{{ route('admin.rekap.export') }}" class="px-4 py-2 bg-[#2a64f5] text-white rounded-lg text-sm font-semibold hover:bg-blue-700 flex items-center transition shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Ekspor Rekap (XLSX)
                        </a>
                    </div>
                </div>

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
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rekaps as $rekap)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $rekap->nama }}</td>
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $rekap->nip }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $rekap->divisi }}</td>
                                <td class="px-6 py-4 text-center text-gray-600 font-semibold">{{ $rekap->kuota }} Hari</td>
                                <td class="px-6 py-4 text-center font-bold text-red-500">{{ $rekap->terpakai }} Hari</td>
                                <td class="px-6 py-4 text-center font-bold {{ $rekap->sisa > 0 ? 'text-emerald-600' : 'text-gray-400' }}">{{ $rekap->sisa }} Hari</td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.rekap.show', $rekap->id) }}" class="text-[#2a64f5] font-semibold text-xs hover:underline">Lihat Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION (Dummy) -->
                <div class="mt-6 flex justify-between items-center text-sm text-gray-500">
                    <div>Menampilkan 5 dari 142 data</div>
                    <div class="flex items-center space-x-1">
                        <button class="px-3 py-1.5 border border-gray-200 rounded-md hover:bg-gray-50 font-medium">Sebelumnya</button>
                        <button class="px-3 py-1.5 bg-[#2a64f5] text-white rounded-md font-medium">1</button>
                        <button class="px-3 py-1.5 border border-gray-200 rounded-md hover:bg-gray-50 font-medium">2</button>
                        <button class="px-3 py-1.5 border border-gray-200 rounded-md hover:bg-gray-50 font-medium">Selanjutnya</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>