<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('admin.rekap.index') }}" class="hover:underline">Beranda / Rekap Cuti</a> / Detail Pegawai
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Detail Rekap: {{ $pegawai->nama }}
        </h2>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- KIRI: Profil & Informasi Kuota -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Card: Profil Pegawai -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 text-center">
                        <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center text-gray-500 font-bold text-2xl uppercase">
                            {{ substr($pegawai->nama, 0, 1) }}
                        </div>
                        <h3 class="font-bold text-xl text-gray-900">{{ $pegawai->nama }}</h3>
                        <p class="text-sm font-mono text-gray-500 mt-1">{{ $pegawai->nip }}</p>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <span class="inline-block bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                                {{ $pegawai->divisi }}
                            </span>
                        </div>
                    </div>

                    <!-- Card: Kuota Cuti & Tombol Edit Modal -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100" x-data="{ openEditModal: false }">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#2a64f5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Sisa Cuti Tahunan
                        </h3>
                        
                        <div class="flex items-baseline space-x-2 mb-6">
                            <!-- Data Sisa Cuti Dinamis -->
                            <span class="text-2xl font-black text-[#2a64f5] leading-none">{{ $pegawai->sisa_cuti }}</span>
                            <span class="text-gray-500 font-medium">/ {{ $pegawai->total_kuota }} Hari</span>
                        </div>

                        <!-- Tombol Pemicu Modal -->
                        <button @click="openEditModal = true" class="w-full py-2.5 border-2 border-[#2a64f5] text-[#2a64f5] rounded-xl font-bold text-sm hover:bg-blue-50 transition">
                            Edit Sisa Kuota
                        </button>

                        <!-- Modal Edit Kuota -->
                        <div x-show="openEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
                            <div x-show="openEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openEditModal = false"></div>
                            
                            <div x-show="openEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 mx-4 z-10">
                                
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-bold text-gray-900">Sesuaikan Kuota</h3>
                                    <button @click="openEditModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                </div>
                                
                                <form id="form-edit-jatah" action="{{ route('admin.rekap.update-jatah-individu', $pegawai->id) }}" method="POST" onsubmit="return konfirmasiEditJatah(event, '{{ $pegawai->nama }}')">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jatah Cuti Tahun Ini</label>
                                        <input type="number" name="jatah_cuti" min="0" max="365" required value="{{ $pegawai->total_kuota }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="text-xs text-gray-500 mt-2">Sisa cuti akan otomatis dihitung ulang sistem: jatah baru dikurangi pemakaian yang sudah tercatat.</p>
                                    </div>
                                    <button type="submit" class="w-full bg-[#2a64f5] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-blue-700 transition">
                                        Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Riwayat Cuti Pegawai -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100 h-full">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Riwayat Pengambilan Cuti</h3>
                        
                        @if(count($riwayats) > 0)
                            <div class="space-y-4">
                               <!-- Looping Data Riwayat Asli dari Database -->
                                @foreach($riwayats as $riwayat)
                                <!-- Ubah div menjadi tag <a> dan tambahkan hover border biru -->
                                <a href="{{ route('admin.approval.show', $riwayat->id) }}" class="block p-4 border border-gray-100 rounded-xl hover:bg-gray-50 hover:border-blue-300 hover:shadow-sm transition flex justify-between items-center group">
                                    <div>
                                        <h4 class="font-bold text-gray-900 group-hover:text-[#2a64f5] transition">{{ $riwayat->jenis }}</h4>
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Mulai: {{ $riwayat->tanggal_mulai }}
                                            <span class="mx-2">•</span>
                                            <span class="font-semibold text-gray-700">{{ $riwayat->durasi }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <!-- Logika Warna Status Dinamis -->
                                        @if($riwayat->status == 8)
                                            <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold border border-emerald-100">Disetujui</span>
                                        @elseif(in_array($riwayat->status, [0, 10]))
                                            <span class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-100">{{ $riwayat->status == 0 ? 'Ditolak' : 'Dibatalkan' }}</span>
                                        @else
                                            <span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-xs font-bold border border-amber-100">Menunggu</span>
                                        @endif
                                        
                                        <!-- Tambahan Icon Panah agar terlihat bisa diklik -->
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#2a64f5] transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-gray-500 font-medium">Belum ada riwayat cuti.</p>
                            </div>
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
                icon: 'success', title: 'Berhasil', text: @json(session('success')),
                confirmButtonColor: '#2a64f5',
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' }
            });
        @endif

        function konfirmasiEditJatah(e, nama) {
            e.preventDefault();
            const form = document.getElementById('form-edit-jatah');
            const jumlah = form.jatah_cuti.value;
            Swal.fire({
                title: 'Ubah Jatah Cuti?',
                html: `Jatah cuti <b>${nama}</b> akan diubah menjadi <b>${jumlah} hari</b>.`,
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Ya, Ubah', confirmButtonColor: '#2a64f5',
                cancelButtonText: 'Batal', reverseButtons: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' }
            }).then((result) => { if (result.isConfirmed) form.submit(); });
            return false;
        }
    </script>
</x-admin-layout>