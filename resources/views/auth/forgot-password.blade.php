<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Kata Sandi - Sistem Layanan Cuti</title>
    
    <!-- Memanggil Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    
    <!-- Background Full Screen (Sama dengan halaman Login) -->
    <div class="min-h-screen bg-cover bg-center flex items-center justify-center relative" style="background-image: url('{{ asset('img/bg-kantor.jpg') }}');">
        
        <!-- Efek Gelap Transparan (Overlay) -->
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <!-- Kotak Putih Lupa Sandi -->
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 mx-4">
            
            <!-- Tombol Kembali ke Login -->
            <a href="{{ route('login') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-blue-600 mb-6 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Login
            </a>

            <!-- Judul & Deskripsi -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Lupa Kata Sandi</h2>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Masukkan alamat email yang terdaftar. Kami akan mengirimkan instruksi dan tautan untuk mengatur ulang kata sandi Anda.
                </p>
            </div>

            <!-- Status Pengiriman Email (Pesan Sukses bawaan Laravel) -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Form Lupa Sandi -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-5">
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email</label>
                    <input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@perusahaan.co.id" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Tombol Kirim -->
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-150 shadow-md text-sm">
                        Kirim Link Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>