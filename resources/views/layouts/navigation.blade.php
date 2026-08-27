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
                
                <!-- Menu Beranda -->
                <a href="{{ route('dashboard') }}" 
                   class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center 
                   {{ request()->routeIs('dashboard') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-[#e5edff]/60' }}">
                    Beranda
                </a>

                @hasanyrole(['Kepala Seksi', 'Kepala Bidang', 'Kepala Sub Bagian', 'Kepala TU', 'Kepala Kantor'])
                    <a href="{{ route('kepala.approval.index') }}" 
                       class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center {{ request()->routeIs('kepala.approval.*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-[#e5edff]/60' }}">
                       Approval Cuti
                    </a>
                @endhasanyrole
                
                <!-- Menu Pengajuan Cuti -->
                <a href="{{ route('pengajuan.index') }}" 
                class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center {{ request()->is('pengajuan') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-[#e5edff]/60' }}">
                    Pengajuan Cuti
                </a>

                <!-- Menu Riwayat Pengajuan -->
                <a href="{{ route('pengajuan.riwayat') }}" 
                class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center {{ request()->is('riwayat-pengajuan') || request()->is('pengajuan/*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-[#e5edff]/60' }}">
                    Riwayat Pengajuan
                </a>

                <!-- Menu Notifikasi (Dengan Efek Lonceng Goyang) -->
                <a href="{{ route('notifikasi') }}" 
                class="group px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-1.5 
                {{ request()->is('notifikasi') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-[#e5edff]/60' }}">
                    Notifikasi
                    <svg class="w-4 h-4 transition-all duration-300 origin-top group-hover:rotate-12 group-hover:scale-110 {{ request()->is('notifikasi') ? 'text-[#2A65F3]' : 'text-gray-400 group-hover:text-[#2A65F3]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </a>

                <!-- Dropdown Profil (Dengan Gaya Pill yang Sama) -->
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" 
                            class="px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2 focus:outline-none
                            {{ request()->routeIs('profile.*') ? 'bg-[#e5edff] font-bold text-[#2A65F3]' : 'font-semibold text-gray-600 hover:text-[#2A65F3] hover:bg-[#e5edff]/60' }}">
                        Profil
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Isi Kotak Dropdown -->
                    <div x-show="dropdownOpen" 
                         @click.away="dropdownOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 transform -translate-y-2"
                         class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50"
                         style="display: none;">
                        
                        <a href="{{ route('profile.edit') }}" 
                           class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#2A65F3] font-medium transition-colors">
                            Lihat Profil
                        </a>

                        <div class="h-px bg-gray-100"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 font-medium transition-colors">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
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
            
            <x-responsive-nav-link :href="route('pengajuan.riwayat')" :active="request()->routeIs('pengajuan.riwayat', 'pengajuan.show')">
                Riwayat Pengajuan
            </x-responsive-nav-link>
             <x-responsive-nav-link :href="route('notifikasi')" :active="request()->routeIs('notifikasi')">
                Notifikasi
            </x-responsive-nav-link>
        </div>
    </div>
</nav>