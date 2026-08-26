<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PELITA - Admin') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-[#f8fafc] relative min-h-screen flex flex-col">
        
        <!-- Watermark Background -->
        <div class="fixed inset-0 z-0 flex justify-center items-center pointer-events-none opacity-[0.04]">
            <img src="{{ asset('img/Logo Otban.png') }}" alt="Watermark" class="w-[550px] grayscale">
        </div>

        <div class="relative z-10 flex-grow flex flex-col">
            
            <!-- Navbar Admin -->
            <nav x-data="{ open: false, scrolled: false }" 
                 @scroll.window="scrolled = (window.pageYOffset > 20)"
                 :class="{'bg-white/90 backdrop-blur-md shadow-md': scrolled, 'bg-white shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]': !scrolled}"
                 class="sticky top-0 z-50 w-full transition-all duration-300">
                
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center transition-all duration-300" :class="{'h-16': scrolled, 'h-20': !scrolled}">
                        
                        <!-- Kiri: DUA LOGO BERDAMPINGAN -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                                <img src="{{ asset('img/Logo Otban.png') }}" alt="Logo Otban" 
                                     class="w-auto transition-all duration-300" 
                                     :class="{'h-12': scrolled, 'h-16': !scrolled}">
                                
                                <div class="w-px bg-gray-200 transition-all duration-300" 
                                     :class="{'h-8': scrolled, 'h-10': !scrolled}"></div>
                                
                                <img src="{{ asset('img/logo-pelita.png') }}" alt="Logo Pelita" 
                                     class="w-auto mt-1 transition-all duration-300" 
                                     :class="{'h-9': scrolled, 'h-12': !scrolled}">
                            </a>
                        </div>

                        <!-- Kanan: Navigation Links -->
                        <div class="hidden sm:flex space-x-8 items-center">
                            <a href="{{ route('admin.dashboard') }}" class="bg-[#eef2ff] text-blue-600 px-5 py-2 rounded-full font-bold text-sm transition">
                                Beranda
                            </a>
                            <a href="#" class="text-gray-500 hover:text-gray-800 font-medium text-sm transition">
                                Approval Cuti
                            </a>
                            <a href="#" class="text-gray-500 hover:text-gray-800 font-medium text-sm transition">
                                Rekap Cuti
                            </a>
                            <a href="#" class="text-gray-500 hover:text-gray-800 font-medium text-sm transition flex items-center">
                                Notifikasi
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="text-gray-500 hover:text-gray-800 font-medium text-sm transition">
                                Profil
                            </a>
                            <div class="w-px h-5 bg-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
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