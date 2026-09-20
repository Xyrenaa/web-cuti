<x-app-layout>
    <!-- Pita Biru Header -->
    <div class="bg-[#2A65F3] pt-8 pb-10 px-4 sm:px-6 lg:px-8 shadow-sm">
        <div class="max-w-7xl mx-auto">
            <p class="text-blue-100 text-sm font-medium mb-1">Sistem Layanan Cuti (PELITA)</p>
            <h1 class="text-3xl font-bold text-white">Beranda</h1>
        </div>
    </div>

    <!-- Area Konten Utama -->
    <div class="relative py-8 min-h-screen bg-gray-50/50">
        
        <!-- Watermark Logo Otban Samar di Background -->
        <div class="absolute inset-0 flex justify-center items-center z-0 pointer-events-none overflow-hidden">
            <img src="{{ asset('img/Logo Otban.png') }}" class="w-[500px] opacity-[0.03]" alt="Watermark">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Kotak Selamat Datang -->
            <div class="bg-[#F4F7FF] rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border border-blue-100 shadow-sm">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Selamat Datang Kembali</h2>
                    <p class="text-gray-500 mt-1 text-sm">Kelola pengajuan cuti pegawai Kantor Otoritas Bandar Udara Wilayah III Juanda secara real-time.</p>
                </div>
    
                <!-- Bagian Waktu Real-time -->
                <div class="mt-4 md:mt-0 text-right" 
                     x-data="{ 
                     jam: '{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H') }}',
                     menit: '{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('i') }}',
                     detik: '{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('s') }}',
                     updateTime() {
                         const now = new Date();
                         this.jam = String(now.getHours()).padStart(2, '0');
                         this.menit = String(now.getMinutes()).padStart(2, '0');
                         this.detik = String(now.getSeconds()).padStart(2, '0');
                     }
                 }" 
                 x-init="setInterval(() => updateTime(), 1000)">
                
                    <p class="text-xs font-bold text-[#2A65F3] uppercase tracking-wider mb-1">Waktu Saat Ini</p>
                    <div class="flex items-baseline justify-end gap-1">
                        <div class="text-xl font-bold text-gray-800">
                            <span x-text="jam"></span><span class="mx-0.5">:</span><span x-text="menit"></span>
                        </div>
                        <div class="text-xs font-semibold text-gray-400">
                            <span class="mr-0.5">:</span><span x-text="detik"></span>
                        </div>
                        <span class="text-xs font-bold text-gray-600 ml-1">WIB</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: STATISTIK 4 KARTU (Sleek Enterprise Style) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-8 overflow-hidden">
                <!-- Grid khusus untuk 4 kartu pembatas garis -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y md:divide-y-0 lg:divide-x divide-gray-200">
                    
                    <div onclick="openDrillDownModal()" class="p-6 cursor-pointer hover:bg-gray-50 transition-colors">
                        <h3 class="text-gray-500 text-sm font-medium">Pengajuan Menunggu</h3>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $countMenunggu }}</p>
                        <p class="text-xs text-gray-400 mt-1">Klik untuk melihat sebaran divisi</p>
                    </div>

                    <div class="p-6">
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Disetujui Bulan Ini</h3>
                        <div class="flex items-end gap-2 mt-2">
                            <p class="text-3xl font-bold text-gray-800">{{ $countDisetujui }}</p>
                            <p class="text-sm text-green-500 font-medium mb-1">✓ Tuntas</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Ditolak / Revisi</h3>
                        <div class="flex items-end gap-2 mt-2">
                            <p class="text-3xl font-bold text-gray-800">{{ $countDitolak }}</p>
                            <p class="text-sm text-gray-400 font-medium mb-1">Berkas</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-blue-500 text-sm font-medium uppercase tracking-wider">Total Pegawai Aktif</h3>
                        <div class="flex items-end gap-2 mt-2">
                            <p class="text-3xl font-bold text-blue-600">{{ $totalPegawai }}</p>
                            <p class="text-sm text-blue-400 font-medium mb-1">Personel</p>
                        </div>
                    </div>

                </div>
            </div> <!-- PENUTUP GRID 4 KARTU -->

            <!-- SECTION 2: EXECUTIVE ANALYTICS -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Analisis Strategis & Tren</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="text-md font-medium text-gray-700 mb-2">Tren Pengajuan Cuti Bulanan</h3>
                        <p class="text-xs text-gray-500 mb-4">Visualisasi peak season cuti pegawai</p>
                        <div class="relative h-72 w-full">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    @if($levelKepala === 'seksi' && $risikoRingkas)
                    <!-- MODE KEPALA SEKSI/SUB-BAGIAN: cuma satu angka ringkasan, tidak ada chart -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col">
                        <h3 class="text-md font-medium text-gray-700 mb-1">Risiko Kekosongan Seksi/Sub-Bagian Anda</h3>
                        <p class="text-xs text-gray-500 mb-6">Deteksi dini staf cuti &gt; 20%</p>
                        <div class="flex-grow flex flex-col items-center justify-center gap-2 py-6">
                            <p class="text-5xl font-bold {{ $risikoRingkas['status_bahaya'] ? 'text-red-600' : 'text-blue-600' }}">{{ $risikoRingkas['persentase'] }}%</p>
                            <p class="text-sm text-gray-500">{{ $risikoRingkas['sedang_cuti'] }} dari {{ $risikoRingkas['total_pegawai'] }} pegawai sedang cuti</p>
                            <span class="mt-2 px-3 py-1 text-xs font-bold rounded-full {{ $risikoRingkas['status_bahaya'] ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-green-100 text-green-700' }}">
                                {{ $risikoRingkas['status_bahaya'] ? 'Bahaya!' : 'Aman' }}
                            </span>
                        </div>
                    </div>
                    @else
                    <!-- MODE KEPALA KANTOR (semua Bagian/Bidang, bisa di-klik) & KEPALA BIDANG/BAGIAN (sub-unit sendiri) -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col">
                        <h3 class="text-md font-medium text-gray-700 mb-2">Risiko Kekosongan per Divisi</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Deteksi dini divisi dengan staf cuti &gt; 20%
                            @if($levelKepala === 'kantor')
                                &middot; <span class="text-[#2A65F3] font-medium">klik salah satu bagian untuk lihat rincian sub-unit</span>
                            @endif
                        </p>
                        <div class="relative h-64 w-full flex-grow">
                            <canvas id="riskChart"></canvas>
                        </div>
                        <div class="mt-4 space-y-2 max-h-32 overflow-y-auto pr-2">
                            @forelse($risikoDivisi as $i => $risiko)
                                <div
                                    @if($levelKepala === 'kantor' && !empty($risiko['rincian']))
                                        onclick='openRincianModal(@json($risiko["nama_divisi"]), @json($risiko["rincian"]))'
                                        class="flex justify-between items-center text-sm cursor-pointer hover:bg-gray-50 rounded {{ $risiko['status_bahaya'] ? 'border-l-4 border-red-500 pl-2 bg-red-50 py-1' : '' }}"
                                    @else
                                        class="flex justify-between items-center text-sm {{ $risiko['status_bahaya'] ? 'border-l-4 border-red-500 pl-2 bg-red-50 py-1' : '' }}"
                                    @endif
                                >
                                    <span class="{{ $risiko['status_bahaya'] ? 'font-semibold text-red-700' : 'text-gray-700' }}">
                                        {{ $risiko['nama_divisi'] }} — {{ $risiko['sedang_cuti'] }}/{{ $risiko['total_pegawai'] }} pegawai ({{ $risiko['persentase'] }}%)
                                    </span>
                                    @if($risiko['status_bahaya'])
                                        <span class="px-2 py-1 bg-red-600 text-white rounded-full text-[10px] animate-pulse">Bahaya!</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px]">Aman</span>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 text-center">Belum ada data divisi.</div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- SECTION 3: TABEL PERSETUJUAN TERKINI (Desain Dummy Diubah Dinamis) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Persetujuan Terkini Menunggu Tindakan</h3>
                   <a href="{{ route('kepala.approval.index') }}" class="text-sm font-semibold text-[#2A65F3] hover:text-blue-800 flex items-center gap-1">
                        Lihat Semua <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 font-bold bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4">NAMA PEGAWAI</th>
                                <th class="px-6 py-4">JENIS CUTI</th>
                                <th class="px-6 py-4">TANGGAL PENGAJUAN</th>
                                <th class="px-6 py-4 text-center">DURASI</th>
                                <th class="px-6 py-4 text-center">STATUS</th>
                                <th class="px-6 py-4 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            
                            @forelse($pengajuanTerbaru as $pengajuan)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">{{ $pengajuan->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $pengajuan->user->bagianBidang->nama ?? $pengajuan->user->bagianBidang->nama_bagian ?? 'Divisi Tidak Diketahui' }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $pengajuan->jenisCuti->nama_cuti ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->translatedFormat('l, d F Y') }}</td>
                                <td class="px-6 py-4 text-center text-gray-600">{{ $pengajuan->durasi_hari }} Hari</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">{{ $pengajuan->status_label ?? 'Menunggu' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('kepala.approval.show', $pengajuan->id) }}" class="font-bold text-[#2A65F3] hover:text-blue-800">Tinjau Berkas</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada pengajuan baru yang menunggu tindakan.</td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer Section -->
    <footer class="w-full bg-gray-200 py-5">
        <div class="text-center">
            <p class="text-sm font-medium text-gray-600">
                Kantor Otoritas Bandar Udara Wilayah III Juanda
            </p>
        </div>
    </footer>

    <!-- MODAL DRILL-DOWN SEBARAN DIVISI -->
    <div id="drillDownModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
            <button onclick="closeDrillDownModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <h3 class="text-lg font-bold text-gray-800 mb-4">Sebaran Pengajuan Menunggu</h3>
            
            <ul class="divide-y divide-gray-100">
                @forelse($menungguPerDivisi as $divisi => $jumlah)
                <li class="py-3 flex justify-between items-center">
                    <span class="text-gray-700 font-medium">{{ $divisi }}</span>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">{{ $jumlah }} Berkas</span>
                </li>
                @empty
                <li class="py-4 text-center text-gray-500 text-sm">Tidak ada data.</li>
                @endforelse
            </ul>
            
            <div class="mt-6 text-right">
                <button onclick="closeDrillDownModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL RINCIAN RISIKO (klik slice/baris di chart Risiko Kekosongan, khusus Kepala Kantor) -->
    <div id="rincianDivisiModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
            <button onclick="closeRincianModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <h3 id="rincianDivisiTitle" class="text-lg font-bold text-gray-800 mb-1">Rincian Divisi</h3>
            <p class="text-xs text-gray-500 mb-4">Sebaran pegawai yang sedang cuti per sub-unit</p>

            <ul id="rincianDivisiList" class="divide-y divide-gray-100 max-h-80 overflow-y-auto"></ul>

            <div class="mt-6 text-right">
                <button onclick="closeRincianModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- FUNGSI MODAL DRILL-DOWN ---
        function openDrillDownModal() {
            document.getElementById('drillDownModal').classList.remove('hidden');
        }

        function closeDrillDownModal() {
            document.getElementById('drillDownModal').classList.add('hidden');
        }

        // --- FUNGSI MODAL RINCIAN RISIKO (klik slice chart Risiko Kekosongan) ---
        function openRincianModal(namaDivisi, rincian) {
            document.getElementById('rincianDivisiTitle').textContent = 'Rincian ' + namaDivisi;
            const list = document.getElementById('rincianDivisiList');

            if (!rincian || rincian.length === 0) {
                list.innerHTML = '<li class="py-4 text-center text-gray-500 text-sm">Tidak ada sub-unit dengan pegawai aktif.</li>';
            } else {
                list.innerHTML = rincian.map(r => `
                    <li class="py-3 flex justify-between items-center gap-3">
                        <div>
                            <span class="text-gray-800 font-medium block">${r.nama}</span>
                            <span class="text-xs text-gray-400">${r.sedang_cuti} dari ${r.total_pegawai} pegawai sedang cuti</span>
                        </div>
                        <span class="flex-shrink-0 px-3 py-1 text-xs font-bold rounded-full ${r.status_bahaya ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}">
                            ${r.persentase}%
                        </span>
                    </li>
                `).join('');
            }

            document.getElementById('rincianDivisiModal').classList.remove('hidden');
        }

        function closeRincianModal() {
            document.getElementById('rincianDivisiModal').classList.add('hidden');
        }

        // --- INISIALISASI GRAFIK CHART.JS ---
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. GRAFIK TREN BULANAN (Sisi Kiri)
            const trendCtx = document.getElementById('trendChart');
            if(trendCtx) {
                const trenData = @json($trenBulanan); 
                
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                        datasets: [{
                            label: 'Total Disetujui',
                            data: trenData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });
            }

            // 2. GRAFIK RISIKO KEKOSONGAN (Sisi Kanan)
            const riskCtx = document.getElementById('riskChart');
            if(riskCtx) {
                const risikoDataRaw = @json($risikoDivisi);
                const levelKepala = @json($levelKepala);

                const labelsDivisi = risikoDataRaw.map(item => item.nama_divisi);
                const dataPersentase = risikoDataRaw.map(item => item.persentase);

                // Palet warna beda per bagian/bidang; merah HANYA dipakai khusus status bahaya
                const palet = [
                    'rgba(59, 130, 246, 0.85)',   // biru
                    'rgba(16, 185, 129, 0.85)',   // hijau
                    'rgba(245, 158, 11, 0.85)',   // amber
                    'rgba(139, 92, 246, 0.85)',   // ungu
                    'rgba(236, 72, 153, 0.85)',   // pink
                    'rgba(20, 184, 166, 0.85)',   // teal
                    'rgba(99, 102, 241, 0.85)',   // indigo
                    'rgba(234, 179, 8, 0.85)',    // kuning
                ];
                const backgroundColors = risikoDataRaw.map((item, idx) =>
                    item.status_bahaya ? 'rgba(239, 68, 68, 0.9)' : palet[idx % palet.length]
                );

                new Chart(riskCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labelsDivisi,
                        datasets: [{
                            data: dataPersentase,
                            backgroundColor: backgroundColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right' },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const item = risikoDataRaw[context.dataIndex];
                                        return `${item.nama_divisi}: ${item.sedang_cuti}/${item.total_pegawai} pegawai (${item.persentase}%)`;
                                    }
                                }
                            }
                        },
                        onClick: (evt, elements) => {
                            // Drill-down cuma untuk Kepala Kantor; level lain sudah di titik sub-unit terkecil
                            if (elements.length > 0 && levelKepala === 'kantor') {
                                const item = risikoDataRaw[elements[0].index];
                                openRincianModal(item.nama_divisi, item.rincian);
                            }
                        },
                        onHover: (evt, elements) => {
                            evt.native.target.style.cursor = (elements.length > 0 && levelKepala === 'kantor') ? 'pointer' : 'default';
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>