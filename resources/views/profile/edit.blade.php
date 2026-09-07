<x-app-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2 font-medium tracking-wide">
            <a href="{{ route('profile.show') }}" class="hover:underline">Beranda / Profil</a> / Edit Profil
        </div>
        <h1 class="text-white text-3xl font-bold">Edit Profil</h1>
    </div>

    <!-- Wrapper Konten Utama -->
    <div class="relative min-h-screen bg-[#F8FAFC] py-10 px-4 sm:px-6 lg:px-24 overflow-hidden">
        
        <!-- Watermark Logo -->
        <div class="absolute inset-0 flex justify-center items-center pointer-events-none opacity-[0.03] z-0">
            <img src="{{ asset('img/Logo Otban.png') }}" class="w-full max-w-3xl object-contain" alt="Watermark" />
        </div>

        <div class="max-w-5xl mx-auto relative z-10">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Edit Profil</h2>

            <!-- Form Card -->
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                @csrf
                @method('patch')

               <!-- Section: Foto Profil -->
                <h4 class="text-xs font-bold text-[#2A65F3] tracking-widest uppercase mb-4">Foto Profil</h4>
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 mb-8 border-b border-gray-100 pb-8">
                    @php
                        $nameParts = explode(' ', $user->name);
                        $initials = collect($nameParts)->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                    @endphp
                    
                    <!-- Avatar Preview -->
                    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center text-[#2A65F3] text-2xl font-bold uppercase shrink-0 shadow-sm border border-blue-100">
                        {{ $initials }}
                    </div>
                    
                    <!-- Area Upload Modern & Compact -->
                    <div class="flex flex-col justify-center sm:mt-2 text-center sm:text-left">
                        <div class="relative inline-block mb-2">
                            <!-- Input file disembunyikan tapi tetap bisa diklik (menimpa tombol) -->
                            <input type="file" name="avatar" id="avatar" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/png, image/jpeg">
                            
                            <!-- Tampilan Tombol -->
                            <div class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-50 hover:border-gray-400 transition shadow-sm flex items-center justify-center sm:justify-start gap-2 w-full sm:w-max cursor-pointer">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Pilih Foto Baru
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">Format: PNG, JPG (Maks. 2MB)</p>
                        <p class="text-xs text-gray-400">Rekomendasi rasio 1:1</p>
                    </div>
                </div>

                <!-- Section: Data Diri -->
                <!-- Section: Data Diri -->
                <h4 class="text-xs font-bold text-[#2A65F3] tracking-widest uppercase mb-6">Form Data Diri</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    
                    <!-- Kolom yang BISA diedit -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="telepon" class="block text-xs font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon', $user->telepon ?? '') }}" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan nomor telepon">
                    </div>

                    <!-- Kolom yang TIDAK BISA diedit (Read-Only) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" value="{{ $user->nip ?? '-' }}" readonly class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-md px-4 py-2.5 text-sm cursor-not-allowed select-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Alamat Email</label>
                        <input type="email" value="{{ $user->email }}" readonly class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-md px-4 py-2.5 text-sm cursor-not-allowed select-none">
                        <span class="text-[10px] text-gray-400 mt-1 block">*Hubungi admin untuk mengubah email</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Bagian / Bidang</label>
                        <input type="text" value="{{ $user->bagian->nama ?? '-' }}" readonly class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-md px-4 py-2.5 text-sm cursor-not-allowed select-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Sub-Bagian / Seksi</label>
                        <input type="text" value="{{ $user->subBagian->nama ?? '-' }}" readonly class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-md px-4 py-2.5 text-sm cursor-not-allowed select-none">
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('profile.show') }}" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-50 transition">Batal</a>
                    <button type="submit" class="px-6 py-2.5 bg-[#2A65F3] text-white text-sm font-bold rounded-md hover:bg-blue-700 transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>