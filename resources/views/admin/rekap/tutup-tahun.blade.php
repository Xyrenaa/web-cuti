<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('admin.rekap.index') }}" class="hover:underline">Beranda / Rekap Cuti</a> / Tutup Tahun
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">
            Tutup Tahun {{ $tahunDitutup }} → Siapkan Jatah {{ $tahunBaru }}
        </h2>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Pilih Tahun -->
            <form method="GET" action="{{ route('admin.rekap.tutup-tahun.preview') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">Tahun yang Ditutup</label>
                    <input type="number" name="tahun" value="{{ $tahunDitutup }}" min="2020" max="2100" class="rounded-xl border-gray-200 shadow-sm focus:border-blue-500 text-sm py-2.5">
                </div>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl transition text-sm">
                    Lihat Pratinjau
                </button>
            </form>

            @if($tahunBelumBerakhir)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 text-sm font-semibold flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Tahun {{ $tahunDitutup }} belum berakhir. Angka "Cuti Terpakai" di bawah baru mencerminkan pemakaian sampai hari ini, bukan angka final setahun penuh — sebaiknya jalankan fitur ini setelah 31 Desember {{ $tahunDitutup }}.
                </div>
            @endif

            @if($sudahDitutup)
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 text-sm font-semibold flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tahun {{ $tahunDitutup }} <b>sudah pernah ditutup</b> pada {{ $sudahDitutup->created_at->translatedFormat('d M Y, H:i') }} oleh {{ $sudahDitutup->admin->name ?? '-' }}, mempengaruhi {{ $sudahDitutup->jumlah_pegawai }} pegawai. Menjalankan ulang akan menimpa lagi jatah cuti yang sudah diproses sebelumnya.
                </div>
            @endif

            <!-- Ringkasan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <span class="text-sm font-semibold text-gray-600">Total Pegawai Diproses</span>
                    <div class="text-3xl font-bold text-gray-900 mt-2">{{ $hasil->count() }}</div>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <span class="text-sm font-semibold text-gray-600">Total Hari yang Dibawa ke {{ $tahunBaru }}</span>
                    <div class="text-3xl font-bold text-blue-600 mt-2">{{ $hasil->sum('sisa_dibawa') }} Hari</div>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <span class="text-sm font-semibold text-gray-600">Rata-rata Jatah {{ $tahunBaru }}</span>
                    <div class="text-3xl font-bold text-emerald-600 mt-2">{{ $hasil->count() ? round($hasil->avg('jatah_baru'), 1) : 0 }} Hari</div>
                </div>
            </div>

            <!-- Tabel Pratinjau -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-lg text-gray-900 mb-4">Pratinjau Perhitungan (belum disimpan)</h3>
                <div class="overflow-x-auto border border-gray-100 rounded-xl max-h-[600px] overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/80 text-gray-600 text-xs font-bold border-b border-gray-100 uppercase tracking-wider sticky top-0">
                            <tr>
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">NIP</th>
                                <th class="px-6 py-4 text-center">Jatah {{ $tahunDitutup }}</th>
                                <th class="px-6 py-4 text-center">Terpakai {{ $tahunDitutup }}</th>
                                <th class="px-6 py-4 text-center">Sisa Dibawa (maks 6)</th>
                                <th class="px-6 py-4 text-center">Jatah Baru {{ $tahunBaru }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($hasil as $baris)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-3 font-semibold text-gray-900">{{ $baris->nama }}</td>
                                <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ $baris->nip }}</td>
                                <td class="px-6 py-3 text-center text-gray-500">{{ $baris->jatah_saat_ini }}</td>
                                <td class="px-6 py-3 text-center text-red-500 font-semibold">{{ $baris->terpakai }}</td>
                                <td class="px-6 py-3 text-center text-blue-600 font-semibold">{{ $baris->sisa_dibawa }}</td>
                                <td class="px-6 py-3 text-center text-emerald-600 font-bold">{{ $baris->jatah_baru }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Konfirmasi Eksekusi -->
            <form id="form-tutup-tahun" method="POST" action="{{ route('admin.rekap.tutup-tahun.proses') }}" onsubmit="return konfirmasiTutupTahun(event)" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahunDitutup }}">
                <label class="flex items-start gap-3 text-sm text-gray-700 mb-4">
                    <input type="checkbox" name="konfirmasi" required class="mt-1">
                    Saya sudah memeriksa tabel pratinjau di atas dan memahami bahwa jatah cuti {{ $hasil->count() }} pegawai akan ditimpa untuk tahun {{ $tahunBaru }}.
                </label>
                <button type="submit" class="bg-[#2a64f5] hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition shadow-sm text-sm">
                    Proses Tutup Tahun {{ $tahunDitutup }} Sekarang
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiTutupTahun(e) {
            e.preventDefault();
            const form = document.getElementById('form-tutup-tahun');
            Swal.fire({
                title: 'Yakin Proses Tutup Tahun {{ $tahunDitutup }}?',
                html: 'Tindakan ini akan mengubah <b>jatah_cuti</b> {{ $hasil->count() }} pegawai sekaligus, dan <b>tidak bisa dibatalkan otomatis</b>.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses Sekarang',
                confirmButtonColor: '#dc2626',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' }
            }).then((result) => { if (result.isConfirmed) form.submit(); });
            return false;
        }
    </script>
</x-admin-layout>