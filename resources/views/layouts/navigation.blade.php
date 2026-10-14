<nav x-data="{ open: false, scrolled: false }"
    x-init="scrolled = window.pageYOffset > 40"
    @scroll.window.passive="scrolled = scrolled ? (window.pageYOffset > 10) : (window.pageYOffset > 40)"
    :class="scrolled ? 'shadow-md' : 'shadow-sm'"
    class="fixed inset-x-0 top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur transition-shadow duration-300">
                
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div :class="scrolled ? 'h-16' : 'h-20'" class="flex justify-between items-center transition-[height] duration-300">
            
            <!-- Bagian Kiri: DUA LOGO BERDAMPINGAN -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <!-- Logo Instansi (Otban) -->
                    <img src="{{ asset('img/Logo Otban.png') }}"
                         class="block w-auto drop-shadow-sm transition-all duration-300"
                         :class="scrolled ? 'h-10' : 'h-[50px]'" alt="Logo Instansi" />
                    <!-- Garis Pemisah -->
                    <div class="w-px h-8 bg-gray-300"></div>
                    <!-- Logo Web (PELITA) -->
                    <img src="{{ asset('img/logo-pelita.png') }}"
                        class="block w-auto drop-shadow-sm transition-all duration-300"
                        :class="scrolled ? 'h-11' : 'h-14'" alt="Logo PELITA" />
                </a>
            </div>

                       <!-- Bagian Kanan: Menu Navigasi Desktop -->
            <div class="hidden sm:flex sm:items-center gap-6 lg:gap-8">

                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Beranda
                </x-nav-link>

                @hasanyrole(['Kepala Seksi','Kepala Bagian', 'Kepala Bidang', 'Kepala Sub-Bagian', 'Kepala TU', 'Kepala Kantor'])
                    <x-nav-link :href="route('kepala.approval.index')" :active="request()->routeIs('kepala.approval.*')">
                        Approval Cuti
                    </x-nav-link>
                @endhasanyrole

                @if(! auth()->user()->hasAnyRole(['Kepala Seksi','Kepala Bagian', 'Kepala Bidang', 'Kepala Sub-Bagian', 'Kepala TU', 'Kepala Kantor']) && auth()->user()->sedangMenjadiPlh())
                    <x-nav-link :href="route('kepala.approval.index')" :active="request()->routeIs('kepala.approval.*')">
                        Approval Cuti (PLH)
                    </x-nav-link>
                @endif

                @unlessrole('Kepala Kantor')
                    <x-nav-link :href="route('pengajuan.index')" :active="request()->is('pengajuan')">
                        Pengajuan Cuti
                    </x-nav-link>

                    <x-nav-link :href="route('pengajuan.riwayat')" :active="request()->is('riwayat-pengajuan') || request()->is('pengajuan/*')">
                        Riwayat Pengajuan
                    </x-nav-link>
                @endunlessrole

                <!-- Notifikasi (lonceng + badge belum dibaca) -->
                @php $jumlahNotif = $jumlahNotifBelumDibaca ?? 0; @endphp
                <x-nav-link :href="route('notifikasi')" :active="request()->is('notifikasi')" class="group gap-1.5">
                    Notifikasi
                    <span class="relative inline-flex">
                        <svg class="w-4 h-4 origin-top transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        @if($jumlahNotif > 0)
                            <span class="absolute -top-2 -right-2.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white">
                                {{ $jumlahNotif > 9 ? '9+' : $jumlahNotif }}
                            </span>
                        @endif
                    </span>
                </x-nav-link>

                <!-- Dropdown Profil -->
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen"
                            class="relative inline-flex items-center gap-1.5 py-1 text-sm font-semibold transition-colors duration-200 focus:outline-none
                                   after:absolute after:inset-x-0 after:-bottom-1 after:h-0.5 after:rounded-full after:transition-colors after:duration-200
                                   {{ request()->routeIs('profile.*') ? 'text-[#2A65F3] after:bg-[#2A65F3]' : 'text-gray-500 hover:text-gray-900 after:bg-transparent hover:after:bg-gray-300' }}">
                        Profil
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="dropdownOpen"
                         @click.away="dropdownOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 transform -translate-y-2"
                         class="absolute right-0 top-full mt-4 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50"
                         style="display: none;">

                        <a href="{{ route('profile.show') }}"
                           class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#2A65F3] font-medium transition-colors">
                            Lihat Profil
                        </a>

                        <div class="h-px bg-gray-100"></div>
                        @unlessrole('Kepala Kantor|Admin')
                            <x-dropdown-link :href="auth()->user()->hasRole('Pegawai') ? route('pegawai.informasi') : route('kepala.informasi')">
                                {{ __('Informasi & Jatah Cuti') }}
                            </x-dropdown-link>
                        @endunlessrole

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

            @hasanyrole(['Kepala Seksi','Kepala Bagian', 'Kepala Bidang', 'Kepala Sub-Bagian', 'Kepala TU', 'Kepala Kantor'])
                <x-responsive-nav-link :href="route('kepala.approval.index')" :active="request()->routeIs('kepala.approval.*')">
                    Approval Cuti
                </x-responsive-nav-link>
            @endhasanyrole

            @if(! auth()->user()->hasAnyRole(['Kepala Seksi','Kepala Bagian', 'Kepala Bidang', 'Kepala Sub-Bagian', 'Kepala TU', 'Kepala Kantor']) && auth()->user()->sedangMenjadiPlh())
                <x-responsive-nav-link :href="route('kepala.approval.index')" :active="request()->routeIs('kepala.approval.*')">
                    Approval Cuti (PLH)
                </x-responsive-nav-link>
            @endif

            @unlessrole('Kepala Kantor')
                <x-responsive-nav-link :href="route('pengajuan.index')" :active="request()->is('pengajuan')">
                    Pengajuan Cuti
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('pengajuan.riwayat')" :active="request()->routeIs('pengajuan.riwayat', 'pengajuan.show')">
                    Riwayat Pengajuan
                </x-responsive-nav-link>
            @endunlessrole
            <x-responsive-nav-link :href="route('notifikasi')" :active="request()->routeIs('notifikasi')">
                Notifikasi
                @if(($jumlahNotifBelumDibaca ?? 0) > 0)
                    <span class="ml-2 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                        {{ $jumlahNotifBelumDibaca > 9 ? '9+' : $jumlahNotifBelumDibaca }}
                    </span>
                @endif
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile.show')" :active="request()->routeIs('profile.*')">
                Profil
            </x-responsive-nav-link>
            @unlessrole('Kepala Kantor|Admin')
                <x-responsive-nav-link :href="auth()->user()->hasRole('Pegawai') ? route('pegawai.informasi') : route('kepala.informasi')">
                    {{ __('Informasi & Jatah Cuti') }}
                </x-responsive-nav-link>
            @endunlessrole

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-red-600 hover:text-red-700 hover:bg-red-50 hover:border-red-300 focus:outline-none focus:text-red-700 focus:bg-red-50 focus:border-red-300 transition duration-150 ease-in-out">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>
<div class="h-20" aria-hidden="true"></div>