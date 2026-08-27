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
                <div class="flex flex-col md:flex-row items-center gap-6 mb-8 border-b border-gray-100 pb-8">
                    @php
                        $nameParts = explode(' ', $user->name);
                        $initials = collect($nameParts)->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                    @endphp
                    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center text-[#2A65F3] text-2xl font-bold uppercase shrink-0">
                        {{ $initials }}
                    </div>
                    
                    <!-- Area Upload Dropzone -->
                    <div class="w-full relative">
                        <input type="file" name="avatar" id="avatar" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/png, image/jpeg">
                        <div class="w-full border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <span class="text-sm font-bold text-gray-700">Unggah foto baru</span>
                            <span class="text-xs text-gray-500 mt-1">PNG, JPG maks 2MB (Rekomendasi ukuran square 1:1)</span>
                        </div>
                    </div>
                </div>

                <!-- Section: Data Diri -->
                <h4 class="text-xs font-bold text-[#2A65F3] tracking-widest uppercase mb-6">Form Data Diri</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 mb-2">Alamat Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="telepon" class="block text-xs font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon', $user->telepon ?? '') }}" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="divisi" class="block text-xs font-semibold text-gray-700 mb-2">Divisi / Departemen</label>
                        <select name="divisi" id="divisi" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-700">
                            <option value="Teknologi Informasi" {{ ($user->divisi ?? '') == 'Teknologi Informasi' ? 'selected' : '' }}>Teknologi Informasi</option>
                            <option value="Operasional" {{ ($user->divisi ?? '') == 'Operasional' ? 'selected' : '' }}>Operasional</option>
                            <option value="Keuangan" {{ ($user->divisi ?? '') == 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                            <!-- Tambahkan opsi lain sesuai kebutuhan -->
                        </select>
                    </div>
                    <div>
                        <label for="jabatan" class="block text-xs font-semibold text-gray-700 mb-2">Jabatan Pekerjaan</label>
                        <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan', $user->jabatan ?? '') }}" class="w-full bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
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