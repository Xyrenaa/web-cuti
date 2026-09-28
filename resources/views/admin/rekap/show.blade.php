<x-admin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('admin.rekap.index') }}" class="hover:underline">Beranda / Rekap Cuti</a> / Detail Pegawai
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">Detail Rekap: {{ $pegawai->nama }}</h2>
    </x-slot>

    <div class="pt-8 pb-12" x-data="{ openEditModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Identitas + pilih tahun --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold text-xl uppercase">{{ substr($pegawai->nama, 0, 1) }}</div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">{{ $pegawai->nama }}</h3>
                        <p class="text-sm font-mono text-gray-500">{{ $pegawai->nip }}</p>
                        <span class="inline-block mt-1 bg-blue-50 text-blue-700 px-3 py-0.5 rounded-full text-xs font-bold">{{ $pegawai->divisi }}</span>
                    </div>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-600">Tahun</label>
                    <select name="tahun" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm py-2 pr-8">
                        @foreach($daftarTahun as $t)
                            <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Saldo ala Excel (hanya tahun berjalan, karena jatah disimpan per tahun berjalan) --}}
            @if($tahun == date('Y'))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 pt-5 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Saldo Cuti Tahunan {{ $tahun }}</h3>
                    <button type="button" @click="openEditModal = true" class="px-4 py-2 border-2 border-[#2a64f5] text-[#2a64f5] rounded-xl font-bold text-sm hover:bg-blue-50 transition">Koreksi Kuota</button>
                </div>
                <div class="overflow-x-auto p-6">
                    <table class="w-full text-center text-sm border border-gray-200">
                        <thead>
                            <tr class="text-[11px] font-bold uppercase text-gray-700">
                                <th colspan="3" class="bg-yellow-100 border border-gray-200 py-2">Sisa Tahun {{ $tahun - 1 }}</th>
                                <th colspan="3" class="bg-amber-50 border border-gray-200 py-2">Jatah Tahun {{ $tahun }}</th>
                                <th colspan="2" class="bg-yellow-200 border border-gray-200 py-2">Ringkasan</th>
                            </tr>
                            <tr class="text-[11px] font-bold uppercase text-gray-600 bg-gray-50">
                                <th class="border border-gray-200 py-2">Dibawa</th><th class="border border-gray-200">Terpakai</th><th class="border border-gray-200">Sisa</th>
                                <th class="border border-gray-200">Jatah</th><th class="border border-gray-200">Terpakai</th><th class="border border-gray-200">Sisa</th>
                                <th class="border border-gray-200">Total Terpakai</th><th class="border border-gray-200">Total Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-base font-semibold text-gray-800">
                                <td class="border border-gray-200 py-3">{{ $saldo['saldo_lalu'] }}</td>
                                <td class="border border-gray-200 text-red-600">{{ $saldo['dipakai_lalu'] ?: '-' }}</td>
                                <td class="border border-gray-200 text-blue-600 font-bold">{{ $saldo['sisa_lalu'] }}</td>
                                <td class="border border-gray-200">{{ $saldo['jatah_berjalan'] }}</td>
                                <td class="border border-gray-200 text-red-600">{{ $saldo['dipakai_berjalan'] ?: '-' }}</td>
                                <td class="border border-gray-200 text-green-600 font-bold">{{ $saldo['sisa_berjalan'] }}</td>
                                <td class="border border-gray-200 text-red-600">{{ $saldo['terpakai'] }}</td>
                                <td class="border border-gray-200 text-green-700 font-black bg-green-50">{{ $saldo['total_sisa'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if($user->koreksi_terpakai != 0)
                        <p class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                            Termasuk penyesuaian {{ $user->koreksi_terpakai > 0 ? '+' : '' }}{{ $user->koreksi_terpakai }} hari dari sheet JATAH CUTI.
                            Riwayat yang tercatat di sistem: {{ $tercatat }} hari.
                        </p>
                    @endif
                    @if($saldo['cuti_besar'])
                        <p class="mt-3 text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                            Pegawai ini menjalani Cuti Besar pada {{ $tahun }}: hak cuti tahunan tahun berjalan hangus, yang tersedia hanya sisa tahun lalu.
                        </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Riwayat --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 pt-5 pb-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <h3 class="font-bold text-gray-900">Riwayat Cuti {{ $tahun }} <span class="text-gray-400 font-normal text-sm">({{ $riwayats->count() }} pengajuan)</span></h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ringkasJenis as $nama => $r)
                            <span class="bg-gray-50 border border-gray-200 text-gray-600 rounded-full px-3 py-1 text-xs font-semibold">{{ $nama }} · {{ $r['kali'] }}× · {{ $r['hari'] }} hari</span>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold">Kode</th>
                                <th class="px-4 py-3 text-left font-bold">Jenis</th>
                                <th class="px-4 py-3 text-left font-bold">Tanggal Cuti</th>
                                <th class="px-4 py-3 text-center font-bold">Durasi</th>
                                <th class="px-4 py-3 text-left font-bold">Diajukan</th>
                                <th class="px-4 py-3 text-left font-bold">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($riwayats as $c)
                                @php
                                    $badge = match($c->approval_step) {
                                        8       => ['Disetujui',    'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        0       => ['Ditolak',      'bg-red-50 text-red-700 border-red-200'],
                                        10      => ['Dibatalkan',   'bg-gray-100 text-gray-500 border-gray-200'],
                                        9       => ['Perlu Revisi', 'bg-orange-50 text-orange-700 border-orange-200'],
                                        default => ['Menunggu',     'bg-amber-50 text-amber-700 border-amber-200'],
                                    };
                                    $mulai   = \Carbon\Carbon::parse($c->tanggal_mulai);
                                    $selesai = \Carbon\Carbon::parse($c->tanggal_selesai);
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition {{ $c->approval_step == 10 ? 'opacity-60' : '' }}">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $c->kode_pengajuan }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $c->jenisCuti->nama_cuti ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ $mulai->translatedFormat('d M Y') }}@if(!$mulai->isSameDay($selesai)) – {{ $selesai->translatedFormat('d M Y') }}@endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-800">{{ $c->durasi_hari }}</td>
                                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ optional($c->created_at)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3"><span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badge[1] }}">{{ $badge[0] }}</span></td>
                                    <td class="px-6 py-3 text-right"><a href="{{ route('admin.approval.show', $c->id) }}" class="text-[#2a64f5] hover:text-blue-800 font-bold text-xs">Detail</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Belum ada riwayat cuti pada {{ $tahun }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal koreksi kuota --}}
        <div x-show="openEditModal" x-cloak style="display:none" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openEditModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Koreksi Kuota Cuti</h3>
                    <button type="button" @click="openEditModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form id="form-edit-jatah" action="{{ route('admin.rekap.update-jatah-individu', $pegawai->id) }}" method="POST" onsubmit="return konfirmasiEditJatah(event, '{{ addslashes($pegawai->nama) }}')">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jatah total tahun ini (termasuk sisa tahun lalu)</label>
                            <input type="number" name="jatah_cuti" min="0" max="365" required value="{{ $user->jatah_cuti ?? 12 }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Sisa tahun lalu yang dibawa</label>
                            <input type="number" name="saldo_tahun_lalu" min="0" max="24" required value="{{ $user->saldo_tahun_lalu }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Penyesuaian pemakaian (hari, boleh negatif)</label>
                            <input type="number" name="koreksi_terpakai" min="-60" max="60" required value="{{ $user->koreksi_terpakai }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Riwayat tercatat {{ $tercatat }} hari. Angka ini ditambahkan ke pemakaian tercatat.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Alasan koreksi <span class="text-red-500">*</span></label>
                            <input type="text" name="alasan" maxlength="255" required placeholder="Contoh: menyesuaikan data sheet kepegawaian" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tercatat bersama nama admin di log sistem.</p>
                        </div>
                    </div>
                    <button type="submit" class="mt-5 w-full bg-[#2a64f5] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-blue-700 transition">Simpan Koreksi</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), confirmButtonColor: '#2a64f5',
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' } });
        @endif

        function konfirmasiEditJatah(e, nama) {
            e.preventDefault();
            const form = document.getElementById('form-edit-jatah');
            Swal.fire({
                title: 'Simpan koreksi kuota?',
                html: `Kuota <b>${nama}</b> akan diubah: jatah <b>${form.jatah_cuti.value}</b>, dibawa <b>${form.saldo_tahun_lalu.value}</b>, penyesuaian <b>${form.koreksi_terpakai.value}</b>.`,
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Ya, Simpan', confirmButtonColor: '#2a64f5',
                cancelButtonText: 'Batal', reverseButtons: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' }
            }).then((r) => { if (r.isConfirmed) form.submit(); });
            return false;
        }
    </script>
</x-admin-layout>