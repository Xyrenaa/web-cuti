<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Kata Sandi - Sistem Layanan Cuti</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    
    <div class="min-h-screen bg-cover bg-center flex items-center justify-center relative" style="background-image: url('{{ asset('img/bg-kantor.jpg') }}');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 mx-4">
            
            <div class="text-center mb-6">
                <img src="{{ asset('img/logo-pelita.png') }}" alt="Logo Pelita" class="w-48 mx-auto mb-4 object-contain">
                <h2 class="text-xl font-bold text-gray-800">Buat Sandi Baru</h2>
                <p class="text-xs text-gray-500 mt-1">Silakan masukkan kata sandi baru Anda</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Token Rahasia dari Link -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email (Otomatis terisi dan tidak bisa diedit/readonly) -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-500 shadow-sm px-4 py-2.5 text-sm" readonly>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Sandi Baru -->
                <div class="mb-4">
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru</label>
                    <input id="password" type="password" name="password" required autofocus placeholder="Minimal 8 karakter" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Konfirmasi Sandi -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Sandi Baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi kata sandi" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-150 shadow-md text-sm">
                        Simpan Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>