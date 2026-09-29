<x-app-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Riwayat Pengajuan</div>
        <h1 class="text-white text-3xl font-bold">Riwayat Pengajuan</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">

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

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold">Kode</th>
                            <th class="px-4 py-3 text-left font-bold">Jenis</th>
                            <th class="px-4 py-3 text-left font-bold">Tanggal Cuti</th>
                            <th class="px-4 py-3 text-center font-bold">Hari</th>
                            <th class="px-4 py-3 text-left font-bold">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($riwayat as $item)
                            @php
                                $warna = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                if ($item->status_group === 'Disetujui') $warna = 'bg-green-100 text-green-800 border-green-200';
                                if ($item->status_group === 'Ditolak')   $warna = 'bg-red-100 text-red-800 border-red-200';
                                if ($item->status_group === 'Dibatalkan') $warna = 'bg-gray-100 text-gray-500 border-gray-200';
                                $mulai   = \Carbon\Carbon::parse($item->tanggal_mulai);
                                $selesai = \Carbon\Carbon::parse($item->tanggal_selesai);
                            @endphp
                            <tr class="hover:bg-gray-50 transition {{ $item->approval_step == 10 ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3 font-semibold text-blue-600 whitespace-nowrap">#{{ $item->kode_pengajuan }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $item->jenisCuti->nama_cuti ?? 'Cuti Tahunan' }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $mulai->translatedFormat('d M Y') }}@if(!$mulai->isSameDay($selesai)) – {{ $selesai->translatedFormat('d M Y') }}@endif
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-gray-800">{{ $item->durasi_hari }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full border whitespace-nowrap {{ $warna }}">{{ $item->status_label }}</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('pegawai.detail', $item->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Detail →</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada riwayat pengajuan cuti yang sesuai dengan filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $riwayat->links() }}
        </div>
    </div>
</x-app-layout>