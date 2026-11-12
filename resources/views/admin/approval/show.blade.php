<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Beranda / Approval Cuti / Detail Pengajuan</div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Detail Pengajuan Cuti
        </h2>
    </x-slot>

    <!-- Ambil variabel step untuk logika UI -->
    @php
     $step   = (int) ($data->approval_step ?? 0);              // nilai asli
    $halted = in_array($step, [0, 10]);
    $posisi = in_array($step, [0, 9, 10]) ? -1 : $step; 
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
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->kode_pengajuan }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Tanggal pengajuan</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('l, d/m/Y H:i') }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Jenis Cuti</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->jenisCuti->nama_cuti ?? 'Cuti Tahunan' }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Durasi</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->durasi_hari }} Hari</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Tanggal Mulai</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_mulai)->translatedFormat('l, d/m/Y') }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4">
                                <dt class="text-sm text-gray-500 font-medium">Selesai</dt>
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ \Carbon\Carbon::parse($data->tanggal_selesai)->translatedFormat('l, d/m/Y') }}</dd>
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
                                <dd class="text-sm font-bold text-gray-900 md:col-span-2">{{ $data->lokasi ?? '-' }}</dd>
                            </div>
                         </div>
                     </div>

                    <!-- Card: Lampiran -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-4">Lampiran</h3>
                        
                        $bukti = $data->daftarBerkas('bukti');

                        @if($data->surat_pengajuan || !empty($buktiPendukung))
                            <div class="space-y-3">
                                @if($data->surat_pengajuan)
                                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition gap-4">
                                        <div class="flex items-center space-x-3 overflow-hidden">
                                            <svg class="w-6 h-6 text-[#2a64f5] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            <span class="text-sm font-bold text-gray-800 truncate">{{ basename($data->surat_pengajuan) }} (Surat Wajib)</span>
                                        </div>
                                        <a href="{{ asset('storage/' . $data->surat_pengajuan) }}" target="_blank" class="text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition flex-shrink-0">
                                            Unduh File
                                        </a>
                                    </div>
                                @endif

                                @foreach($buktiPendukung as $file)
                                    @if(is_string($file))
                                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition gap-4">
                                        <div class="flex items-center space-x-3 overflow-hidden">
                                            <svg class="w-6 h-6 text-[#2a64f5] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            <span class="text-sm font-bold text-gray-800 truncate">{{ basename($file) }}</span>
                                        </div>
                                        <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition flex-shrink-0">
                                            Unduh File
                                        </a>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-gray-500 italic p-4 text-center border border-dashed border-gray-200 rounded-xl">
                                Tidak ada file lampiran yang diunggah.
                            </div>
                        @endif

                        @if(!empty($data->dokumen_ttd))
                        <div class="pt-3 mt-3 border-t border-dashed border-gray-200">
                            <p class="mb-2 text-xs font-bold tracking-wide text-gray-400 uppercase">Dokumen Bertanda Tangan</p>
                            @foreach($data->dokumen_ttd as $ttd)
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 mb-2 transition border border-green-200 bg-green-50/50 hover:bg-green-50 rounded-xl">
                                <div class="flex items-center overflow-hidden space-x-3">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div class="truncate">
                                        <span class="text-sm font-bold text-gray-800">{{ basename($ttd['file']) }}</span>
                                        <p class="text-xs text-gray-500">Ditandatangani {{ $ttd['nama'] ?? '-' }} ({{ $ttd['peran'] ?? '-' }}) &middot; {{ \Carbon\Carbon::parse($ttd['waktu'])->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/'.$ttd['file']) }}" target="_blank" class="flex-shrink-0 text-sm font-bold text-green-700 hover:text-green-900 transition">Unduh File</a>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                </div> 
                <!-- ================= AKHIR KOLOM KIRI ================= -->

               <!-- KANAN: Timeline & Action Buttons -->
                <div class="lg:col-span-1 flex flex-col gap-6">
                    
                    <!-- Card: Alur Persetujuan Dinamis -->
                    <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-6">Alur Persetujuan</h3>
                        
                        @php
                            $step = $data->approval_step ?? 0;
                            $halted = in_array($step, [0,10]);
                        @endphp

                        <div class="relative border-l-2 border-gray-100 ml-3 space-y-6 pb-2">
                            
                            <!-- 1. Pengajuan Dikirim -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full {{ $step === 10 ? 'bg-gray-300' : 'bg-[#1e3a8a]' }} -left-[11px] top-0 border-4 border-white shadow-sm"></div>
                                <h4 class="font-bold text-sm {{ $step === 10 ? 'text-gray-400' : 'text-gray-900' }}">Pengajuan Dikirim</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Oleh Pegawai</p>
                            </div>
                            
                            <!-- 2. Kepala Seksi -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $posisi > 1 ? 'bg-[#1e3a8a]' : ($posisi == 1 ? ($halted ? 'bg-red-500' : 'bg-amber-400 animate-pulse') : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $posisi > 1 ? 'text-gray-900' : ($posisi == 1 ? ($halted ? 'text-red-600' : 'text-amber-600') : 'text-gray-400') }}">Persetujuan Kepala Seksi</h4>
                                <p class="text-[11px] {{ $posisi == 1 ? ($halted ? 'text-red-500' : 'text-gray-500') : 'text-gray-400' }} mt-0.5">{{ $posisi > 1 ? 'Selesai' : ($posisi == 1 ? ($halted ? $data->status_label : 'Menunggu Validasi') : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 3. Kepala Bidang -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $posisi > 2 ? 'bg-[#1e3a8a]' : ($posisi == 2 ? ($halted ? 'bg-red-500' : 'bg-amber-400 animate-pulse') : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $posisi > 2 ? 'text-gray-900' : ($posisi == 2 ? ($halted ? 'text-red-600' : 'text-amber-600') : 'text-gray-400') }}">Persetujuan Kepala Bidang</h4>
                                <p class="text-[11px] {{ $posisi == 2 ? ($halted ? 'text-red-500' : 'text-gray-500') : 'text-gray-400' }} mt-0.5">{{ $posisi > 2 ? 'Selesai' : ($posisi == 2 ? ($halted ? $data->status_label : 'Menunggu Validasi') : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 4. Kasubag -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $posisi > 4 ? 'bg-[#1e3a8a]' : (in_array($posisi, [3, 4]) ? 'bg-amber-400 animate-pulse' : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $posisi > 4 ? 'text-gray-900' : (in_array($posisi, [3, 4]) ? 'text-amber-600' : 'text-gray-400') }}">Persetujuan Kasubag</h4>
                                <p class="text-[11px] {{ in_array($posisi, [3, 4]) ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">{{ $posisi > 4 ? 'Selesai' : (in_array($posisi, [3, 4]) ? ($posisi == 3 ? 'Menunggu Verifikasi Admin' : 'Menunggu Validasi Kasubag') : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 5. Kepala TU -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $posisi > 5 ? 'bg-[#1e3a8a]' : ($posisi == 5 ? ($halted ? 'bg-red-500' : 'bg-amber-400 animate-pulse') : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $posisi > 5 ? 'text-gray-900' : ($posisi == 5 ? ($halted ? 'text-red-600' : 'text-amber-600') : 'text-gray-400') }}">Persetujuan Kepala TU</h4>
                                <p class="text-[11px] {{ $posisi == 5 ? ($halted ? 'text-red-500' : 'text-gray-500') : 'text-gray-400' }} mt-0.5">{{ $posisi > 5 ? 'Selesai' : ($posisi == 5 ? ($halted ? $data->status_label : 'Menunggu Validasi TU') : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 6. Kepala Kantor -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $posisi > 6 ? 'bg-[#1e3a8a]' : ($posisi == 6 ? ($halted ? 'bg-red-500' : 'bg-amber-400 animate-pulse') : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $posisi > 6 ? 'text-gray-900' : ($posisi == 6 ? ($halted ? 'text-red-600' : 'text-amber-600') : 'text-gray-400') }}">Persetujuan Kepala Kantor</h4>
                                <p class="text-[11px] {{ $posisi == 6 ? ($halted ? 'text-red-500' : 'text-gray-500') : 'text-gray-400' }} mt-0.5">{{ $posisi > 6 ? 'Selesai' : ($posisi == 6 ? ($halted ? $data->status_label : 'Menunggu Validasi Kakan') : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 7. Finalisasi Penomoran -->
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $posisi > 7 ? 'bg-[#1e3a8a]' : ($posisi == 7 ? ($halted ? 'bg-red-500' : 'bg-amber-400 animate-pulse') : 'bg-gray-300') }}"></div>
                                <h4 class="font-bold text-sm {{ $posisi > 7 ? 'text-gray-900' : ($posisi == 7 ? ($halted ? 'text-red-600' : 'text-amber-600') : 'text-gray-400') }}">Finalisasi Penomoran</h4>
                                <p class="text-[11px] {{ $posisi == 7 ? ($halted ? 'text-red-500' : 'text-gray-500') : 'text-gray-400' }} mt-0.5">{{ $posisi > 7 ? 'Selesai' : ($posisi == 7 ? ($halted ? $data->status_label : 'Menunggu Proses Admin') : 'Menunggu Tahap Sebelumnya') }}</p>
                            </div>

                            <!-- 8. Final -->
                            @php
                                $warnaAkhir = match ($step) { 8 => 'bg-[#1e3a8a]', 0 => 'bg-red-500', 9 => 'bg-amber-400', default => 'bg-gray-300' };
                                $judulAkhir = match ($step) { 0 => 'Pengajuan Ditolak', 9 => 'Dikembalikan untuk Revisi', 10 => 'Pengajuan Dibatalkan', default => 'Cuti Diterima' };
                                $teksAkhir  = $step === 8 ? 'text-gray-900' : ($step === 0 ? 'text-red-600' : 'text-gray-400');
                            @endphp
                            <div class="relative pl-6">
                                <div class="absolute w-5 h-5 rounded-full -left-[11px] top-0 border-4 border-white shadow-sm {{ $warnaAkhir }}"></div>
                                <h4 class="font-bold text-sm {{ $teksAkhir }}">{{ $judulAkhir }}</h4>
                            </div>

                        </div>
                    </div>
                    @if(session('error'))
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border-l-4 border-red-500 rounded shadow-sm" role="alert">{{ session('error') }}</div>
                    @endif

                    {{-- Gerbang PLH: Kepala (non-Kakan) wajib punya PLH sebelum difinalisasi --}}
                    @php
                        $plhKurang = $step == 7 && $data->butuhPlh() && ! $data->plh_user_id;
                    @endphp
                    @if($step == 7 && $data->butuhPlh())
                        <div class="p-4 mb-4 text-sm rounded-xl border {{ $plhKurang ? 'bg-amber-50 border-amber-300 text-amber-800' : 'bg-green-50 border-green-200 text-green-800' }}">
                            @if($plhKurang)
                                <strong>PLH belum ditunjuk.</strong> {{ $data->user->name }} perlu menunjuk PLH dulu dari halaman detail pengajuannya. Finalisasi baru bisa dilakukan setelah itu.
                            @else
                                <strong>PLH:</strong> {{ $data->plh->name ?? '-' }}
                            @endif
                        </div>
                    @endif

                    <!-- HANYA TAMPIL JIKA BERKAS ADA DI TANGAN ADMIN (Step 3 atau 7) -->
                    @if(in_array($step, [3, 7]))
                    
                    <!-- x-data untuk mengontrol state modal dan jenis aksi -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100" x-data="{ openModal: false, modalAction: '' }">
                        
                        <div class="space-y-3">
                            <!-- Teks tombol Setujui dinamis -->
                            @php
                                $btnText = 'Setujui';
                                if($step == 3) $btnText = 'Setujui & Teruskan ke Kasubag';
                                if($step == 7) $btnText = 'Selesaikan & Terbitkan Nomor Cuti';
                            @endphp

                            @if($step == 3)
                            <div class="mb-2">
                                <label class="block mb-1 text-xs font-semibold text-gray-600">
                                    Upload Dokumen yang Sudah Ditandatangani <span class="font-normal text-gray-400">(opsional)</span>
                                </label>
                                <input type="file" name="dokumen_ttd" form="form-verifikasi-admin" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    class="block w-full text-xs text-gray-600 border border-gray-300 rounded-lg file:mr-3 file:py-2 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2a64f5] hover:file:bg-blue-100">
                            </div>
                            @endif

                            <!-- Tombol Setujui (Memicu Modal) -->
                            <button type="button" @click="openModal = true; modalAction = 'setujui'"
                                    @if($plhKurang) disabled title="Menunggu Kepala menunjuk PLH" @endif
                                    class="w-full bg-[#2a64f5] text-white rounded-lg py-3 font-bold text-sm hover:bg-blue-700 transition {{ $plhKurang ? 'opacity-50 cursor-not-allowed' : '' }}">
                                {{ $btnText }}
                            </button>
                            
                            <!-- Tombol Revisi & Tolak (HANYA MUNCUL DI STEP 7) -->
                            @if($step == 7)
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="openModal = true; modalAction = 'revisi'" class="w-full border border-orange-400 text-orange-500 rounded-lg py-2.5 font-bold text-sm hover:bg-orange-50 transition">
                                    Revisi
                                </button>
                                <button type="button" @click="openModal = true; modalAction = 'tolak'" class="w-full border border-red-500 text-red-600 rounded-lg py-2.5 font-bold text-sm hover:bg-red-50 transition">
                                    Tolak
                                </button>
                            </div>
                            @endif
                        </div>

                        <!-- ================= MODAL KONFIRMASI DINAMIS ================= -->
                        <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
                            <div x-show="openModal" 
                                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openModal = false">
                            </div>

                            <div x-show="openModal" 
                                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 mx-4 z-10">
                                
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="text-xl font-bold text-gray-900" x-text="modalAction === 'setujui' ? 'Konfirmasi Persetujuan' : 'Alasan Penolakan/Revisi'"></h3>
                                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                
                                <p class="text-sm text-gray-500 mb-5" x-text="modalAction === 'setujui' ? 'Apakah Anda yakin dokumen pengajuan ini sudah siap diteruskan?' : 'Silakan berikan deskripsi atau alasan detail mengapa pengajuan ini memerlukan revisi atau ditolak.'"></p>

                                <form id="form-verifikasi-admin" action="{{ route('admin.approval.verifikasi', $data->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="action" x-bind:value="modalAction">

                                    <div x-show="modalAction !== 'setujui'">
                                        <textarea name="catatan" rows="4" x-bind:required="modalAction !== 'setujui'"
                                            class="w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-6 bg-gray-50" 
                                            placeholder="Tuliskan catatan Anda di sini..."></textarea>
                                    </div>
                                    
                                    <div class="flex justify-end gap-3 mt-2">
                                        <button type="button" @click="openModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                                        <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition shadow-sm"
                                                x-bind:class="{ 'bg-blue-600 hover:bg-blue-700': modalAction === 'setujui', 'bg-orange-500 hover:bg-orange-600': modalAction === 'revisi', 'bg-red-600 hover:bg-red-700': modalAction === 'tolak' }">
                                            <span x-text="modalAction === 'setujui' ? 'Ya, Teruskan' : 'Kirim Tanggapan'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>