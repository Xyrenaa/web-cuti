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

                <!-- NIP -->
                <div class="mb-4">
                    <label for="nip" class="block text-xs font-semibold text-gray-700 mb-1">NIP</label>
                    <input id="nip" type="text" name="nip" value="{{ old('nip') }}" required autofocus inputmode="numeric" autocomplete="username" placeholder="Masukkan NIP" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    <x-input-error :messages="$errors->get('nip')" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mb-4" x-data="{ showPassword: false }">
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan Kata Sandi" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 pr-10 text-sm">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" tabindex="-1" aria-label="Tampilkan/sembunyikan kata sandi">
                            <!-- Ikon mata dicoret: ditampilkan saat password TERSEMBUNYI -->
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <!-- Ikon mata terbuka: ditampilkan saat password TERLIHAT -->
                            <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Captcha angka -->
                @if ($captchaSrc)
                    <div class="mb-4"
                         x-data="{
                             src: @js($captchaSrc),
                             memuat: false,
                             pesan: '',
                             async segarkan() {
                                 if (this.memuat) return;
                                 this.memuat = true; this.pesan = '';
                                 try {
                                     const r = await fetch(@js(route('captcha.segarkan', [], false)) + '?t=' + Date.now(), {
                                         headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                         credentials: 'same-origin',
                                         cache: 'no-store',
                                     });
                                     if (r.status === 429) { this.pesan = 'Terlalu sering mengganti gambar. Tunggu sebentar lalu coba lagi.'; }
                                     else if (!r.ok) {
                                         // Saat APP_DEBUG=true Laravel mengirim penyebab asli di field message;
                                         // saat produksi isinya generik ('Server Error'), jadi aman ditampilkan.
                                         const d = await r.json().catch(() => ({}));
                                         const rinci = (d.message && d.message !== 'Server Error') ? ' — ' + String(d.message).slice(0, 160) : '';
                                         this.pesan = 'Gambar baru gagal dimuat (kode ' + r.status + ')' + rinci;
                                     }
                                     else {
                                         this.src = (await r.json()).src;
                                         this.$refs.captcha.value = '';
                                         this.$refs.captcha.focus();
                                     }
                                 } catch (e) {
                                     this.pesan = 'Gambar baru gagal dimuat. Periksa koneksi lalu tekan tombol lagi.';
                                 }
                                 this.memuat = false;
                             }
                         }">
                        <div class="flex items-center gap-2 mb-2">
                            <img :src="src" alt="Gambar captcha angka" width="150" height="48"
                                 class="w-[150px] h-12 rounded-md border border-gray-200 select-none"
                                 :class="memuat ? 'opacity-50' : ''" draggable="false">
                            <button type="button" @click="segarkan()" :disabled="memuat"
                                    class="shrink-0 p-1.5 rounded-md border border-gray-300 text-gray-500 hover:text-blue-600 hover:border-blue-600 transition disabled:opacity-50"
                                    aria-label="Ganti gambar captcha" title="Ganti gambar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" :class="memuat ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <input id="captcha" x-ref="captcha" type="text" name="captcha" required inputmode="numeric" pattern="[0-9]*" maxlength="{{ config('captcha.length', 5) }}" autocomplete="off" placeholder="Masukkan angka" class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm tracking-widest">
                        <p x-show="pesan" x-cloak x-text="pesan" class="mt-1 text-xs text-red-600"></p>
                        <x-input-error :messages="$errors->get('captcha')" class="mt-1" />
                    </div>
                @endif

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

            </form>
        </div>
    </div>

</body>
</html>