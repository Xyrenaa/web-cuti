<x-app-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Riwayat Pengajuan</div>
        <h1 class="text-white text-3xl font-bold">Riwayat Pengajuan</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Riwayat Pengajuan Cuti</h2>

        <!-- Box Filter Pencarian -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
            <form action="{{ route('pengajuan.riwayat') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    
                    <!-- Input Pencarian -->
                    <div class="md:col-span-4 lg:col-span-1">
                        <x-input-label for="cari" value="Cari Pengajuan" />
                        <!-- Diubah name-nya menjadi 'cari' menyesuaikan controllermu -->
                        <x-text-input id="cari" name="cari" type="text" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Kata kunci alasan..." value="{{ request('cari') }}" />
                    </div>

                    <!-- Filter Jenis Cuti -->
                    <div>
                        <x-input-label for="jenis_cuti" value="Jenis Cuti" />
                        <select name="jenis_cuti" id="jenis_cuti" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">Semua Jenis</option>
                            @foreach($jenis_cutis as $jc)
                                <option value="{{ $jc->id }}" {{ request('jenis_cuti') == $jc->id ? 'selected' : '' }}>
                                    {{ $jc->nama_cuti }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Bulan -->
                    <div>
                        <x-input-label for="bulan" value="Bulan" />
                        <select name="bulan" id="bulan" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">Semua Bulan</option>
                            @php
                                $bulanList = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @foreach($bulanList as $num => $name)
                                <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tahun & Tombol Aksi -->
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <x-input-label for="tahun" value="Tahun" />
                            <select name="tahun" id="tahun" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">Semua Tahun</option>
                                @php $currentYear = date('Y'); @endphp
                                @for($i = $currentYear; $i >= $currentYear - 3; $i--)
                                    <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex gap-2 mb-[2px]">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-semibold transition">
                                Cari
                            </button>
                            <a href="{{ route('pengajuan.riwayat') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md font-semibold transition inline-flex items-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- LIST CARD PENGAJUAN (LEBIH COMPACT/KECIL) -->
        <div class="space-y-4">
            <!-- Menggunakan variabel $riwayat as $item -->
            @forelse ($riwayat as $item)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <!-- Tambahkan Kode Pengajuan di sini dengan warna biru agar menonjol -->
                                <span class="text-blue-600">#{{ $item->kode_pengajuan }}</span> - 
                                {{ $item->jenisCuti->nama_cuti ?? 'Cuti Tahunan' }}
                                <span class="text-gray-400 font-normal text-sm">— {{ $item->durasi ?? 0 }} Hari</span>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') }}
                            </p>
                        </div>

                        <div class="flex flex-col md:items-end gap-3">
                            @php
                                $statusColor = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                if(str_contains($item->status_pengajuan, 'Disetujui')) $statusColor = 'bg-green-100 text-green-800 border-green-200';
                                if(str_contains($item->status_pengajuan, 'Ditolak') || str_contains($item->status_pengajuan, 'Dibatalkan')) $statusColor = 'bg-red-100 text-red-800 border-red-200';
                            @endphp
                            <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $statusColor }}">
                                {{ $item->status_pengajuan }}
                            </span>
                            
                            <!-- Sesuaikan nama routenya dengan route detail milikmu, misalnya 'pengajuan.show' atau 'pegawai.detail' -->
                            <a href="{{ route('pegawai.detail', $item->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 group">
                                Lihat detail lengkap
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 bg-white rounded-xl border border-gray-200">
                    <p class="text-gray-500">Belum ada riwayat pengajuan cuti yang sesuai dengan filter.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $riwayat->withQueryString()->links() }}
        </div>
            <div class="mt-6">
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>
</x-app-layout>