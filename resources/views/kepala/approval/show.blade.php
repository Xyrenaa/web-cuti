<x-app-layout>
    
    <!-- 1. BANNER BIRU FULL-WIDTH -->
    <div class="w-full pt-6 pb-8 bg-[#2A65F3]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-1 text-sm font-medium text-blue-100/80">
                <a href="{{ route('dashboard') }}" class="transition hover:text-white">Beranda</a> / 
                <a href="{{ route('kepala.approval.index') }}" class="transition hover:text-white">Approval Cuti</a> / 
                <span class="text-white">Detail Pengajuan</span>
            </div>
            <!-- Judul Halaman -->
            <h2 class="text-3xl font-bold text-white">Detail Pengajuan Cuti</h2>
        </div>
    </div>

    <!-- 2. AREA KONTEN UTAMA (Card Rincian, Alur, dll) -->
    <div class="py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            
            <!-- KIRI: Informasi Detail -->
            <div class="space-y-6 lg:col-span-2">
                
                <!-- Card: Rincian Cuti -->
                <div class="p-7 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <h3 class="mb-6 text-lg font-bold text-gray-900">Rincian Cuti</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Nomor pengajuan</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">CT-2026-000148</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tanggal pengajuan</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Minggu, 15 Maret 2026, 09:12</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Jenis Cuti</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Cuti Tahunan</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Durasi</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">3 Hari</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Senin, 16 Maret 2026</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Selesai</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Rabu, 18 Maret 2026</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Aktif Kembali</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Kamis, 19 Maret 2026</dd>
                        </div>
                    </div>
                </div>

                <!-- Card: Keterangan -->
                <div class="p-7 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <h3 class="mb-6 text-lg font-bold text-gray-900">Keterangan</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                            <dd class="text-sm font-bold leading-relaxed text-gray-900 md:col-span-2">Menghadiri acara pernikahan adik kandung di luar kota.</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Alamat Selama Cuti</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Jl. Nameless No. 7 Surabaya</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Kontak Darurat</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">082133550336 (Ibu Rahma)</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 md:grid-cols-3 md:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Pengganti Tugas</dt>
                            <dd class="text-sm font-bold text-gray-900 md:col-span-2">Liino (NIP: 199105142017041002)</dd>
                        </div>
                    </div>
                </div>

                <!-- Card: Lampiran -->
                <div class="p-7 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">Lampiran</h3>
                    <div class="flex items-center justify-between p-4 transition border border-gray-200 bg-gray-50/50 hover:bg-gray-50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-bold text-gray-800">surat-undangan-keluarga.pdf</span>
                        </div>
                        <a href="#" class="text-sm font-bold text-[#2a64f5] hover:text-blue-800 transition">Unduh File</a>
                    </div>
                </div>
            </div>

            <!-- KANAN: Timeline & Aksi -->
            <div class="lg:col-span-1">
                <!-- Card Alur Persetujuan -->
                <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <h3 class="pb-3 mb-4 text-lg font-bold text-gray-800 border-b border-gray-100">Alur Persetujuan</h3>
                    
                    <!-- Timeline Vertikal -->
                    <div class="relative pb-2 mt-4 ml-3 space-y-6 border-l-2 border-gray-200">
                        
                        <!-- Step 0: Dikirim -->
                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full bg-[#2A65F3] ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">Pengajuan Dikirim</h3>
                            <p class="mt-1 text-xs text-gray-500">Oleh: {{ $data->user->name ?? 'Pegawai' }}</p>
                        </div>
                        
                        <!-- Step 1: Kepala Seksi -->
                        @hasanyrole(['Kepala Seksi', 'Kepala Sub-Bagian', 'Kepala Bagian', 'Kepala Kantor'])
                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 1 ? 'bg-[#2A65F3]' : 'bg-yellow-400' }} ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Seksi</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 1 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
                        </div>
                        @endhasanyrole

                        <!-- Step 2: Kepala Sub-Bagian -->
                        @hasanyrole(['Kepala Sub-Bagian', 'Kepala Bagian', 'Kepala Kantor'])
                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 2 ? 'bg-[#2A65F3]' : 'bg-yellow-400' }} ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Sub-Bagian</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 2 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
                        </div>
                        @endhasanyrole

                        <!-- Step 3: Kepala Bagian -->
                        @hasanyrole(['Kepala Bagian', 'Kepala Kantor'])
                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 3 ? 'bg-[#2A65F3]' : 'bg-yellow-400' }} ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Bagian</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 3 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
                        </div>
                        @endhasanyrole

                        <!-- Step 4: Kepala Kantor -->
                        @hasanyrole(['Kepala Kantor'])
                        <div class="relative pl-6">
                            <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full {{ $data->approval_step > 4 ? 'bg-[#2A65F3]' : 'bg-gray-200' }} ring-4 ring-white"></span>
                            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Kepala Kantor</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ $data->approval_step > 4 ? 'Telah Disetujui' : 'Menunggu Tindakan Anda' }}</p>
                        </div>
                        @endhasanyrole 
                    </div>

                    <!-- ========================================== -->
                    <!-- AREA TOMBOL AKSI DENGAN SWEETALERT         -->
                    <!-- ========================================== -->
                    <div class="flex flex-col gap-3 pt-6 mt-8 border-t border-gray-100">
                        
                        <!-- Tombol Setujui -->
                        <form id="form-approve" action="{{ route('kepala.approval.approve', $data->id) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <button type="button" onclick="konfirmasiAksi('approve')" class="w-full px-6 py-2.5 text-sm font-semibold text-white transition bg-[#2A65F3] rounded-lg shadow-sm hover:bg-blue-700">
                                Setujui & Teruskan
                            </button>
                        </form>

                        <!-- Tombol Revisi -->
                        <form id="form-revisi" action="{{ route('kepala.approval.revisi', $data->id) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <button type="button" onclick="konfirmasiAksi('revisi')" class="w-full px-6 py-2.5 text-sm font-semibold text-yellow-700 transition bg-white border border-yellow-300 rounded-lg shadow-sm hover:bg-yellow-50 hover:border-yellow-400">
                                Kembalikan (Revisi)
                            </button>
                        </form>

                        <!-- Tombol Tolak Permanen -->
                        <form id="form-reject" action="{{ route('kepala.approval.reject', $data->id) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <button type="button" onclick="konfirmasiAksi('reject')" class="w-full px-6 py-2.5 text-sm font-semibold text-red-600 transition bg-white border border-red-200 rounded-lg hover:bg-red-50 hover:border-red-300">
                                Tolak Permanen
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan CDN SweetAlert2 dan Script Logikanya di Sini -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiAksi(jenis) {
            let config = {};

            if (jenis === 'approve') {
                config = {
                    title: "Setujui Pengajuan?",
                    text: "Berkas akan diteruskan ke tahap selanjutnya.",
                    icon: "question",
                    confirmButtonText: "Ya, Setujui",
                    confirmButtonColor: "#2A65F3", // Warna biru PELITA
                };
            } else if (jenis === 'revisi') {
                config = {
                    title: "Kembalikan Berkas?",
                    text: "Pegawai harus merevisi dokumen ini.",
                    icon: "warning",
                    confirmButtonText: "Ya, Kembalikan",
                    confirmButtonColor: "#eab308", // Warna kuning
                };
            } else if (jenis === 'reject') {
                config = {
                    title: "Tolak Permanen?",
                    text: "Pengajuan ini tidak dapat diproses lagi.",
                    icon: "error",
                    confirmButtonText: "Ya, Tolak",
                    confirmButtonColor: "#dc2626", // Warna merah
                };
            }

            Swal.fire({
                ...config,
                showCancelButton: true,
                cancelButtonText: "Batal",
                cancelButtonColor: "#6b7280",
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-gray-100',
                    title: 'text-xl font-bold text-gray-800',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik Ya, jalankan submit form sesuai ID
                    document.getElementById('form-' + jenis).submit();
                }
            });
        }
    </script>
</x-app-layout>