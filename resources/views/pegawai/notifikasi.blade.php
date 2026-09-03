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
                    @foreach(auth()->user()->notifications as $notification)
    @if($notification->unread())
        <!-- Notifikasi Belum Dibaca (Bisa Diklik) -->
        <form action="{{ route('notifikasi.read', $notification->id) }}" method="POST" class="w-full">
            @csrf
            <button type="submit" class="w-full text-left p-4 mb-3 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-lg transition">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-bold text-gray-900">{{ $notification->data['title'] ?? 'Pemberitahuan' }}</h4>
                    <span class="text-xs text-gray-500 flex items-center gap-2">
                        {{ $notification->created_at->diffForHumans() }}
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> <!-- Titik biru penanda unread -->
                    </span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? 'Ada pembaruan status.' }}</p>
            </button>
        </form>
    @else
        <!-- Notifikasi Sudah Dibaca (Tidak perlu form) -->
        <div class="p-4 mb-3 bg-white border border-gray-100 rounded-lg opacity-75">
            <div class="flex justify-between items-center">
                <h4 class="text-sm font-semibold text-gray-700">{{ $notification->data['title'] ?? 'Pemberitahuan' }}</h4>
                <span class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $notification->data['message'] ?? 'Ada pembaruan status.' }}</p>
        </div>
    @endif
@endforeach
                    @forelse ($notifikasis as $notif)
                        <!-- Render Otomatis Berdasarkan Status Baca -->
                        <div class="relative {{ $notif->unread() ? 'bg-blue-50/50 border-blue-100' : 'bg-white border-gray-100' }} border rounded-lg p-4 flex justify-between items-start gap-4 transition hover:shadow-sm cursor-pointer">
                            <div>
                                <!-- Judul Notifikasi -->
                                <h4 class="font-bold {{ $notif->unread() ? 'text-gray-900' : 'text-gray-700' }} text-sm">
                                    {{ $notif->data['judul'] ?? 'Pemberitahuan Baru' }}
                                </h4>
                                <!-- Pesan Notifikasi -->
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $notif->data['pesan'] ?? '-' }}
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-3 shrink-0 mt-1">
                                <!-- Waktu (Contoh: "5 menit yang lalu") -->
                                <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                                
                                <!-- Titik Biru Menyala Jika Belum Dibaca -->
                                @if($notif->unread())
                                    <span class="w-2.5 h-2.5 bg-[#2A65F3] rounded-full"></span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <!-- Tampilan Jika Kosong -->
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <p>Kotak masuk notifikasi Anda bersih.</p>
                        </div>
                    @endforelse

                </div>
            </div>
</x-app-layout>