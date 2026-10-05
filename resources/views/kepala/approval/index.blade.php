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

        <!-- Notifikasi Sukses / Gagal dari aksi approve/tolak/revisi -->
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm" role="alert">
            {{ session('error') }}
        </div>
        @endif
        @if(session('warning'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded shadow-sm" role="alert">
            {{ session('warning') }}
        </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white border-b border-gray-100 shadow-sm rounded-t-xl">
            
            <!-- Filter Section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 w-full">
                <form action="{{ route('kepala.approval.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Cari Pengajuan</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama atau NIP..." class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5">
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
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 text-gray-400">
                    </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="bg-[#2A65F3] hover:bg-blue-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-colors flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Cari
                            </button>
                        <a href="{{ route('kepala.approval.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-5 rounded-xl transition-colors text-center border border-gray-200">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== ANTREAN APPROVAL MILIK SENDIRI (disembunyikan untuk Pegawai biasa yang cuma jadi PLH) ===== --}}
        @if($peranSendiri)
        @if($antreanPlh->isNotEmpty())
            <h3 class="mt-6 text-lg font-bold text-gray-900">Antrean Approval Saya</h3>
        @endif

        <!-- Table Section -->
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden mt-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-white text-gray-500 text-[11px] font-bold border-b border-gray-100 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-5">NO</th>
                            <th class="px-6 py-5">KODE</th>
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
                            <td class="px-6 py-5 font-bold text-gray-900">{{ $pengajuan->kode_pengajuan ?? '-' }}</td>
                            <!-- Mengambil relasi dari user -->
                            <td class="px-6 py-5 font-bold text-gray-900">{{ $pengajuan->user->name ?? '-' }}</td>
                            <td class="px-6 py-5 text-gray-500 font-mono text-xs">{{ $pengajuan->user->nip ?? '-' }}</td>
                            
                            <!-- Relasi ke JenisCuti, kolom aslinya nama_cuti -->
                            <td class="px-6 py-5 text-gray-600">{{ $pengajuan->jenisCuti->nama_cuti ?? '-' }}</td>
                            
                            <!-- Format tanggal menggunakan Carbon -->
                            <td class="px-6 py-5 text-gray-500">{{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') }}</td>
                            
                            <td class="px-6 py-5 font-bold text-gray-800">{{ $pengajuan->durasi_hari ?? 0 }} Hari</td>
                            
                            @php
                                $step = $pengajuan->approval_step;
                                $warnaBadge = match(true) {
                                    $step === 0 => 'bg-red-100 text-red-700',
                                    $step === 8 => 'bg-green-100 text-green-700',
                                    $step === 9 => 'bg-orange-100 text-orange-700',
                                    $step === 10 => 'bg-gray-200 text-gray-600',
                                    default => 'bg-[#fef3c7] text-[#b45309]',
                                };
                            @endphp
                            <td class="px-6 py-5">
                                <span class="{{ $warnaBadge }} text-[11px] px-3 py-1.5 rounded-full font-bold">
                                    {{ $pengajuan->status_label ?? 'Menunggu' }}
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
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500">
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
        @endif
        {{-- ===== SECTION TERPISAH: SEDANG MENJADI PLH UNTUK... ===== --}}
        @foreach($antreanPlh as $plh)
            <div class="mt-10">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                    <h3 class="text-lg font-bold text-gray-900">Sedang Menjadi {{ $plh['jenis'] ?? 'PLH' }} Untuk {{ $plh['label'] ?? $plh['kepala']->name }}</h3>
                    <span class="text-[11px] font-bold px-3 py-1.5 rounded-full bg-purple-100 text-purple-700">{{ $plh['jenis'] ?? 'PLH' }} {{ $plh['peran'] }}</span>
                </div>
                <p class="mb-3 text-xs text-gray-500">
                    @if($plh['sampai'])
                        Berlaku sampai {{ \Carbon\Carbon::parse($plh['sampai'])->translatedFormat('d F Y') }}.
                    @else
                        Berlaku sampai dicabut oleh Superadmin.
                    @endif
                    Pengajuan di bawah ini seharusnya masuk ke meja jabatan tersebut dan kini menjadi wewenang Anda selama masa penugasan.
                </p>

                <div class="overflow-hidden bg-white border border-purple-100 shadow-sm rounded-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-[11px] font-bold tracking-wider text-gray-500 uppercase border-b border-gray-100 bg-purple-50/50">
                                <tr>
                                    <th class="px-6 py-4">KODE</th>
                                    <th class="px-6 py-4">NAMA PEGAWAI</th>
                                    <th class="px-6 py-4">JENIS CUTI</th>
                                    <th class="px-6 py-4">TANGGAL PENGAJUAN</th>
                                    <th class="px-6 py-4">DURASI</th>
                                    <th class="px-6 py-4 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($plh['pengajuans'] as $item)
                                    <tr class="transition hover:bg-gray-50/50">
                                        <td class="px-6 py-4 font-bold text-gray-900">{{ $item->kode_pengajuan ?? '-' }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-900">{{ $item->user->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $item->jenisCuti->nama_cuti ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-500">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-800">{{ $item->durasi_hari ?? 0 }} Hari</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('kepala.approval.show', $item->id) }}" class="text-[#2a64f5] hover:text-blue-800 font-bold text-sm inline-flex items-center">
                                                Lihat Detail <span class="ml-1 text-lg leading-none">&rarr;</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            Belum ada pengajuan yang perlu diproses atas nama {{ $plh['label'] ?? $plh['kepala']->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>