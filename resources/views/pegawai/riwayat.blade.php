<x-app-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Riwayat Pengajuan</div>
        <h1 class="text-white text-3xl font-bold">Riwayat Pengajuan</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Riwayat Pengajuan Cuti</h2>

        <!-- Box Filter Pencarian -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 max-w-5xl mx-auto flex flex-col md:flex-row gap-4">
            <form action="{{ route('pengajuan.riwayat') }}" method="GET" class="w-full flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Cari Pengajuan</label>
                    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Masukkan kata kunci alasan..." class="w-full bg-gray-50 border border-gray-300 rounded-md px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-6 py-2 bg-[#2A65F3] text-white rounded-md font-semibold text-sm hover:bg-blue-700 transition">Cari</button>
                    <a href="{{ route('pengajuan.riwayat') }}" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-md font-semibold text-sm hover:bg-gray-50 transition">Reset</a>
                </div>
            </form>
        </div>

        <!-- List Data Card -->
        <div class="max-w-5xl mx-auto space-y-4">
            @forelse ($riwayat as $item)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-4 border-b border-gray-100 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ $item->jenisCuti->nama_cuti ?? 'Cuti' }} — {{ Str::limit($item->alasan, 30) }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }} • 
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai)) + 1 }} hari
                            </p>
                        </div>
                        
                        <!-- Logika Warna Status -->
                        @php
                            $badgeColor = 'bg-yellow-100 text-yellow-700'; // Default Menunggu
                            if($item->status_pengajuan == 'Disetujui') $badgeColor = 'bg-green-100 text-green-700';
                            if($item->status_pengajuan == 'Ditolak') $badgeColor = 'bg-red-100 text-red-700';
                        @endphp
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold {{ $badgeColor }}">
                            {{ $item->status_pengajuan }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-y-3 gap-x-6 text-sm">
                        <div><span class="text-gray-500 block">Lokasi Cuti</span><span class="font-medium text-gray-800">{{ $item->lokasi }}</span></div>
                        <div class="md:col-span-2"><span class="text-gray-500 block">Alasan</span><span class="font-medium text-gray-800">{{ $item->alasan }}</span></div>
                    </div>

                    <div class="mt-4 pt-4 text-right">
                        <a href="{{ route('pengajuan.show', $item->id) }}" class="text-[#2A65F3] text-sm font-bold hover:underline flex items-center justify-end gap-1">
                            Lihat detail lengkap <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200 text-gray-500">
                    Belum ada riwayat pengajuan cuti.
                </div>
            @endforelse

            <div class="mt-6">
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>
</x-app-layout>