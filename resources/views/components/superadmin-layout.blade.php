<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
        <script>
            window.escHtml = (s) => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

            window.konfirmasiSubmit = function (form, opsi) {
                const { inputName, ...swalOpsi } = opsi;

                Swal.fire({
                    icon: 'question',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    cancelButtonColor: '#6b7280',
                    confirmButtonColor: '#2A65F3',
                    reverseButtons: true,
                    customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100', title: 'text-xl font-bold text-gray-800' },
                    ...swalOpsi,
                }).then((hasil) => {
                    if (!hasil.isConfirmed) return;

                    if (inputName) {
                        let h = form.querySelector('input[name="' + inputName + '"]');
                        if (!h) {
                            h = document.createElement('input');
                            h.type = 'hidden';
                            h.name = inputName;
                            form.appendChild(h);
                        }
                        h.value = hasil.value;
                    }

                    form.submit();
                });
            };
        </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PELITA') }} - Superadmin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-[#f8fafc] relative min-h-screen flex flex-col">

        <div class="fixed inset-0 z-0 flex justify-center items-center pointer-events-none opacity-[0.04]">
            <img src="{{ asset('img/Logo Otban.png') }}" alt="Watermark" class="w-[550px] grayscale">
        </div>

        <div class="relative z-10 flex-grow flex flex-col">

            @php
                $menu = [
                    ['label' => 'Beranda',      'route' => 'superadmin.dashboard',       'aktif' => 'superadmin.dashboard'],
                    ['label' => 'Data Pegawai', 'route' => 'superadmin.pegawai.index',   'aktif' => 'superadmin.pegawai.*'],
                    ['label' => 'Plh',          'route' => 'superadmin.plh.index',       'aktif' => 'superadmin.plh.*'],
                    ['label' => 'Plt',          'route' => 'superadmin.penugasan.index', 'aktif' => 'superadmin.penugasan.*'],
                ];
            @endphp

            <nav x-data="{ open: false, scrolled: false }"
                 @scroll.window.passive="scrolled = (window.pageYOffset > 20)"
                 :class="scrolled ? 'shadow-md' : 'shadow-sm'"
                 class="sticky top-0 z-50 border-b border-gray-100 bg-white transition-shadow duration-300">

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-20">

                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3">
                                <img src="{{ asset('img/Logo Otban.png') }}" class="block h-[50px] w-auto" alt="Logo Instansi" width="50" height="50" />
                                <div class="w-px h-8 bg-gray-300"></div>
                                <img src="{{ asset('img/logo-pelita.png') }}" class="block h-14 w-auto" alt="Logo PELITA" width="186" height="56" />
                            </a>
                        </div>

                        <div class="hidden sm:flex space-x-4 items-center">
                            @foreach($menu as $m)
                                <a href="{{ route($m['route']) }}"
                                   class="px-5 py-2 rounded-full font-bold text-sm transition {{ request()->routeIs($m['aktif']) ? 'bg-[#eef2ff] text-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
                                    {{ $m['label'] }}
                                </a>
                            @endforeach

                            {{-- PROFIL SUPERADMIN DINONAKTIFKAN (sementara). Hapus tanda komentar ini untuk mengaktifkan lagi.
                            <a href="{{ route('profile.show') }}"
                               class="px-5 py-2 rounded-full font-bold text-sm transition {{ request()->routeIs('profile.*') ? 'bg-[#eef2ff] text-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
                                Profil
                            </a>
                            --}}

                            <div class="w-px h-5 bg-gray-200"></div>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-red-500 transition px-2" title="Keluar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/90 backdrop-blur-md border-t border-gray-100">
                    <div class="pt-2 pb-3 space-y-1">
                        @foreach($menu as $m)
                            <x-responsive-nav-link :href="route($m['route'])" :active="request()->routeIs($m['aktif'])">
                                {{ $m['label'] }}
                            </x-responsive-nav-link>
                        @endforeach

                        {{-- PROFIL SUPERADMIN DINONAKTIFKAN (sementara).
                        <x-responsive-nav-link :href="route('profile.show')" :active="request()->routeIs('profile.*')">
                            Profil
                        </x-responsive-nav-link>
                        --}}

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-red-600 hover:text-red-700 hover:bg-red-50 hover:border-red-300 transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-[#2a64f5] shadow-md relative z-40">
                    <div class="max-w-7xl mx-auto py-7 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-grow pb-12">
                {{ $slot }}
            </main>
        </div>

        <footer class="relative z-10 bg-[#e2e8f0] py-4 w-full">
            <div class="text-center text-sm font-medium text-gray-500">
                Kantor Otoritas Bandar Udara Wilayah III Juanda
            </div>
        </footer>
    </body>
</html>