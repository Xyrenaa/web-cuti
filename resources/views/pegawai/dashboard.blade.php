<x-app-layout>
    <!-- Load CSS untuk Animasi Scroll (AOS) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Wrapper Utama dengan Background Biru Solid -->
    <div class="bg-[#2A65F3] relative overflow-hidden font-sans">
        
        <!-- MOCKUP WAVE (SVG) - Dibuat Lebih Tebal & Beranimasi -->
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none z-0" data-aos="fade-down" data-aos-duration="1500" data-aos-easing="ease-out-cubic">
            <svg class="absolute top-0 left-0 w-full md:w-3/4 h-auto drop-shadow-2xl" viewBox="0 0 1000 800" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0H1000C1000 0 850 200 700 250C550 300 450 450 300 550C150 650 0 800 0 800V0Z" fill="#3B82F6"/>
                <path d="M0 0H800C800 0 650 150 500 250C350 350 250 500 100 600C0 666.67 0 800 0 800V0Z" fill="#60A5FA"/>
            </svg>
        </div>

        <!-- SECTION 1: HERO -->
        <div class="relative z-10 container mx-auto px-6 lg:px-24 min-h-[calc(100vh-5rem)] flex items-center py-10">
            <div class="flex flex-col md:flex-row items-center justify-between w-full gap-10">
                <!-- Teks Kiri -->
                <div class="text-white w-full md:w-3/5" data-aos="fade-right" data-aos-duration="1200" data-aos-delay="300">
                    <h1 class="text-4xl md:text-6xl font-bold mb-4 leading-tight">
                        Selamat Datang, <br>
                        {{ Auth::user()->name ?? 'Pegawai' }}
                    </h1>
                    <p class="text-lg md:text-xl font-light text-blue-100 mb-6">
                        Portal Elektronik Layanan Informasi dan Tata Administrasi Cuti (PELITA)
                    </p>
                   
                </div>
                
                <!-- Logo Besar Kanan -->
                <div class="w-full md:w-2/5 flex justify-end items-center" data-aos="fade-left" data-aos-duration="1200" data-aos-delay="500">
                    <!-- Logo PELITA yang jadi Hero -->
                    <img src="{{ asset('img/Logo Otban.png') }}" alt="Logo Instansi" class="w-64 md:w-80 h-auto opacity-90 drop-shadow-xl" />
                </div>
            </div>
        </div>

        <!-- SECTION 2: TENTANG KAMI -->
        <div class="relative z-10 container mx-auto px-6 lg:px-24 py-24 pb-32">
            <div class="flex flex-col md:flex-row items-center relative">
                <div data-aos="fade-up" data-aos-duration="1000" class="w-full md:w-2/3 bg-white/10 backdrop-blur-xl border border-white/20 p-10 md:p-14 shadow-[0_8px_32px_0_rgba(0,0,0,0.15)] relative z-10 md:-mr-20 lg:-mr-32">
                    <h3 class="text-white/70 text-sm font-bold tracking-widest uppercase mb-3">Tentang Kami</h3>
                    <p class="text-white text-2xl md:text-3xl font-semibold leading-relaxed">
                        Portal Elektronik Layanan Informasi dan Tata Administrasi cuti (PELITA)
                    </p>
                </div>
                <div data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300" class="w-full md:w-1/2 relative z-0 mt-8 md:mt-0">
                    <img src="{{ asset('img/bg-kantor.jpg') }}" alt="Gedung Kantor" class="w-full h-[400px] md:h-[500px] rounded-none shadow-2xl object-cover" />
                </div>
            </div>
        </div>

        <!-- SECTION 3: LAYANAN -->
        <div class="relative z-10 mt-10 text-center container mx-auto px-6">
            <h3 data-aos="fade-up" class="text-white/80 text-sm font-bold tracking-widest uppercase mb-6">Layanan</h3>
            <div data-aos="zoom-in" data-aos-duration="800" class="max-w-3xl mx-auto bg-white rounded-3xl p-10 md:p-14 shadow-2xl transition transform hover:-translate-y-2 duration-300">
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <h4 class="text-3xl font-bold text-gray-800 mb-4">Pengajuan Cuti</h4>
                <p class="text-gray-600 text-lg md:text-xl leading-relaxed">
                    Layanan proses pengajuan, persetujuan, dan pemantauan jadwal cuti Anda secara mandiri, efisien, dan transparan.
                </p>
            </div>
        </div>

        <!-- SECTION 4: KONTAK -->
        <div class="relative z-10 mt-32 text-center pb-24 container mx-auto px-6 lg:px-24">
            <h3 data-aos="fade-up" class="text-white/80 text-sm font-bold tracking-widest uppercase mb-2">Kontak</h3>
            <h2 data-aos="fade-up" data-aos-delay="100" class="text-3xl md:text-4xl font-bold text-white mb-12">Kontak Kami</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="bg-[#dbe4ff] rounded-2xl p-8 text-left shadow-lg text-gray-800">
                    <svg class="w-10 h-10 mb-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h4 class="font-bold text-xl mb-1 text-gray-900">Email</h4>
                    <p class="text-sm font-medium text-gray-700">otban_wil3@kemenhub.go.id</p>
                </div>
                <div data-aos="fade-up" data-aos-delay="300" data-aos-duration="800" class="bg-[#dbe4ff] rounded-2xl p-8 text-left shadow-lg text-gray-800">
                    <svg class="w-10 h-10 mb-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <h4 class="font-bold text-xl mb-1 text-gray-900">No Telepon</h4>
                    <p class="text-sm font-medium text-gray-700">031-8677604</p>
                </div>
                <div data-aos="fade-up" data-aos-delay="400" data-aos-duration="800" class="bg-[#dbe4ff] rounded-2xl p-8 text-left shadow-lg text-gray-800">
                    <svg class="w-10 h-10 mb-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="font-bold text-xl mb-1 text-gray-900">Jam Operasional</h4>
                    <p class="text-sm font-medium text-gray-700">07.30 - 16.00 WIB</p>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="bg-[#c3d4fb] text-gray-600 text-center py-4 text-xs font-bold relative z-10 w-full tracking-wider">
            Kantor Otoritas Bandar Udara Wilayah III Juanda
        </div>
    </div>

    <!-- Script Inisialisasi AOS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                once: true,
                offset: 120,
            });
        });
    </script>
</x-app-layout>