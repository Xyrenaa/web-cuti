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

                @php
                    $bulanIndo = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ];
                    $sortByOptions = [
                        'nama' => 'Nama Pegawai',
                        'tanggal_pengajuan' => 'Pengajuan Terakhir',
                        'jumlah_ajuan' => 'Jumlah Ajuan',
                        'jumlah_terpakai' => 'Cuti Terpakai',
                        'sisa_jatah' => 'Sisa Jatah',
                    ];
                    $filterAktif = (request()->filled('divisi') && request('divisi') !== 'Semua Divisi')
                        || (request()->filled('sub_bagian') && request('sub_bagian') !== 'Semua Sub-Bagian')
                        || (request()->filled('jenis_cuti') && request('jenis_cuti') !== 'Semua Jenis')
                        || (request()->filled('bulan') && request('bulan') !== 'Semua Bulan')
                        || (request()->filled('tahun') && (int) request('tahun') !== (int) date('Y'))
                        || (request()->filled('sort_by') && request('sort_by') !== 'nama')
                        || (request()->filled('sort_dir') && request('sort_dir') !== 'asc');
                @endphp

                <!-- Form Pencarian, Filter & Sort -->
            <form action="{{ route('admin.rekap.index') }}" method="GET"
                  x-data='{
                      filterOpen: {{ $filterAktif ? "true" : "false" }},
                      divisiId: "{{ request("divisi") }}",
                      sortDir: "{{ $sortDir }}",
                      subBagianList: @json($daftarSubBagian),
                      get filteredSubBagian() {
                          if (!this.divisiId || this.divisiId === "Semua Divisi") return this.subBagianList;
                          return this.subBagianList.filter(s => s.bagian_bidang_id == this.divisiId);
                      }
                  }'
                  class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">

                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
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

                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto mt-4 lg:mt-0 items-end">

                                                <!-- Toggle Panel Filter -->
                        <button type="button" @click="filterOpen = !filterOpen"
                                class="relative w-full sm:w-auto flex items-center justify-center gap-2 border rounded-xl px-5 py-2.5 text-sm font-bold transition h-[42px] whitespace-nowrap"
                                :class="filterOpen || {{ $filterAktif ? 'true' : 'false' }} ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 01.8 1.6l-6.3 8.4v5.2a1 1 0 01-1.45.9l-4-2A1 1 0 018 16.2v-3.2L1.7 4.6A1 1 0 013 4z"></path></svg>
                            Filter
                            @if($filterAktif)
                                <span class="absolute -top-1.5 -right-1.5 h-2.5 w-2.5 rounded-full bg-blue-600 ring-2 ring-white"></span>
                            @endif
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="filterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Tombol Ekspor (Bawa query parameter agar filter ikut ke Excel) -->
                        <a href="{{ route('admin.rekap.export', request()->query()) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow-sm text-sm whitespace-nowrap h-[42px] flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Ekspor (XLSX)
                        </a>

                        <button type="button" x-data @click="$dispatch('open-modal-jatah')" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-2.5 px-5 rounded-xl transition shadow-sm text-sm whitespace-nowrap h-[42px] flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Update Jatah Cuti Massal
                        </button>
                    </div>
                </div>

