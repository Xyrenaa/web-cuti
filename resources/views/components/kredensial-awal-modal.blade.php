{{-- Pop-up wajib saat login pertama: konfirmasi email aktif + ganti kata sandi.
     Tidak bisa ditutup (tanpa tombol close/klik luar/Esc); satu-satunya jalan
     keluar selain menyimpan adalah Keluar (logout). Dipasang di kedua layout. --}}
@auth
    @if (auth()->user()->wajib_ganti_kredensial)
        @php($bag = $errors->getBag('kredensialAwal'))
        <div x-data="{ lihatPw: false }"
             x-init="document.body.classList.add('overflow-hidden')"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm overflow-y-auto"
             role="dialog" aria-modal="true" aria-labelledby="judul-kredensial">

            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-8 my-auto">
                <div class="text-center mb-5">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4" />
                        </svg>
                    </div>
                    <h2 id="judul-kredensial" class="text-lg font-bold text-gray-800">Amankan Akun Cuti Anda</h2>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Ini login pertama Anda. Demi keamanan, lengkapi email aktif dan ganti kata sandi awal
                        sebelum melanjutkan.
                    </p>
                </div>

                <form method="POST" action="{{ route('kredensial.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="kred_email" class="block text-xs font-semibold text-gray-700 mb-1">Email Aktif</label>
                        <input id="kred_email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               placeholder="nama@email.com"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                        <x-input-error :messages="$bag->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label for="kred_email_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Ulangi Email</label>
                        <input id="kred_email_confirmation" type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" required autocomplete="off"
                               placeholder="Ketik ulang email Anda"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                        <p class="text-[11px] text-gray-400 mt-1">Email dipakai untuk pemulihan kata sandi, pastikan tidak salah ketik.</p>
                    </div>

                    <div>
                        <label for="kred_password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru</label>
                        <div class="relative">
                            <input id="kred_password" :type="lihatPw ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                   placeholder="Minimal 8 karakter"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 pr-16 text-sm">
                            <button type="button" @click="lihatPw = !lihatPw" tabindex="-1"
                                    class="absolute inset-y-0 right-0 px-3 text-[11px] font-semibold text-gray-400 hover:text-gray-600"
                                    x-text="lihatPw ? 'Sembunyi' : 'Lihat'"></button>
                        </div>
                        <x-input-error :messages="$bag->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label for="kred_password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input id="kred_password_confirmation" :type="lihatPw ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                               placeholder="Ulangi kata sandi baru"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm px-4 py-2.5 text-sm">
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-150 shadow-md text-sm">
                        Simpan &amp; Lanjutkan
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center mt-4">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 underline">Keluar</button>
                </form>
            </div>
        </div>
    @endif

    @if (session('kredensial_berhasil'))
        <div x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 5000)" x-show="tampil" x-transition
             class="fixed top-4 right-4 z-[100] rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-lg">
            Email dan kata sandi berhasil disimpan. Akun Anda kini lebih aman.
        </div>
    @endif
@endauth