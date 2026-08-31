<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Beranda / Approval Cuti / Detail Pengajuan</div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Detail Pengajuan Cuti
        </h2>
    </x-slot>

    <!-- Ambil variabel step untuk logika UI -->
    @php
        $step = $data->approval_step ?? 0;
        $status = $data->status_pengajuan ?? '';
    @endphp

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- KIRI: Informasi Detail -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card: Rincian Cuti -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Rincian Cuti</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Nomor pengajuan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->nomor_pengajuan ?? 'CT-2026-000148' }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Tanggal pengajuan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('l, d F Y, H:i') }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Jenis Cuti</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->jenisCuti->nama_cuti ?? 'Cuti Tahunan' }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Durasi</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">
                                    {{ \Carbon\Carbon::parse($data->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($data->tanggal_selesai)) + 1 }} Hari
                                </dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Tanggal Mulai</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_mulai)->translatedFormat('l, d F Y') }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Selesai</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_selesai)->translatedFormat('l, d F Y') }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Keterangan -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Keterangan</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Alasan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2 leading-relaxed">{{ $data->alasan }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Alamat Selama Cuti</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->lokasi }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Kontak Darurat</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">082133550336 (Ibu Rahma)</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Pengganti Tugas</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">Liino (NIP: 199105142017041002)</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Lampiran -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-4">Lampiran</h3>
                        <div class="border border-gray-200 rounded-xl p-4 flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span class="text-sm font-bold text-gray-800">surat-pengajuan-cuti.pdf</span>
                            </div>
                            <a href="#" class="text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition">Unduh File</a>
                        </div>
                    </div>

                </div>

                <!-- KANAN: Timeline & Action Buttons -->
                <div class="lg:col-span-1 flex flex-col gap-6">
                    
                    <!-- Card: Alur Persetujuan Dinamis -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Alur Persetujuan</h3>
                        
                        <div class="relative border-l-2 border-gray-100 ml-3 space-y-6 pb-2">
                            
                            <!-- 1. Pengajuan Dikirim -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full bg-[#1e3a8a] -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm text-gray-900">Pengajuan Dikirim</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Oleh Pegawai</p>
                            </div>
                            
                            <!-- 2. Kepala Seksi -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $step > 1 ? 'bg-[#1e3a8a]' : ($step == 1 ? 'bg-amber-400 animate-pulse' : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $step > 1 ? 'text-gray-900' : ($step == 1 ? 'text-amber-600' : 'text-gray-400') }}">Persetujuan Kepala Seksi</h4>
                                <p class="text-[11px] {{ $step == 1 ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">{{ $step > 1 ? 'Selesai' : ($step == 1 ? 'Menunggu Validasi' : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 3. Kepala Bidang -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $step > 2 ? 'bg-[#1e3a8a]' : ($step == 2 ? 'bg-amber-400 animate-pulse' : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $step > 2 ? 'text-gray-900' : ($step == 2 ? 'text-amber-600' : 'text-gray-400') }}">Persetujuan Kepala Bidang</h4>
                                <p class="text-[11px] {{ $step == 2 ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">{{ $step > 2 ? 'Selesai' : ($step == 2 ? 'Menunggu Validasi' : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 4. Kasubag (Dengan indikator pengecekan Admin di Step 3) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $step > 4 ? 'bg-[#1e3a8a]' : (in_array($step, [3, 4]) ? 'bg-amber-400 animate-pulse' : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $step > 4 ? 'text-gray-900' : (in_array($step, [3, 4]) ? 'text-amber-600' : 'text-gray-400') }}">Persetujuan Kasubag</h4>
                                <p class="text-[11px] {{ in_array($step, [3, 4]) ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">{{ $step > 4 ? 'Selesai' : ($step == 3 ? 'Menunggu Verifikasi Admin' : ($step == 4 ? 'Menunggu Validasi Kasubag' : 'Menunggu Tahap Sebelumnya')) }}</p>
                            </div>

                            <!-- 5. Kepala TU (Dengan indikator pengecekan Admin di Step 5) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $step > 6 ? 'bg-[#1e3a8a]' : (in_array($step, [5, 6]) ? 'bg-amber-400 animate-pulse' : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $step > 6 ? 'text-gray-900' : (in_array($step, [5, 6]) ? 'text-amber-600' : 'text-gray-400') }}">Persetujuan Kepala TU</h4>
                                <p class="text-[11px] {{ in_array($step, [5, 6]) ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">{{ $step > 6 ? 'Selesai' : ($step == 5 ? 'Menunggu Verifikasi Admin' : ($step == 6 ? 'Menunggu Validasi TU' : 'Menunggu Tahap Sebelumnya')) }}</p>
                            </div>

                            <!-- 6. Kepala Kantor (Dengan indikator pengecekan Admin di Step 7) -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $step > 8 ? 'bg-[#1e3a8a]' : (in_array($step, [7, 8]) ? 'bg-amber-400 animate-pulse' : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $step > 8 ? 'text-gray-900' : (in_array($step, [7, 8]) ? 'text-amber-600' : 'text-gray-400') }}">Persetujuan Kepala Kantor</h4>
                                <p class="text-[11px] {{ in_array($step, [7, 8]) ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">{{ $step > 8 ? 'Selesai' : ($step == 7 ? 'Menunggu Verifikasi Admin Terakhir' : ($step == 8 ? 'Menunggu Persetujuan Final Kakan' : 'Menunggu Tahap Sebelumnya')) }}</p>
                            </div>

                            <!-- 7. Final -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $status == 'Disetujui' ? 'bg-[#1e3a8a]' : 'bg-gray-300' }}"></div>
                                <h4 class="font-bold text-sm {{ $status == 'Disetujui' ? 'text-gray-900' : 'text-gray-400' }}">Cuti Diterima</h4>
                            </div>

                        </div>
                    </div>

                <!-- HANYA TAMPIL JIKA BERKAS ADA DI TANGAN ADMIN (Step 3, 5, atau 7) -->
                    @if(in_array($step, [3, 5, 7]))
                    
                    <!-- x-data untuk mengontrol state modal dan jenis aksi -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100" x-data="{ openModal: false, modalAction: '' }">
                        
                        <div class="space-y-3">
                            <!-- Teks tombol Setujui dinamis -->
                            @php
                                $btnText = 'Setujui';
                                if($step == 3) $btnText = 'Setujui & Teruskan ke Kasubag';
                                if($step == 5) $btnText = 'Setujui & Teruskan ke Kepala TU';
                                if($step == 7) $btnText = 'Setujui & Teruskan ke Kepala Kantor';
                            @endphp
                            
                            <!-- Tombol Setujui (Memicu Modal) -->
                            <button type="button" @click="openModal = true; modalAction = 'setujui'" class="w-full bg-[#2a64f5] text-white rounded-lg py-3 font-bold text-sm hover:bg-blue-700 transition">
                                {{ $btnText }}
                            </button>
                            
                            <!-- Tombol Revisi & Tolak (Memicu Modal) -->
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="openModal = true; modalAction = 'revisi'" class="w-full border border-orange-400 text-orange-500 rounded-lg py-2.5 font-bold text-sm hover:bg-orange-50 transition">
                                    Revisi
                                </button>
                                <button type="button" @click="openModal = true; modalAction = 'tolak'" class="w-full border border-red-500 text-red-600 rounded-lg py-2.5 font-bold text-sm hover:bg-red-50 transition">
                                    Tolak
                                </button>
                            </div>
                        </div>

                        <!-- ================= MODAL KONFIRMASI DINAMIS ================= -->
                        <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
                            <!-- Background Overlay Gelap -->
                            <div x-show="openModal" 
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="absolute inset-0 bg-black/40 backdrop-blur-sm" 
                                 @click="openModal = false">
                            </div>

                            <!-- Kotak Modal -->
                            <div x-show="openModal" 
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 mx-4 z-10">
                                
                                <div class="flex justify-between items-center mb-2">
                                    <!-- Judul Dinamis -->
                                    <h3 class="text-xl font-bold text-gray-900" 
                                        x-text="modalAction === 'setujui' ? 'Konfirmasi Persetujuan' : 'Alasan Penolakan/Revisi'">
                                    </h3>
                                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                
                                <!-- Deskripsi Dinamis -->
                                <p class="text-sm text-gray-500 mb-5" 
                                   x-text="modalAction === 'setujui' ? 'Apakah Anda yakin dokumen pengajuan ini sudah lengkap dan siap diteruskan ke tahap persetujuan berikutnya?' : 'Silakan berikan deskripsi atau alasan detail mengapa pengajuan ini memerlukan revisi atau ditolak.'">
                                </p>

                                <form action="{{ route('admin.approval.verifikasi', $data->id) }}" method="POST">
                                    @csrf
                                    <!-- Input hidden untuk mengirimkan aksi yang dipilih -->
                                    <input type="hidden" name="action" x-bind:value="modalAction">
                                    
                                    <!-- Kolom Teks Alasan (Hanya muncul jika aksi BUKAN 'setujui') -->
                                    <div x-show="modalAction !== 'setujui'">
                                        <textarea name="catatan" rows="4" x-bind:required="modalAction !== 'setujui'"
                                            class="w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-6 bg-gray-50" 
                                            placeholder="Tuliskan catatan Anda di sini..."></textarea>
                                    </div>
                                    
                                    <div class="flex justify-end gap-3 mt-2">
                                        <button type="button" @click="openModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                            Batal
                                        </button>
                                        
                                        <!-- Tombol Submit dengan Warna Dinamis -->
                                        <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition shadow-sm"
                                                x-bind:class="{
                                                    'bg-blue-600 hover:bg-blue-700': modalAction === 'setujui',
                                                    'bg-orange-500 hover:bg-orange-600': modalAction === 'revisi',
                                                    'bg-red-600 hover:bg-red-700': modalAction === 'tolak'
                                                }">
                                            <span x-text="modalAction === 'setujui' ? 'Ya, Teruskan' : 'Kirim Tanggapan'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- ================= END MODAL ================= -->

                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-admin-layout>