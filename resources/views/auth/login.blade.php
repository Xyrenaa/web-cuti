<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Layanan Cuti</title>
    
    <!-- Memanggil Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    
    <!-- Background Full Screen -->
    <div class="min-h-screen bg-cover bg-center flex items-center justify-center relative" style="background-image: url('{{ asset('img/bg-kantor.jpg') }}');">
        
        <!-- Efek Gelap Transparan (Overlay) -->
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <!-- Kotak Putih Login: Diperkecil lebarnya (max-w-sm) dan paddingnya disesuaikan -->
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 mx-4">
            
            <!-- Logo & Judul -->
            <div class="text-center mb-6">
                <!-- Ukuran logo diperbesar menggunakan width (w-64) agar lebih proporsional -->
                <img src="{{ asset('img/logo-pelita.png') }}" alt="Logo Pelita" class="w-64 mx-auto mb-4 object-contain">
                <h2 class="text-xl font-bold text-gray-800">Selamat Datang Kembali</h2>
                <p class="text-xs text-gray-500 mt-1">Masuk untuk mengelola cuti Anda</p>
            </div>

            <!-- Form Login -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email</label>
                    <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Masukkan alamat email" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan Kata Sandi" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Lupa Sandi -->
                <div class="flex items-center justify-between mb-5">
                    @if (Route::has('password.request'))
                        <a class="text-xs text-blue-600 hover:text-blue-800 font-medium" href="{{ route('password.request') }}">
                            Lupa Kata Sandi?
                        </a>
                    @endif
                </div>

                <!-- Tombol Masuk -->
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-150 shadow-md text-sm">
                        Login
                    </button>
                </div>

                <!-- Daftar Sekarang -->
                <div class="text-center mt-5 text-xs text-gray-600">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-bold">Daftar Sekarang</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>