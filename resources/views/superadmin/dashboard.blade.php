<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Superadmin</div>
        <h2 class="font-bold text-3xl text-white leading-tight">Beranda</h2>
    </x-slot>

    <div class="pt-12 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            <div class="px-2 sm:px-0">
                <h1 class="text-3xl font-bold text-gray-900">Selamat Datang, {{ Auth::user()->name }}</h1>
                <p class="text-base text-gray-500 max-w-2xl leading-relaxed mt-1">
                    Kelola data akun pegawai dan penugasan Pelaksana Harian (Plh) / Pelaksana Tugas (Plt).
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Total Pegawai</p>
                    <p class="text-4xl font-bold text-gray-800 mt-2">{{ $stat['total_pegawai'] }}</p>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Plh Berlaku</p>
                    <p class="text-4xl font-bold text-blue-600 mt-2">{{ $stat['plh_aktif'] }}</p>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Plt Berlaku</p>
                    <p class="text-4xl font-bold text-indigo-600 mt-2">{{ $stat['plt_aktif'] }}</p>
                </div>
                <a href="{{ route('superadmin.plh.index') }}"
                   class="rounded-xl p-6 border shadow-sm transition hover:shadow-md {{ $stat['perlu_perhatian'] > 0 ? 'bg-amber-50 border-amber-300' : 'bg-white border-gray-200' }}">
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Perlu Perhatian</p>
                    <p class="text-4xl font-bold mt-2 {{ $stat['perlu_perhatian'] > 0 ? 'text-amber-600' : 'text-gray-300' }}">{{ $stat['perlu_perhatian'] }}</p>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <a href="{{ route('superadmin.plh.index') }}"
                   class="block bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition">
                    <h3 class="text-lg font-bold text-gray-800">Plh dari Kepala</h3>
                    <p class="text-sm text-gray-500 mt-1">Lihat PLH yang ditunjuk kepala, dan tukar bila PLH berhalangan.</p>
                </a>
                <a href="{{ route('superadmin.penugasan.index') }}"
                   class="block bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition">
                    <h3 class="text-lg font-bold text-gray-800">Plt</h3>
                    <p class="text-sm text-gray-500 mt-1">Tunjuk Pelaksana Tugas untuk jabatan yang lowong.</p>
                </a>
            </div>
        </div>
    </div>
</x-superadmin-layout>