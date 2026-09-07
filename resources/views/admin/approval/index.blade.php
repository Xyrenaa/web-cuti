<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Beranda / Approval Cuti</div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Approval Cuti
        </h2>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
        <!-- Filter Section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <!-- Tambahkan action dan method GET -->
                <form action="{{ route('admin.approval.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    
                    <!-- Search Input (Dilebarkan jadi 2 kolom) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 mb-2">Cari Pengajuan</label>
                        <!-- Tambahkan atribut name="search" dan value untuk menyimpan inputan -->
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama atau NIP..." class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5">
                    </div>
                    
                    <!-- Status Dropdown -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Filter Status</label>
                        <!-- Tambahkan atribut name="status" dan logika selected -->
                        <select name="status" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 text-gray-600">
                            <option value="Semua Status" {{ request('status') == 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                            <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <!-- Aku tambahkan Dibatalkan sekalian karena fiturnya baru saja kita buat -->
                            <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    
                    <!-- Date Picker -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Rentang Tanggal</label>
                        <!-- Tambahkan atribut name="date" dan value -->
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 text-gray-400">
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <!-- Tombol Cari -->
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl transition text-sm w-full shadow-sm">
                            Cari
                        </button>
                        <!-- Tombol Reset diubah menjadi <a> link agar memuat ulang halaman tanpa parameter -->
                        <a href="{{ route('admin.approval.index') }}" class="bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 font-bold py-2.5 px-4 rounded-xl transition text-sm w-full text-center">
                            Reset
                        </a>
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
                            
                            @forelse($pengajuans as $index => $pengajuan)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-5 text-gray-500">{{ $pengajuans->firstItem() + $index }}</td>
                                <td class="px-6 py-5 font-bold text-gray-900">{{ $pengajuan->user->name ?? 'User Dihapus' }}</td>
                                <td class="px-6 py-5 text-gray-500 font-mono text-xs">{{ $pengajuan->user->nip ?? '-' }}</td>
                                <td class="px-6 py-5 text-gray-600">{{ $pengajuan->jenisCuti->nama_cuti ?? 'Cuti Tahunan' }}</td>
                                <td class="px-6 py-5 text-gray-500">{{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') }}</td>
                                <td class="px-6 py-5 font-bold text-gray-800">{{ $pengajuan->durasi_hari }} Hari</td>
                                <td class="px-6 py-5">
                                    <span class="bg-[#fef3c7] text-[#b45309] text-[11px] px-3 py-1.5 rounded-full font-bold inline-block whitespace-nowrap text-center">
                                        {{ $pengajuan->status_pengajuan }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('admin.approval.show', $pengajuan->id) }}" 
                                       class="text-[#2a64f5] hover:text-blue-800 font-bold text-sm inline-flex items-center">
                                        Lihat Detail <span class="ml-1 text-lg leading-none">&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500 font-medium">Belum ada data pengajuan cuti yang masuk.</td>
                            </tr>
                            @endforelse
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
</x-admin-layout>