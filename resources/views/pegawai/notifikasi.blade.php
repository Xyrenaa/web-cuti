<x-app-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Notifikasi</div>
        <h1 class="text-white text-3xl font-bold">Notifikasi</h1>
    </div>

    <!-- Wrapper Konten Utama -->
    <div class="relative min-h-screen bg-[#F8FAFC] py-12 px-4 sm:px-6 lg:px-24 overflow-hidden">
        
        <!-- Watermark Logo -->
        <div class="absolute inset-0 flex justify-center items-center pointer-events-none opacity-[0.03] z-0">
            <img src="{{ asset('img/Logo Otban.png') }}" class="w-full max-w-3xl object-contain" alt="Watermark" />
        </div>

        <div class="max-w-4xl mx-auto relative z-10">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Kotak Masuk Notifikasi</h2>

            <!-- Tombol Aksi -->
            <form action="{{ route('notifikasi.readAll') }}" method="POST" class="mb-4">
             @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none">
                <svg class="w-4 h-4" ... ><!-- Ikon mata milikmu --></svg>
                Tandai Semua Dibaca
            </button>
        </form>

            <!-- Kontainer Utama List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                
                <!-- Header Kotak (Angka Dinamis) -->
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <h3 class="font-bold text-gray-800">Semua Notifikasi ({{ $notifikasis->count() }})</h3>
                    @if($belumDibaca > 0)
                        <span class="text-sm font-semibold text-[#2A65F3]">{{ $belumDibaca }} Belum Dibaca</span>
                    @endif
                </div>

                <!-- Daftar Notifikasi (Looping Database) -->
                <div class="space-y-3">
                                        @php
                        $gaya = [
                            'aksi'   => ['Butuh Tindakan', 'bg-amber-100 text-amber-700'],
                            'status' => ['Perubahan Status', 'bg-blue-100 text-blue-700'],
                            'ttd'    => ['Tanda Tangan', 'bg-green-100 text-green-700'],
                            'final'  => ['Disetujui', 'bg-emerald-100 text-emerald-700'],
                            'tolak'  => ['Ditolak', 'bg-red-100 text-red-700'],
                            'revisi' => ['Perlu Revisi', 'bg-yellow-100 text-yellow-700'],
                            'batal'  => ['Dibatalkan', 'bg-gray-100 text-gray-600'],
                            'umum'   => ['Pemberitahuan', 'bg-gray-100 text-gray-600'],
                        ];
                    @endphp

                    @forelse ($notifikasis as $notif)
                        @php
                            $tipe = $notif->data['tipe'] ?? 'umum';
                            [$labelTipe, $warnaTipe] = $gaya[$tipe] ?? $gaya['umum'];
                        @endphp
                        <form action="{{ route('notifikasi.read', $notif->id) }}" method="POST" class="contents">
                            @csrf
                            <button type="submit" class="w-full text-left relative {{ $notif->unread() ? 'bg-blue-50/50 border-blue-100' : 'bg-white border-gray-100' }} border rounded-lg p-4 flex justify-between items-start gap-4 transition hover:shadow-sm">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $warnaTipe }}">{{ $labelTipe }}</span>
                                        @if(!empty($notif->data['kode_pengajuan']))
                                            <span class="text-[11px] font-semibold text-gray-400">{{ $notif->data['kode_pengajuan'] }}</span>
                                        @endif
                                    </div>
                                    <h4 class="text-sm font-bold {{ $notif->unread() ? 'text-gray-900' : 'text-gray-700' }}">
                                        {{ $notif->data['judul'] ?? 'Pemberitahuan Baru' }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-600">{{ $notif->data['pesan'] ?? '-' }}</p>
                                </div>

                                <div class="flex items-center gap-3 mt-1 shrink-0">
                                    <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                                    @if($notif->unread())
                                        <span class="w-2.5 h-2.5 bg-[#2A65F3] rounded-full"></span>
                                    @endif
                                </div>
                            </button>
                        </form>
                    @empty
                        <div class="py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <p>Kotak masuk notifikasi Anda bersih.</p>
                        </div>
                    @endforelse

                </div>
            </div>
</x-app-layout>