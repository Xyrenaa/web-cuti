<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Pusat Integrasi & Migrasi Data Excel</h2>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- CARD 1: IMPORT PEGAWAI -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-blue-600 mb-2">1. Integrasi Data Pegawai</h3>
                    <p class="text-sm text-gray-500 mb-4">Upload "Data Pegawai.xlsx". Sistem otomatis akan membuatkan akun dengan password "password123". Jangan lupa tambahkan kolom "JENIS KELAMIN" (Isi L atau P) di Excel sebelum upload.</p>
                    
                    <form action="{{ route('admin.migrasi.pegawai') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file_pegawai" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-4" required>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Mulai Import Pegawai</button>
                    </form>
                </div>

                <!-- CARD 2: IMPORT REKAP CUTI -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-orange-600 mb-2">2. Migrasi Riwayat Cuti Lama</h3>
                    <p class="text-sm text-gray-500 mb-4">Upload "REKAP CUTI.xlsx". Pastikan Data Pegawai sudah di-upload terlebih dahulu. Sistem otomatis mendeteksi status "CANCEL" dan mencocokkan nama dengan data pegawai.</p>
                    
                    <form action="{{ route('admin.migrasi.rekap') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file_rekap" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 mb-4" required>
                        <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded shadow hover:bg-orange-700">Migrasi Rekap Cuti</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>