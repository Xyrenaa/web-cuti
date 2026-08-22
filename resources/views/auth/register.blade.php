<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Akun - Sistem Layanan Cuti</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    
    <div class="min-h-screen bg-cover bg-center flex items-center justify-center relative" style="background-image: url('{{ asset('img/bg-kantor.jpg') }}');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 sm:p-8 mx-4 my-8">
            
            <div class="text-center mb-6">
                <img src="{{ asset('img/logo-pelita.png') }}" alt="Logo Pelita" class="w-48 mx-auto mb-4 object-contain">
                <h2 class="text-xl font-bold text-gray-800">Pendaftaran Akun Baru</h2>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Baris 1: Nama & NIP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                        <input id="name" type="text" name="name" :value="old('name')" required autofocus placeholder="Budi Santoso" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <label for="nip" class="block text-xs font-semibold text-gray-700 mb-1">NIP</label>
                        <input id="nip" type="text" name="nip" :value="old('nip')" required placeholder="Contoh: 19940321..." class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm">
                        <x-input-error :messages="$errors->get('nip')" class="mt-1" />
                    </div>
                </div>

                <!-- Baris 2: Email -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email</label>
                    <input id="email" type="email" name="email" :value="old('email')" required placeholder="nama@perusahaan.co.id" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Baris 3: Divisi & Jabatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="divisi" class="block text-xs font-semibold text-gray-700 mb-1">Divisi</label>
                        <select id="divisi" name="divisi" required class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm bg-white">
                            <option value="" disabled selected>Pilih Divisi...</option>
                            <option value="Teknologi Informasi">Teknologi Informasi</option>
                            <option value="Kepegawaian / SDM">Kepegawaian / SDM</option>
                            <option value="Keuangan">Keuangan</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Humas">Humas</option>
                        </select>
                        <x-input-error :messages="$errors->get('divisi')" class="mt-1" />
                    </div>
                    <div>
                        <label for="jabatan" class="block text-xs font-semibold text-gray-700 mb-1">Jabatan / Posisi</label>
                        <input id="jabatan" type="text" name="jabatan" :value="old('jabatan')" required placeholder="Senior Developer" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm">
                        <x-input-error :messages="$errors->get('jabatan')" class="mt-1" />
                    </div>
                </div>

                <!-- Baris 4: Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm">
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Sandi</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2 text-sm">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-150 shadow-md text-sm">
                        Daftar Akun
                    </button>
                </div>

                <div class="text-center mt-5 text-xs text-gray-600">
                    Sudah memiliki akun? 
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-bold">Masuk Di Sini</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>