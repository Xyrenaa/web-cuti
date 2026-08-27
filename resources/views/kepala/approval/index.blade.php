<x-app-layout>
    
    <!-- 1. BANNER BIRU FULL-WIDTH -->
    <div class="w-full pt-6 pb-8 bg-[#2A65F3]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-1 text-sm font-medium text-blue-100/80">
                <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a> / 
                <span class="text-white">Approval Cuti</span>
            </div>
            <h2 class="text-3xl font-bold text-white">Approval Cuti</h2>
        </div>
    </div>

    <!-- 2. AREA KONTEN (Filter & Tabel) -->
    <div class="py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        
        <!-- Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white border-b border-gray-100 shadow-sm rounded-t-xl">
            
            <!-- Filter Section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <form action="#" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Cari Pengajuan</label>
                        <input type="text" placeholder="Masukkan nama atau NIP..." class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Filter Status</label>
                        <select class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 text-gray-600">
                            <option>Semua Status</option>
                            <option>Menunggu</option>
                            <option>Disetujui</option>
                            <option>Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Rentang Tanggal</label>
                        <input type="date" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 text-gray-400">
                    </div>
                    <div>
                        <button type="reset" class="bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 font-bold py-2.5 px-6 rounded-xl transition text-sm w-full md:w-auto">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-white text-gray-500 text-[11px] font-bold border-b border-gray-100 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-5">NO</th>
                                <th class="px-6 py-5">NAMA PEGAWAI</th>
                                <th class="px-6 py-5">NIP</th>
                                <th class="px-6 py-5">JENIS CUTI</th>
                                <th class="px-6 py-5">TANGGAL PENGAJUAN</th>
                                <th class="px-6 py-5">DURASI</th>
                                <th class="px-6 py-5">STATUS</th>
                                <th class="px-6 py-5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <!-- Aku buatkan array sementara di Blade agar HTML-nya pendek dan rapi -->
                            @php
                                $dummies = [
                                    ['nama' => 'Ahmad Subarjo', 'nip' => '198804122015031002', 'jenis' => 'Cuti Tahunan'],
                                    ['nama' => 'Siti Rahmawati', 'nip' => '199211082018012005', 'jenis' => 'Cuti Sakit'],
                                    ['nama' => 'Budi Kurniawan', 'nip' => '198501252010031001', 'jenis' => 'Cuti Besar'],
                                    ['nama' => 'Dewi Lestari', 'nip' => '19950719202012003', 'jenis' => 'Cuti Melahirkan'],
                                    ['nama' => 'Kepin', 'nip' => '19900902201604004', 'jenis' => 'Cuti Tahunan'],
                                ];
                            @endphp

                            @foreach($dummies as $index => $data)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-5 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-5 font-bold text-gray-900">{{ $data['nama'] }}</td>
                                <td class="px-6 py-5 text-gray-500 font-mono text-xs">{{ $data['nip'] }}</td>
                                <td class="px-6 py-5 text-gray-600">{{ $data['jenis'] }}</td>
                                <td class="px-6 py-5 text-gray-500">15 Maret 2026</td>
                                <td class="px-6 py-5 font-bold text-gray-800">3 Hari</td>
                                <td class="px-6 py-5">
                                    <span class="bg-[#fef3c7] text-[#b45309] text-[11px] px-3 py-1.5 rounded-full font-bold">Menunggu</span>
                                </td>
                                    <td class="px-6 py-5 text-right">
                                    <a href="{{ route('kepala.approval.show', $index + 1) }}" 
                                       class="text-[#2a64f5] hover:text-blue-800 font-bold text-sm inline-flex items-center">
                                        Lihat Detail <span class="ml-1 text-lg leading-none">&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-6 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                    <div>Menampilkan 5 dari 24 data</div>
                    <div class="flex items-center space-x-2 mt-4 md:mt-0">
                        <button class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Sebelumnya</button>
                        <button class="px-4 py-2 bg-[#2a64f5] text-white rounded-xl font-bold shadow-md shadow-blue-500/20">1</button>
                        <button class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">2</button>
                        <button class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Selanjutnya</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>