<!-- Panel Filter Lanjutan: collapsible, isinya Divisi + Sub-Bagian (baru) + Urutkan -->
                                <div x-show="filterOpen" x-cloak x-transition class="mt-4 pt-4 border-t border-gray-100 space-y-5">

                    <!-- Baris 1: Filter Data -->
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Filter Data</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

                            <!-- Filter Divisi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">Bidang / Bagian</label>
                                <select name="divisi" x-model="divisiId" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                                    <option value="Semua Divisi">Semua Bidang/Bagian</option>
                                    @foreach($daftarDivisi as $div)
                                        <option value="{{ $div->id }}">{{ $div->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Sub-Bagian/Seksi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">Sub-Bagian / Seksi</label>
                                <select name="sub_bagian" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                                    <option value="Semua Sub-Bagian">Semua Sub-Bagian</option>
                                    <template x-for="sub in filteredSubBagian" :key="sub.id">
                                        <option :value="sub.id" x-text="sub.nama" :selected="sub.id == '{{ request('sub_bagian') }}'"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Filter Jenis Cuti -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">Jenis Cuti</label>
                                <select name="jenis_cuti" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                                    <option value="Semua Jenis">Semua Jenis Cuti</option>
                                    @foreach($daftarJenisCuti as $jenis)
                                        <option value="{{ $jenis->id }}" {{ (string) request('jenis_cuti') === (string) $jenis->id ? 'selected' : '' }}>{{ $jenis->nama_cuti }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Tahun -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">Tahun</label>
                                <select name="tahun" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                                    @foreach($daftarTahun as $th)
                                        <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>{{ $th }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Bulan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">Bulan</label>
                                <select name="bulan" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                                    <option value="Semua Bulan">Semua Bulan</option>
                                    @foreach($bulanIndo as $angka => $nama)
                                        <option value="{{ $angka }}" {{ $bulan == $angka ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- Baris 2: Urutkan -->
                    <div class="flex flex-col sm:flex-row sm:items-end gap-3 pt-1">
                        <div class="w-full sm:w-64">
                            <label class="block text-xs font-bold text-gray-600 mb-2">Urutkan Berdasarkan</label>
                            <select name="sort_by" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 bg-gray-50 text-gray-600">
                                @foreach($sortByOptions as $val => $label)
                                    <option value="{{ $val }}" {{ $sortBy === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Arah Urutan: segmented control -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-2">Arah</label>
                            <input type="hidden" name="sort_dir" :value="sortDir">
                            <div class="inline-flex rounded-xl border border-gray-200 bg-gray-50 p-1 h-[42px]">
                                <button type="button" @click="sortDir = 'asc'"
                                        class="flex items-center gap-1.5 px-4 rounded-lg text-sm font-bold transition"
                                        :class="sortDir === 'asc' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4h13M3 8h9M3 12h5m4 8V4m0 16l-4-4m4 4l4-4"></path></svg>
                                    Ascending
                                </button>
                                <button type="button" @click="sortDir = 'desc'"
                                        class="flex items-center gap-1.5 px-4 rounded-lg text-sm font-bold transition"
                                        :class="sortDir === 'desc' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4h5m-5 4h9m-9 4h13m-4-8v16m0 0l4-4m-4 4l-4-4"></path></svg>
                                    Descending
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Terapkan & Reset Filter -->
                        <div class="flex items-center gap-3 sm:ml-auto">
                            <button type="submit" class="bg-[#2a64f5] hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow-sm text-sm h-[42px] whitespace-nowrap">
                                Terapkan Filter
                            </button>

                            @if($filterAktif)
                                <a href="{{ route('admin.rekap.index', array_filter(['search' => request('search')])) }}" class="text-sm font-bold text-red-500 hover:text-red-700 py-2.5 whitespace-nowrap">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
            <!-- Modal Update Jatah Cuti Massal -->
            <div x-data="{ open: false, target: 'semua' }" @open-modal-jatah.window="open = true">
                <div x-show="open" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>

                    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                         class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 mx-4 z-10">

                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Update Jatah Cuti Massal</h3>
                         <button type="button" x-data @click="$dispatch('open-modal-jatah')" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-2.5 px-5 rounded-xl transition shadow-sm text-sm whitespace-nowrap h-[42px] flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Update Jatah Cuti Massal
                        </button>
                        <a href="{{ route('admin.rekap.tutup-tahun.preview') }}" class="bg-amber-50 border border-amber-200 hover:bg-amber-100 text-amber-700 font-bold py-2.5 px-5 rounded-xl transition shadow-sm text-sm whitespace-nowrap h-[42px] flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Tutup Tahun
                        </a>
                        </div>

                        <form id="form-update-jatah" action="{{ route('admin.rekap.update-jatah') }}" method="POST" onsubmit="return konfirmasiUpdateJatah(event)">
                            @csrf

                            <label class="block text-xs font-bold text-gray-600 mb-1">Jumlah Hari</label>
                            <input type="number" name="jumlah_hari" min="0" max="365" required
                                   class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition text-sm py-2.5 mb-4"
                                   placeholder="Contoh: 12">

                            <label class="block text-xs font-bold text-gray-600 mb-1">Terapkan Untuk</label>
                            <select name="target" x-model="target" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 transition text-sm py-2.5 bg-gray-50 mb-4">
                                <option value="semua">Semua Pegawai ({{ $totalPegawai }} orang)</option>
                                <option value="divisi">Divisi Tertentu</option>
                                <option value="sub_bagian">Sub-Bagian/Seksi Tertentu</option>
                            </select>

                            <div x-show="target === 'divisi'" class="mb-4">
                                <select name="bagian_bidang_id" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 transition text-sm py-2.5">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($daftarDivisi as $div)
                                        <option value="{{ $div->id }}">{{ $div->nama }} ({{ $div->users_count }} orang)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="target === 'sub_bagian'" class="mb-4">
                                <select name="sub_bagian_seksi_id" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 transition text-sm py-2.5">
                                    <option value="">-- Pilih Sub-Bagian/Seksi --</option>
                                    @foreach($daftarSubBagian as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->nama }} — {{ $sub->bagianBidang->nama ?? '-' }} ({{ $sub->users_count }} orang)</option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="text-xs text-gray-400 mb-4">Nilai lama akan ditimpa langsung dan tidak tersimpan sebagai riwayat.</p>

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                                <button type="submit" class="px-5 py-2.5 bg-[#2a64f5] hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition shadow-sm">Ubah</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
                                <!-- Info Periode Aktif -->
                <div class="flex flex-wrap items-center gap-2 mb-4 text-xs">
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 font-bold px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Periode: {{ $bulan ? $bulanIndo[$bulan] . ' ' : '' }}{{ $tahun }}
                    </span>
                    @if(request()->filled('jenis_cuti') && request('jenis_cuti') !== 'Semua Jenis')
                        <span class="inline-flex items-center gap-1.5 bg-gray-50 text-gray-600 font-bold px-3 py-1.5 rounded-full border border-gray-100">
                            Jenis: {{ optional($daftarJenisCuti->firstWhere('id', (int) request('jenis_cuti')))->nama_cuti }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 bg-gray-50 text-gray-600 font-bold px-3 py-1.5 rounded-full border border-gray-100">
                        Urut: {{ $sortByOptions[$sortBy] ?? 'Nama Pegawai' }} ({{ $sortDir === 'desc' ? 'Descending' : 'Ascending' }})
                    </span>
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
                                <th class="px-6 py-4 whitespace-nowrap text-center">Jumlah Ajuan</th>
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

                                <td class="px-6 py-5 text-center text-gray-600 font-semibold">{{ $rekap->jumlah_ajuan }}</td>

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
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500 font-medium">Belum ada data pegawai untuk periode/filter ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

<!-- Pagination -->
                <div class="p-6 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                    <div>Menampilkan {{ $rekaps->firstItem() ?? 0 }} sampai {{ $rekaps->lastItem() ?? 0 }} dari {{ $rekaps->total() }} data</div>
                    
                    <div class="flex items-center space-x-2 mt-4 md:mt-0">
                        <!-- Tombol Sebelumnya -->
                        @if ($rekaps->onFirstPage())
                            <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">Sebelumnya</span>
                        @else
                            <a href="{{ $rekaps->previousPageUrl() }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Sebelumnya</a>
                        @endif

                        <!-- Logika Angka dan Elipsis -->
                        @foreach ($rekaps->linkCollection()->slice(1, -1) as $link)
                            @if ($link['label'] === '...')
                                <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">...</span>
                            @elseif ($link['active'])
                                <span class="px-4 py-2 bg-[#2a64f5] text-white rounded-xl font-bold shadow-md shadow-blue-500/20">{{ $link['label'] }}</span>
                            @else
                                <a href="{{ $link['url'] }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">{{ $link['label'] }}</a>
                            @endif
                        @endforeach

                        <!-- Tombol Selanjutnya -->
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('success')),
                confirmButtonColor: '#2a64f5',
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' }
            });
        @endif

        function konfirmasiUpdateJatah(e) {
            e.preventDefault();
            const form = document.getElementById('form-update-jatah');
            const jumlah = form.jumlah_hari.value;

            if (!jumlah) {
                Swal.fire({ icon: 'warning', title: 'Isi jumlah hari dulu', confirmButtonColor: '#2a64f5', customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' } });
                return false;
            }

            const targetText = {
                semua: 'SEMUA pegawai',
                divisi: 'pegawai di divisi yang dipilih',
                sub_bagian: 'pegawai di sub-bagian/seksi yang dipilih'
            }[form.target.value];

            Swal.fire({
                title: 'Ubah Jatah Cuti?',
                html: `Jatah cuti akan diubah menjadi <b>${jumlah} hari</b> untuk <b>${targetText}</b>. Nilai lama akan ditimpa.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ubah',
                confirmButtonColor: '#2a64f5',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100', title: 'text-xl font-bold text-gray-800' }
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });

            return false;
        }
    </script>
</x-admin-layout>