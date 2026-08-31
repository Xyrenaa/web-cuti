<x-admin-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">Beranda / Profil</div>
        <h1 class="text-white text-3xl font-bold">Profil</h1>
    </div>

    <!-- Wrapper Konten Utama -->
    <div class="relative min-h-screen bg-[#F8FAFC] py-10 px-4 sm:px-6 lg:px-24 overflow-hidden">
        
        <!-- Watermark Logo -->
        <div class="absolute inset-0 flex justify-center items-center pointer-events-none opacity-[0.03] z-0">
            <img src="{{ asset('img/Logo Otban.png') }}" class="w-full max-w-3xl object-contain" alt="Watermark" />
        </div>

        <div class="max-w-5xl mx-auto relative z-10">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Profil Pengguna</h2>

            <!-- Card Profil -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                
                <!-- Top Section: Avatar & Nama -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 border-b border-gray-100 pb-8">
                    <div class="flex items-center gap-5">
                        <!-- Avatar Inisial Dinamis -->
                        @php
                            $nameParts = explode(' ', $user->name);
                            $initials = collect($nameParts)->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                        @endphp
                        <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center text-[#2A65F3] text-2xl font-bold uppercase shrink-0">
                            {{ $initials }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $user->jabatan ?? 'Pegawai' }} — {{ $user->divisi ?? 'Instansi' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.profile.edit') }}" class="px-5 py-2.5 bg-[#2A65F3] text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit Profil
                    </a>
                </div>

                <!-- Bottom Section: Data Diri -->
                <h4 class="text-xs font-bold text-[#2A65F3] tracking-widest uppercase mb-6">Detail Informasi Karyawan</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Nama Lengkap</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-700">{{ $user->name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">NIP (Nomor Induk Pegawai)</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-700">{{ $user->nip ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Alamat Email</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-700">{{ $user->email }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Divisi / Departemen</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-700">{{ $user->divisi ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Jabatan Pekerjaan</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-700">{{ $user->jabatan ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Tanggal Bergabung</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-700">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>