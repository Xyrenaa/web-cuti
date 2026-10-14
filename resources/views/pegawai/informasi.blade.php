<x-app-layout>
    <!-- Header Banner -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Informasi Cuti</div>
        <h1 class="text-white text-3xl font-bold">Informasi & Jatah Cuti</h1>
    </div>

    @php
        // Tahapan persetujuan. Ubah teks di sini kalau prosedur kantor berubah.
        $alur = [
            ['Pegawai Mengajukan', 'Mengisi formulir pengajuan, melampirkan surat pengajuan dan bukti pendukung.'],
            ['Kepala Seksi', 'Memeriksa dan menyetujui pengajuan di tingkat seksi.'],
            ['Kepala Bidang', 'Memberikan persetujuan di tingkat bidang.'],
            ['Verifikasi Admin Kepegawaian', 'Memeriksa kelengkapan berkas dan ketersediaan jatah cuti.'],
            ['Kepala Sub Bagian & Kepala TU', 'Persetujuan lanjutan di tingkat Tata Usaha.'],
            ['Kepala Kantor (Keputusan)', 'Keputusan akhir atas pengajuan cuti.'],
            ['Finalisasi Admin & Terbit Surat', 'Penomoran dan penerbitan surat cuti final.'],
        ];

        $ringkasanCuti = [
            ['Cuti Tahunan', '12 hari kerja/tahun', 'Sudah bekerja ≥ 1 tahun terus-menerus.'],
            ['Cuti Sakit', '1 tahun, dapat diperpanjang 6 bulan', 'Surat keterangan dokter.'],
            ['Cuti Alasan Penting', '1 bulan', 'Keluarga inti sakit keras/meninggal, menikah, istri melahirkan/caesar, atau musibah bencana/kebakaran.'],
            ['Cuti Besar', '3 bulan', 'Sudah bekerja ≥ 5 tahun terus-menerus (kecuali ibadah haji pertama kali). Menghanguskan Cuti Tahunan tahun berjalan.'],
            ['Cuti Melahirkan', '3 bulan', 'Untuk anak ke-1 s/d ke-3. Anak ke-4 dan seterusnya memakai Cuti Besar dengan lama yang sama.'],
        ];
    @endphp

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- KOLOM KIRI: Kuota & Statistik -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Jatah Cuti -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <p class="text-sm font-semibold text-gray-500">Total Sisa Kuota Cuti</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-5xl font-extrabold tracking-tight text-[#2A65F3]">{{ $totalJatah }}</span>
                        <span class="text-base font-medium text-gray-500">hari kerja</span>
                    </div>

                    <dl class="mt-5 divide-y divide-gray-100 text-sm">
                        <div class="flex items-center justify-between py-3">
                            <dt class="text-gray-600">Sisa tahun ini ({{ date('Y') }})</dt>
                            <dd class="font-semibold text-gray-900">{{ $jatahTahunIni }} hari</dd>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <dt class="text-gray-600">Sisa tahun lalu</dt>
                            <dd class="font-semibold text-emerald-600">+ {{ $jatahTahunLalu }} hari</dd>
                        </div>
                    </dl>
                </div>

                <!-- Statistik Pengajuan -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <p class="text-sm font-semibold text-gray-500 mb-4">Statistik Pengajuan</p>
                    <div class="grid grid-cols-3 divide-x divide-gray-100 text-center">
                        <div class="px-2">
                            <div class="text-2xl font-bold text-gray-900">{{ $statistik['total_diajukan'] }}</div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Diajukan</div>
                        </div>
                        <div class="px-2">
                            <div class="text-2xl font-bold text-emerald-600">{{ $statistik['disetujui'] }}</div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Disetujui</div>
                        </div>
                        <div class="px-2">
                            <div class="text-2xl font-bold text-amber-600">{{ $statistik['menunggu'] }}</div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Menunggu</div>
                        </div>
                    </div>
                </div>

                <!-- Satu-satunya aksi utama di halaman ini -->
                <a href="{{ route('pengajuan.index') }}"
                   class="block w-full rounded-lg bg-[#2A65F3] px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-blue-700">
                    + Ajukan Cuti Sekarang
                </a>
            </div>

            <!-- KOLOM KANAN: Panduan -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 pb-6 border-b border-gray-100">Panduan & Tata Cara Cuti</h2>

                    <div class="divide-y divide-gray-100">

                        <!-- 01 Alur Persetujuan -->
                        <section class="py-8">
                            <div class="flex items-baseline gap-3">
                                <span class="text-xs font-bold tracking-widest text-[#2A65F3]">01</span>
                                <h3 class="text-lg font-bold text-gray-900">Alur Persetujuan</h3>
                            </div>

                            <ol class="mt-6">
                                @foreach($alur as [$judul, $keterangan])
                                    <li class="relative pl-10 {{ $loop->last ? '' : 'pb-7' }}">
                                        @unless($loop->last)
                                            <span class="absolute left-3 top-7 bottom-0 w-px -translate-x-1/2 bg-gray-200" aria-hidden="true"></span>
                                        @endunless
                                        <span class="absolute left-0 top-0 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold
                                            {{ $loop->last ? 'bg-[#2A65F3] text-white' : 'border border-gray-300 bg-white text-gray-600' }}">
                                            {{ $loop->iteration }}
                                        </span>
                                        <p class="text-sm font-semibold leading-6 {{ $loop->last ? 'text-[#2A65F3]' : 'text-gray-900' }}">{{ $judul }}</p>
                                        <p class="text-sm text-gray-500">{{ $keterangan }}</p>
                                    </li>
                                @endforeach
                            </ol>

                            <p class="mt-6 border-l-2 border-gray-200 pl-4 text-sm text-gray-500">
                                Pejabat struktural (mis. Kepala Seksi, Kepala Bidang) yang mengajukan cuti sendiri otomatis melewati tahap approval jabatannya sendiri, sesuai posisinya di alur ini.
                            </p>
                        </section>

                        <!-- 02 Syarat & Ketentuan -->
                        <section class="py-8">
                            <div class="flex items-baseline gap-3">
                                <span class="text-xs font-bold tracking-widest text-[#2A65F3]">02</span>
                                <h3 class="text-lg font-bold text-gray-900">Syarat & Ketentuan Umum</h3>
                            </div>

                            <ul class="mt-5 space-y-3 text-sm leading-relaxed text-gray-600">
                                @foreach([
                                    '<strong class="font-semibold text-gray-900">Cuti Tahunan</strong> baru menjadi hak setelah bekerja paling kurang 1 tahun terus-menerus, sebanyak 12 hari kerja per tahun.',
                                    'Sisa <strong class="font-semibold text-gray-900">Cuti Tahunan</strong> tahun lalu yang tidak digunakan dapat dibawa maksimal <strong class="font-semibold text-gray-900">6 hari kerja</strong> ke tahun berikutnya (kecuali ada penangguhan resmi oleh Kepala Kantor, maksimal 12 hari).',
                                    'Setiap pengajuan <strong class="font-semibold text-gray-900">Cuti Sakit</strong> di sistem ini wajib melampirkan surat keterangan dokter, sesuai kolom lampiran pada form.',
                                    '<strong class="font-semibold text-gray-900">Cuti Karena Alasan Penting</strong> dan <strong class="font-semibold text-gray-900">Cuti Melahirkan</strong> tidak mengurangi jatah Cuti Tahunan Anda.',
                                    '<strong class="font-semibold text-gray-900">Cuti Besar</strong> menghanguskan hak Cuti Tahunan pada tahun cuti besar tersebut diambil.',
                                    'Pegawai berstatus PPPK hanya dapat mengajukan Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, dan Cuti Bersama.',
                                ] as $poin)
                                    <li class="flex gap-3">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[#2A65F3]"></span>
                                        <span>{!! $poin !!}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>

                        <!-- 03 Ringkasan Jenis Cuti -->
                        <section class="pt-8">
                            <div class="flex items-baseline gap-3">
                                <span class="text-xs font-bold tracking-widest text-[#2A65F3]">03</span>
                                <h3 class="text-lg font-bold text-gray-900">Ringkasan Jenis Cuti</h3>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                                            <th class="py-3 pr-4 font-semibold">Jenis Cuti</th>
                                            <th class="py-3 pr-4 font-semibold">Lama Maksimal</th>
                                            <th class="py-3 font-semibold">Syarat Utama</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-700">
                                        @foreach($ringkasanCuti as [$jenis, $lama, $syarat])
                                            <tr>
                                                <td class="py-3 pr-4 align-top font-semibold text-gray-900 whitespace-nowrap">{{ $jenis }}</td>
                                                <td class="py-3 pr-4 align-top">{{ $lama }}</td>
                                                <td class="py-3 align-top text-gray-600">{{ $syarat }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-4 text-xs text-gray-400">Ringkasan berdasarkan Peraturan BKN Nomor 7 Tahun 2021. Hubungi bagian kepegawaian untuk kasus khusus.</p>
                        </section>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>