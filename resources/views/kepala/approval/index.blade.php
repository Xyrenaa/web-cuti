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
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 w-full">
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
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden mt-4">
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
                        
                        <!-- Mengganti foreach dummy dengan forelse dari data database -->
                        @forelse($pengajuans as $index => $pengajuan)
                        <tr class="hover:bg-gray-50/50 transition">
                            <!-- Menggunakan loop iteration bawaan Laravel -->
                            <td class="px-6 py-5 text-gray-500">{{ $loop->iteration }}</td>
                            
                            <!-- Mengambil relasi dari user -->
                            <td class="px-6 py-5 font-bold text-gray-900">{{ $pengajuan->user->name ?? '-' }}</td>
                            <td class="px-6 py-5 text-gray-500 font-mono text-xs">{{ $pengajuan->user->nip ?? '-' }}</td>
                            
                            <!-- Asumsi tabel relasi JenisCuti bernama jenisCuti -->
                            <td class="px-6 py-5 text-gray-600">{{ $pengajuan->jenisCuti->nama ?? 'Cuti Tahunan' }}</td>
                            
                            <!-- Format tanggal menggunakan Carbon -->
                            <td class="px-6 py-5 text-gray-500">{{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') }}</td>
                            
                            <td class="px-6 py-5 font-bold text-gray-800">{{ $pengajuan->lama_cuti ?? 0 }} Hari</td>
                            
                            <td class="px-6 py-5">
                                <span class="bg-[#fef3c7] text-[#b45309] text-[11px] px-3 py-1.5 rounded-full font-bold">
                                    Menunggu Persetujuan
                                </span>
                            </td>
                            
                            <td class="px-6 py-5 text-right">
                                <!-- Mengarahkan ke route show dengan ID pengajuan yang benar -->
                                <a href="{{ route('kepala.approval.show', $pengajuan->id) }}" 
                                   class="text-[#2a64f5] hover:text-blue-800 font-bold text-sm inline-flex items-center">
                                    Lihat Detail <span class="ml-1 text-lg leading-none">&rarr;</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <!-- Tampilan jika tidak ada data yang perlu di-approve -->
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-base font-medium">Belum ada pengajuan cuti yang perlu persetujuan Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <!-- Pagination Dinamis -->
            <div class="p-6 border-t border-gray-50">
                @if($pengajuans instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $pengajuans->links() }}
                @endif
            </div>
        </div>

    </div>
</x-app-layout>