<nav x-data="{ open: false, scrolled: false }" 
     @scroll.window="scrolled = (window.pageYOffset > 20)"
     :class="{'bg-white/70 backdrop-blur-lg shadow-md': scrolled, 'bg-white shadow-sm': !scrolled}"
     class="sticky top-0 z-50 border-b border-gray-100 transition-all duration-300">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Tambahkan sedikit transisi di tinggi navbar agar agak mengecil saat discroll (opsional tapi keren) -->
        <div class="flex justify-between items-center transition-all duration-300" :class="{'h-16': scrolled, 'h-20': !scrolled}">
            
            <!-- Bagian Kiri: DUA LOGO BERDAMPINGAN -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <!-- Logo Instansi (Otban) -->
                    <img src="{{ asset('img/Logo Otban.png') }}" 
                         class="block w-auto drop-shadow-sm transition-all duration-300" 
                         :class="{'h-[45px]': scrolled, 'h-[60px]': !scrolled}" 
                         alt="Logo Instansi" />
                    <!-- Garis Pemisah -->
                    <div class="w-px bg-gray-300 transition-all duration-300" :class="{'h-6': scrolled, 'h-8': !scrolled}"></div>
                    <!-- Logo Web (PELITA) -->
                    <img src="{{ asset('img/logo-pelita.png') }}" 
                         class="block w-auto drop-shadow-sm transition-all duration-300" 
                         :class="{'h-[60px]': scrolled, 'h-[80px]': !scrolled}" 
                         alt="Logo PELITA" />
                </a>
            </div>

            <!-- Bagian Kanan: Menu Navigasi Desktop -->
            <div class="hidden sm:flex sm:items-center sm:space-x-2 lg:space-x-4">
                
                <a href="{{ route('dashboard') }}" 
                   class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center 
                   {{ request()->routeIs('dashboard') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-gray-50' }}">
                    Beranda
                </a>
                
                <a href="#" 
                   class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center 
                   {{ request()->routeIs('pengajuan.*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-gray-50' }}">
                    Pengajuan Cuti
                </a>

                <a href="#" 
                   class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center 
                   {{ request()->routeIs('riwayat.*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-gray-50' }}">
                    Riwayat Pengajuan
                </a>

                <a href="#" 
                   class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-1.5 
                   {{ request()->routeIs('notifikasi.*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-gray-50' }}">
                    Notifikasi
                    <svg class="w-4 h-4 {{ request()->routeIs('notifikasi.*') ? 'text-[#2A65F3]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </a>

                <a href="{{ route('profile.edit') }}" 
                   class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center 
                   {{ request()->routeIs('profile.*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-gray-50' }}">
                    Profil
                </a>
            </div>

            <!-- Hamburger Menu (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Dropdown Menu Mobile -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/90 backdrop-blur-md border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Beranda
            </x-responsive-nav-link>
            <!-- Menu mobile lainnya -->
        </div>
    </div>
</nav>