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
                <div class="mb-4" x-data="{ showPassword: false }">
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autofocus placeholder="Minimal 8 karakter" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 pr-10 text-sm">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" tabindex="-1" aria-label="Tampilkan/sembunyikan kata sandi">
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Konfirmasi Sandi -->
                <div class="mb-6" x-data="{ showPassword: false }">
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Sandi Baru</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 pr-10 text-sm">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" tabindex="-1" aria-label="Tampilkan/sembunyikan konfirmasi kata sandi">
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
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