<x-app-layout>
    <style>
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .reveal {
            opacity: 0;
            animation: fadeSlideUp .7s cubic-bezier(.16,1,.3,1) forwards;
        }
        .card-lift {
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .card-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px -12px rgba(42,101,243,.28);
        }
        .stat-cell {
            transition: background-color .3s ease;
        }
        .stat-cell:hover {
            background-color: rgba(42,101,243,.04);
        }
        .modal-backdrop, .modal-panel {
            transition: opacity .25s ease, transform .25s ease;
        }
        /* ===== Grafik Tren Cuti Bulanan (batang interaktif) ===== */
        /* Area batang MELAR mengikuti tinggi kartu (kartu kiri & kanan satu baris grid, tingginya disamakan),
           jadi batang selalu pas mengisi kotak di semua role: Kepala Seksi/Sub-Bagian, Bidang/TU, maupun Kepala Kantor.
           Tinggi batang dihitung sebagai persen (--p) dari area, bukan piksel tetap. */
        .trend-wrap { display:flex; flex-direction:column; flex:1 1 auto; min-height:0; }
        .trend-bars { display:flex; align-items:stretch; gap:6px; flex:1 1 auto; min-height:200px; margin-top:8px; }
        .trend-col { flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; font-size:13px; color:#6b7280; cursor:pointer; border-radius:8px; }
        .trend-plot { position:relative; flex:1 1 0; width:100%; min-height:0; margin-top:22px; } /* 22px = ruang angka di atas batang tertinggi */
        .trend-col:focus-visible { outline:3px solid #2A65F3; outline-offset:2px; }
        .trend-col.is-now { color:#1f2937; font-weight:700; }
        .trend-col b { position:absolute; left:0; right:0; bottom:calc(var(--p) * 1% + 6px); text-align:center; color:#1f2937; font-size:14px; opacity:0; transition:opacity .4s ease; }
        .trend-col.is-go b { opacity:1; transition-delay:calc(var(--i) * 50ms + .5s); }
        .trend-bar { position:absolute; left:0; right:0; bottom:0; background:#BFD3FF; border-radius:6px 6px 0 0; height:calc(var(--p) * 1%); min-height:3px; transform:scaleY(0); transform-origin:bottom; transition:background-color .2s ease, box-shadow .2s ease; }
        .trend-col.is-go .trend-bar { animation:trendGrow .7s cubic-bezier(.16,1,.3,1) forwards; animation-delay:calc(var(--i) * 50ms); }
        .trend-col:hover .trend-bar { background:#1E4FCC; }
        .trend-col.is-on .trend-bar { background:#2A65F3; box-shadow:0 -8px 18px -8px rgba(42,101,243,.65); }
        @keyframes trendGrow { to { transform:scaleY(1); } }
        @media (prefers-reduced-motion: reduce) {
            .trend-col.is-go .trend-bar { animation-duration:.01ms; }
            .trend-col b { transition:none; }
        }
        /* --- Dropdown rincian pegawai di bawah batang tren (klik batang untuk membuka) --- */
        .trend-col.is-open .trend-bar { background:#1E4FCC; }
        .trend-col.is-open span { color:#1E4FCC; font-weight:700; text-decoration:underline; text-underline-offset:3px; }
        .trend-drop { display:grid; grid-template-rows:0fr; transition:grid-template-rows .3s ease, margin-top .3s ease; margin-top:0; }
        .trend-drop.is-open { grid-template-rows:1fr; margin-top:14px; }
        .trend-drop-inner { overflow:hidden; min-height:0; }
        .trend-drop-card { position:relative; margin-top:8px; background:#F4F7FF; border:1px solid #D5E1FF; border-radius:14px; padding:14px 16px; }
        .trend-drop-card::before { content:''; position:absolute; top:-7px; left:var(--caret-x, 50%); width:12px; height:12px; background:#F4F7FF; border-left:1px solid #D5E1FF; border-top:1px solid #D5E1FF; transform:translateX(-50%) rotate(45deg); transition:left .25s ease; }
        .trend-list { max-height:260px; overflow-y:auto; list-style:none; margin:0; padding:0; }
        .trend-list li { display:flex; align-items:center; gap:10px; padding:9px 2px; border-top:1px solid #E1EAFF; }
        .trend-list li:first-child { border-top:0; }
        .trend-avatar { width:32px; height:32px; border-radius:9999px; background:#DCE7FF; color:#1E4FCC; font-weight:700; font-size:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .trend-info { flex:1; min-width:0; }
        .trend-info p { margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .trend-tutup { width:28px; height:28px; border-radius:8px; border:1px solid #D5E1FF; background:#fff; color:#6b7280; font-size:18px; line-height:1; cursor:pointer; flex-shrink:0; }
        .trend-tutup:hover { color:#1E4FCC; border-color:#2A65F3; }
        @media (prefers-reduced-motion: reduce) {
            .trend-drop, .trend-drop-card::before { transition:none; }
        }

        /* ===== Kartu Risiko Seksi/Sub-Bagian (meteran busur) ===== */
        .gauge-chip { opacity:0; transform:translateY(6px); animation:gaugeChipIn .35s ease forwards; }
        @keyframes gaugeChipIn { to { opacity:1; transform:none; } }
        @media (prefers-reduced-motion: reduce) {
            .gauge-chip { animation-duration:.01ms; }
        }
    </style>

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
            <div class="reveal bg-[#F4F7FF] rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border border-blue-100 shadow-sm">
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
                
                    <p class="text-xs font-bold text-[#2A65F3] uppercase tracking-wider mb-1 flex items-center justify-end gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Waktu Saat Ini
                    </p>
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
            <div class="reveal bg-white rounded-2xl border border-gray-200 shadow-sm mb-8 overflow-hidden" style="animation-delay: .08s">
                <!-- Grid khusus untuk 4 kartu pembatas garis -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y md:divide-y-0 lg:divide-x divide-gray-200">
                    
                    <div onclick="openDrillDownModal()" class="stat-cell p-6 cursor-pointer">
                        <h3 class="text-gray-500 text-sm font-medium">Pengajuan Menunggu</h3>
                        <p class="text-3xl font-bold text-blue-600 mt-2" data-count-up="{{ $countMenunggu }}">0</p>
                        <p class="text-xs text-gray-400 mt-1">Klik untuk melihat sebaran divisi</p>
                    </div>

                    <div class="stat-cell p-6">
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Disetujui</h3>
                        <div class="flex items-end gap-2 mt-2">
                            <p class="text-3xl font-bold text-gray-800" data-count-up="{{ $countDisetujui }}">0</p>
                            <p class="text-sm text-green-500 font-medium mb-1">✓ Tuntas</p>
                        </div>
                    </div>

                    <div class="stat-cell p-6">
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Ditolak / Revisi</h3>
                        <div class="flex items-end gap-2 mt-2">
                            <p class="text-3xl font-bold text-gray-800" data-count-up="{{ $countDitolak }}">0</p>
                            <p class="text-sm text-gray-400 font-medium mb-1">Berkas</p>
                        </div>
                    </div>

                    <div class="stat-cell p-6">
                        <h3 class="text-blue-500 text-sm font-medium uppercase tracking-wider">
                            {{ match($levelKepala) {
                                'kantor' => 'Total Pegawai Aktif',
                                'bidang' => 'Pegawai di Bidang/Bagian Anda',
                                'seksi' => 'Pegawai di Seksi/Sub-Bagian Anda',
                                default => 'Total Pegawai Aktif',
                            } }}
                        </h3>
                        <div class="flex items-end gap-2 mt-2">
                            <p class="text-3xl font-bold text-blue-600" data-count-up="{{ $totalPegawai }}">0</p>
                            <p class="text-sm text-blue-400 font-medium mb-1">Personel</p>
                        </div>
                    </div>

                </div>
            </div> <!-- PENUTUP GRID 4 KARTU -->

            <!-- SECTION 2: EXECUTIVE ANALYTICS -->
            <div class="mb-8">
                <h2 class="reveal text-lg font-semibold text-gray-800 mb-4" style="animation-delay: .14s">Analisis Strategis & Tren</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" data-trend-grid>
                    
                    <div class="reveal card-lift bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col" style="animation-delay: .18s">
                        <h3 class="text-md font-medium text-gray-700 mb-2">Tren Pengajuan Cuti Bulanan</h3>
                        @php
                            $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                            $namaBulanPenuh = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            $maxTren = max($trenBulanan) ?: 1;
                            $adaDataTren = array_sum($trenBulanan) > 0;
                            $bulanIni = now()->month;
                            $puncakTren = array_search(max($trenBulanan), $trenBulanan);
                        @endphp

                        @if($adaDataTren)
                        <div class="trend-wrap" data-trend data-bulan-ini="{{ $bulanIni }}" data-puncak="{{ $puncakTren }}">
                            <p class="text-sm text-gray-500">
                                Puncak cuti tahun ini di bulan <span class="font-semibold text-gray-700">{{ $namaBulanPenuh[$puncakTren] }}</span>
                                ({{ $trenBulanan[$puncakTren] }} cuti disetujui). Arahkan kursor untuk melihat angkanya, <span class="font-semibold text-[#2A65F3]">klik batang</span> untuk melihat nama pegawai yang cuti.
                            </p>
                            <p class="trend-readout text-lg text-gray-800 min-h-[28px] mt-2" aria-live="polite"></p>

                            <div class="trend-bars">
                                @foreach($trenBulanan as $i => $jumlah)
                                    <div class="trend-col {{ ($i + 1) === $bulanIni ? 'is-now' : '' }}" tabindex="0"
                                         role="button" aria-expanded="false" aria-controls="trend-panel-{{ $i }}"
                                         data-i="{{ $i }}" data-nama="{{ $namaBulanPenuh[$i] }}" data-jumlah="{{ $jumlah }}"
                                         style="--i: {{ $i }}; --p: {{ round($jumlah / $maxTren * 100) }}"
                                         aria-label="{{ $namaBulanPenuh[$i] }}: {{ $jumlah }} cuti disetujui">
                                        <div class="trend-plot">
                                            <b>{{ $jumlah }}</b>
                                            <div class="trend-bar"></div>
                                        </div>
                                        <span>{{ $namaBulan[$i] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- DROPDOWN: nama pegawai yang cuti pada bulan yang batangnya diklik --}}
                            <div class="trend-drop" data-trend-drop>
                                <div class="trend-drop-inner">
                                    <div class="trend-drop-card">
                                        @foreach($detailTren as $i => $rows)
                                            <section id="trend-panel-{{ $i }}" data-panel="{{ $i }}" hidden
                                                     aria-label="Pegawai yang cuti pada {{ $namaBulanPenuh[$i] }}">
                                                <div class="flex items-start justify-between gap-3 mb-2">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">{{ $namaBulanPenuh[$i] }} {{ now()->year }}</p>
                                                        <p class="text-xs text-gray-500">{{ count($rows) }} cuti disetujui</p>
                                                    </div>
                                                    <button type="button" class="trend-tutup" data-trend-tutup aria-label="Tutup daftar">&times;</button>
                                                </div>

                                                @if(count($rows))
                                                    <ul class="trend-list">
                                                        @foreach($rows as $r)
                                                            <li>
                                                                <span class="trend-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($r['nama'], 0, 1)) }}</span>
                                                                <div class="trend-info">
                                                                    <p class="text-sm font-medium text-gray-800">{{ $r['nama'] }}</p>
                                                                    <p class="text-xs text-gray-500">@if($levelKepala === 'kantor' && $r['unit']){{ $r['unit'] }} &middot; @endif{{ $r['jenis'] }}</p>
                                                                </div>
                                                                <div class="text-right shrink-0">
                                                                    <p class="text-xs font-medium text-gray-700">{{ $r['mulai'] }}@if($r['mulai'] !== $r['selesai']) &ndash; {{ $r['selesai'] }}@endif</p>
                                                                    <p class="text-xs text-gray-400">{{ $r['hari'] }} hari</p>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-sm text-gray-500 py-3">Tidak ada cuti yang disetujui pada bulan ini.</p>
                                                @endif
                                            </section>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="h-48 flex items-center justify-center text-sm text-gray-500 text-center">
                            Belum ada cuti yang disetujui tahun ini.
                        </div>
                        @endif
                    </div>

                    @if($levelKepala === 'seksi' && $risikoRingkas)
                    <!-- MODE KEPALA SEKSI/SUB-BAGIAN: meteran busur + daftar pegawai yang cuti sebagai chip -->
                    <div class="reveal card-lift bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col" style="animation-delay: .22s">
                        <h3 class="text-md font-medium text-gray-700 mb-1">Risiko Kekosongan</h3>
                        <p class="text-xs text-gray-500 mb-2">Deteksi dini staf cuti &gt; 50%</p>

                        <div class="flex flex-col items-center gap-1 py-4">
                            <svg viewBox="0 0 220 130" class="w-full max-w-[220px]">
                                <path d="M20 110 A90 90 0 0 1 200 110" fill="none" stroke="#E5E7EB" stroke-width="16" stroke-linecap="round" />
                                <path id="gaugeArcSeksi" d="M20 110 A90 90 0 0 1 200 110" fill="none"
                                    stroke="{{ $risikoRingkas['status_bahaya'] ? '#DC2626' : '#2563EB' }}"
                                    stroke-width="16" stroke-linecap="round"
                                    style="stroke-dasharray:282.74; stroke-dashoffset:282.74; transition: stroke-dashoffset 1s cubic-bezier(.2,.8,.2,1);" />
                                <text x="110" y="95" text-anchor="middle" font-weight="800" font-size="30"
                                    class="{{ $risikoRingkas['status_bahaya'] ? 'text-red-600' : 'text-blue-600' }}"
                                    fill="currentColor" data-count-up="{{ $risikoRingkas['persentase'] }}" data-suffix="%">0%</text>
                            </svg>
                            <p class="text-sm text-gray-500 -mt-1">{{ $risikoRingkas['sedang_cuti'] }} dari {{ $risikoRingkas['total_pegawai'] }} pegawai sedang cuti</p>
                            <span class="mt-2 px-3 py-1 text-xs font-bold rounded-full {{ $risikoRingkas['status_bahaya'] ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-green-100 text-green-700' }}">
                                {{ $risikoRingkas['status_bahaya'] ? 'Bahaya!' : 'Aman' }}
                            </span>
                        </div>

                        @if($risikoRingkas['sedang_cuti'] > 0)
                            <div class="flex flex-wrap gap-2 justify-center mt-2">
                                @foreach($risikoRingkas['daftar_pegawai'] as $i => $p)
                                    <span class="gauge-chip {{ $risikoRingkas['status_bahaya'] ? 'bg-red-100 border-red-200' : 'bg-gray-50 border-gray-200' }} border rounded-full px-3 py-1.5 text-xs flex items-center gap-1"
                                        style="animation-delay: {{ .5 + $i * .09 }}s">
                                        <b class="font-bold {{ $risikoRingkas['status_bahaya'] ? 'text-red-700' : 'text-gray-800' }}">{{ $p['nama'] }}</b>
                                        <span class="{{ $risikoRingkas['status_bahaya'] ? 'text-red-700/75' : 'text-gray-400' }}">&middot; s.d. {{ $p['tanggal_selesai'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                            <button type="button"
                                onclick='openRincianModal("Seksi/Sub-Bagian Anda", @json($risikoRingkas["daftar_pegawai"]), "pegawai")'
                                class="mt-3 text-sm font-semibold text-[#2A65F3] hover:text-blue-800 text-center">
                                Lihat rincian semua pegawai yang cuti
                            </button>
                        @else
                            <p class="text-center text-sm text-gray-500 mt-2">tidak ada yang sedang cuti.</p>
                        @endif
                    </div>
                    @else
                    <!-- MODE KEPALA KANTOR (rincian sub-unit) & KEPALA BIDANG/BAGIAN (rincian nama pegawai) -->
                    <div class="reveal card-lift bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col" style="animation-delay: .22s">
                        <h3 class="text-md font-medium text-gray-700 mb-2">Risiko Kekosongan per Divisi</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Deteksi dini divisi dengan staf cuti &gt; 50%
                            @if($levelKepala === 'kantor')
                                &middot; <span class="text-[#2A65F3] font-medium">klik salah satu bagian untuk lihat rincian sub-unit</span>
                            @elseif($levelKepala === 'bidang')
                                &middot; <span class="text-[#2A65F3] font-medium">klik salah satu seksi untuk lihat nama pegawainya</span>
                            @endif
                        </p>
                        <div class="relative h-64 w-full flex-grow">
                            <canvas id="riskChart"></canvas>
                        </div>
                        <div class="mt-4 space-y-2 max-h-32 overflow-y-auto pr-2">
                            @forelse($risikoDivisi as $i => $risiko)
                                @php
                                    $bisaDiklik = ($levelKepala === 'kantor' && !empty($risiko['rincian']))
                                        || ($levelKepala === 'bidang' && $risiko['sedang_cuti'] > 0);
                                    $tipeRincian = $levelKepala === 'kantor' ? 'sub_unit' : 'pegawai';
                                @endphp
                                <div
                                    @if($bisaDiklik)
                                        onclick='openRincianModal(@json($risiko["nama_divisi"]), @json($risiko["rincian"]), @json($tipeRincian))'
                                        class="flex justify-between items-center text-sm cursor-pointer hover:bg-gray-50 rounded {{ $risiko['status_bahaya'] ? 'border-l-4 border-red-500 pl-2 bg-red-50 py-1' : '' }}"
                                    @else
                                        class="flex justify-between items-center text-sm {{ $risiko['status_bahaya'] ? 'border-l-4 border-red-500 pl-2 bg-red-50 py-1' : '' }}"
                                    @endif
                                >
                                    <span class="{{ $risiko['status_bahaya'] ? 'font-semibold text-red-700' : 'text-gray-700' }}">
                                        {{ $risiko['nama_divisi'] }} — ({{ $risiko['persentase'] }}%)
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
            <div class="reveal bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" style="animation-delay: .28s">
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
                            <tr class="reveal hover:bg-gray-50/50 transition" style="animation-delay: {{ .32 + ($loop->index * .06) }}s">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">{{ $pengajuan->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $pengajuan->user->bagianBidang->nama ?? $pengajuan->user->bagianBidang->nama_bagian ?? 'Divisi Tidak Diketahui' }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $pengajuan->jenisCuti->nama_cuti ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->translatedFormat('l, d F Y') }}</td>
                                <td class="px-6 py-4 text-center text-gray-600">{{ $pengajuan->durasi_hari }} Hari</td>
                                <td class="px-6 py-4 text-center">
                                <x-status-dot status="menunggu">{{ $pengajuan->status_label ?? 'Menunggu' }}</x-status-dot>
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
    <div id="drillDownModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="modal-backdrop absolute inset-0 bg-gray-900/50 opacity-0" onclick="closeDrillDownModal()"></div>
        <div class="modal-panel relative bg-white rounded-xl shadow-lg w-full max-w-md p-6 mx-4 opacity-0 scale-95">
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
    <div id="rincianDivisiModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="modal-backdrop absolute inset-0 bg-gray-900/50 opacity-0" onclick="closeRincianModal()"></div>
        <div class="modal-panel relative bg-white rounded-xl shadow-lg w-full max-w-md p-6 mx-4 opacity-0 scale-95">
            <button onclick="closeRincianModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <h3 id="rincianDivisiTitle" class="text-lg font-bold text-gray-800 mb-1">Rincian Divisi</h3>
            <p id="rincianDivisiSubtitle" class="text-xs text-gray-500 mb-4">Sebaran pegawai yang sedang cuti per sub-unit</p>

            <ul id="rincianDivisiList" class="divide-y divide-gray-100 max-h-80 overflow-y-auto"></ul>

            <div class="mt-6 text-right">
                <button onclick="closeRincianModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- HELPER TRANSISI MODAL (fade + scale, dipakai semua modal di halaman ini) ---
        function bukaModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(() => {
                modal.querySelector('.modal-backdrop').classList.remove('opacity-0');
                modal.querySelector('.modal-panel').classList.remove('opacity-0', 'scale-95');
            });
        }
        function tutupModal(id) {
            const modal = document.getElementById(id);
            modal.querySelector('.modal-backdrop').classList.add('opacity-0');
            modal.querySelector('.modal-panel').classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 250);
        }

        // --- FUNGSI MODAL DRILL-DOWN ---
        function openDrillDownModal() {
            bukaModal('drillDownModal');
        }

        function closeDrillDownModal() {
            tutupModal('drillDownModal');
        }

        // --- FUNGSI MODAL RINCIAN RISIKO (klik slice chart Risiko Kekosongan) ---
        // tipe: 'sub_unit' (breakdown per Seksi/Sub-Bagian, khusus Kepala Kantor)
        //    atau 'pegawai' (daftar nama pegawai yang sedang cuti, Kepala Bidang & Kepala Seksi)
        function openRincianModal(namaDivisi, rincian, tipe) {
            document.getElementById('rincianDivisiTitle').textContent = 'Rincian ' + namaDivisi;
            const list = document.getElementById('rincianDivisiList');

            if (!rincian || rincian.length === 0) {
                const teksKosong = tipe === 'pegawai' ? 'Tidak ada pegawai yang sedang cuti.' : 'Tidak ada sub-unit dengan pegawai aktif.';
                list.innerHTML = `<li class="py-4 text-center text-gray-500 text-sm">${teksKosong}</li>`;
            } else if (tipe === 'pegawai') {
                document.getElementById('rincianDivisiSubtitle').textContent = 'Daftar pegawai yang sedang cuti';
                list.innerHTML = rincian.map(p => `
                    <li class="py-3 flex justify-between items-center gap-3">
                        <div>
                            <span class="text-gray-800 font-medium block">${p.nama}</span>
                            <span class="text-xs text-gray-400">NIP ${p.nip} &middot; ${p.tanggal_mulai} - ${p.tanggal_selesai}</span>
                        </div>
                    </li>
                `).join('');
            } else {
                document.getElementById('rincianDivisiSubtitle').textContent = 'Sebaran pegawai yang sedang cuti per sub-unit';
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

            bukaModal('rincianDivisiModal');
        }

        function closeRincianModal() {
            tutupModal('rincianDivisiModal');
        }

        // --- ANIMASI ANGKA "MENGHITUNG NAIK" UNTUK KARTU STATISTIK ---
        function animateCountUp(el, target, duration = 900) {
            const suffix = el.dataset.suffix || '';
            const startTime = performance.now();
            function step(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                el.textContent = Math.round(target * eased) + suffix;
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = target + suffix;
            }
            requestAnimationFrame(step);
        }

        // --- INISIALISASI GRAFIK CHART.JS ---
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll('[data-count-up]').forEach((el) => {
                const target = parseFloat(el.dataset.countUp) || 0;
                animateCountUp(el, target);
            });

            // Meteran busur risiko Seksi/Sub-Bagian: gerakkan dari 0 ke persentase saat ini
            const gaugeArcSeksi = document.getElementById('gaugeArcSeksi');
            if (gaugeArcSeksi) {
                const target = {{ $risikoRingkas['persentase'] ?? 0 }};
                const offset = 282.74 * (1 - Math.min(target, 100) / 100);
                requestAnimationFrame(() => { gaugeArcSeksi.style.strokeDashoffset = offset; });
            }
            
            // 1. GRAFIK TREN BULANAN (Sisi Kiri) - batang interaktif, bergerak saat terlihat di layar
            document.querySelectorAll('[data-trend]').forEach((w) => {
                const cols = [...w.querySelectorAll('.trend-col')];
                const readout = w.querySelector('.trend-readout');
                const bars = w.querySelector('.trend-bars');
                // Kartu lain di baris grid yang sama (Risiko Kekosongan)
                const grid = w.closest('[data-trend-grid]');
                const kartuLain = grid ? [...grid.children].filter((el) => !el.contains(w)) : [];
                const bulanIni = parseInt(w.dataset.bulanIni, 10) - 1;
                const puncak = parseInt(w.dataset.puncak, 10);
                let timer;

                const pilih = (i) => {
                    cols.forEach((c, j) => c.classList.toggle('is-on', j === i));
                    const c = cols[i];
                    const paling = (i === puncak && parseInt(c.dataset.jumlah, 10) > 0) ? ' (paling banyak tahun ini)' : '';
                    readout.innerHTML = `${c.dataset.nama}: <b>${c.dataset.jumlah}</b> cuti disetujui${paling}`;
                };

                // Animasi pembuka, hanya SEKALI per muat halaman (ulangi dengan refresh): batang naik satu per satu,
                // lalu penanda menyapu dari Januari sampai bulan ini
                const putar = () => {
                    clearTimeout(timer);
                    cols.forEach((c) => { c.classList.remove('is-go'); void c.offsetWidth; c.classList.add('is-go'); });
                    let i = 0;
                    const langkah = () => {
                        pilih(i);
                        if (i++ < bulanIni) timer = setTimeout(langkah, 110);
                    };
                    timer = setTimeout(langkah, 500);
                };

                // --- Dropdown rincian pegawai: klik batang = buka, klik lagi / tombol x / Esc = tutup ---
                const drop = w.querySelector('[data-trend-drop]');
                const kartu = drop.querySelector('.trend-drop-card');
                const panels = [...drop.querySelectorAll('[data-panel]')];
                const kurangGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                let terbuka = -1;
                drop.inert = true; // saat tertutup: tidak bisa difokus & tidak dibaca pembaca layar

                const posisiPanah = () => {
                    if (terbuka < 0) return;
                    const cb = cols[terbuka].getBoundingClientRect();
                    const kb = kartu.getBoundingClientRect();
                    kartu.style.setProperty('--caret-x', (cb.left + cb.width / 2 - kb.left) + 'px');
                };
                // Selama dropdown terbuka, tinggi area batang DAN kartu sebelahnya dikunci. Tanpa ini, grid
                // menyamakan tinggi kedua kartu, sehingga Risiko Kekosongan ikut memanjang (ruang kosong
                // besar) dan batang ikut mengecil saat kartu tren bertambah tinggi oleh daftar pegawai.
                let timerLepas;
                const kunciTinggi = () => {
                    clearTimeout(timerLepas);
                    if (bars.style.minHeight) return; // sudah terkunci (mis. ganti bulan saat dropdown terbuka)
                    bars.style.minHeight = bars.offsetHeight + 'px';
                    kartuLain.forEach((el) => { el.style.height = el.offsetHeight + 'px'; el.style.alignSelf = 'flex-start'; });
                };
                const lepasTinggi = () => {
                    clearTimeout(timerLepas);
                    // tunggu animasi tutup dropdown (.3s) selesai supaya tidak ada lompatan ukuran
                    timerLepas = setTimeout(() => {
                        bars.style.minHeight = '';
                        kartuLain.forEach((el) => { el.style.height = ''; el.style.alignSelf = ''; });
                    }, 350);
                };
                const tutup = () => {
                    lepasTinggi();
                    terbuka = -1;
                    drop.classList.remove('is-open');
                    drop.inert = true;
                    cols.forEach((c) => { c.classList.remove('is-open'); c.setAttribute('aria-expanded', 'false'); });
                };
                const buka = (i) => {
                    kunciTinggi();
                    terbuka = i;
                    panels.forEach((p) => { p.hidden = parseInt(p.dataset.panel, 10) !== i; });
                    cols.forEach((c, j) => { c.classList.toggle('is-open', j === i); c.setAttribute('aria-expanded', j === i ? 'true' : 'false'); });
                    drop.inert = false;
                    drop.classList.add('is-open');
                    posisiPanah();
                    requestAnimationFrame(() => kartu.scrollIntoView({ block: 'nearest', behavior: kurangGerak ? 'auto' : 'smooth' }));
                };
                window.addEventListener('resize', posisiPanah);
                drop.querySelectorAll('[data-trend-tutup]').forEach((b) => b.addEventListener('click', () => {
                    const c = cols[terbuka]; tutup(); if (c) c.focus();
                }));
                w.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && terbuka >= 0) { const c = cols[terbuka]; tutup(); c.focus(); }
                });

                cols.forEach((c) => {
                    const i = parseInt(c.dataset.i, 10);
                    const f = () => { clearTimeout(timer); pilih(i); };
                    c.addEventListener('pointerenter', f);
                    c.addEventListener('focus', f);
                    c.addEventListener('click', () => { f(); terbuka === i ? tutup() : buka(i); });
                    c.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); c.click(); }
                    });
                });

                pilih(bulanIni);
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver((es, ob) => {
                        if (es[0].isIntersecting) { putar(); ob.disconnect(); }
                    }, { threshold: 0.4 }).observe(w);
                } else {
                    putar();
                }
            });

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
                        animation: {
                            duration: 900,
                            easing: 'easeOutQuart',
                            delay: (context) => context.type === 'data' ? context.dataIndex * 90 : 0
                        },
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
                            // Kepala Kantor -> rincian sub-unit; Kepala Bidang -> daftar nama pegawai
                            if (elements.length === 0) return;
                            const item = risikoDataRaw[elements[0].index];
                            if (levelKepala === 'kantor' && item.rincian && item.rincian.length > 0) {
                                openRincianModal(item.nama_divisi, item.rincian, 'sub_unit');
                            } else if (levelKepala === 'bidang' && item.sedang_cuti > 0) {
                                openRincianModal(item.nama_divisi, item.rincian, 'pegawai');
                            }
                        },
                        onHover: (evt, elements) => {
                            const bisaDiklik = elements.length > 0 && (levelKepala === 'kantor' || levelKepala === 'bidang');
                            evt.native.target.style.cursor = bisaDiklik ? 'pointer' : 'default';
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>