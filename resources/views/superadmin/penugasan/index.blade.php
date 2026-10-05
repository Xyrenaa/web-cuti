<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Superadmin</div>
        <h2 class="font-bold text-3xl text-white leading-tight">Plt &amp; Penugasan Superadmin</h2>
    </x-slot>

    @php
        $fmt = fn ($d) => \Carbon\Carbon::parse($d)->format('d M Y');
        $warnaStatus = [
            'aktif'     => 'bg-green-100 text-green-700',
            'terjadwal' => 'bg-blue-100 text-blue-700',
            'selesai'   => 'bg-gray-100 text-gray-600',
            'dicabut'   => 'bg-red-100 text-red-700',
            'dialihkan' => 'bg-amber-100 text-amber-800',
        ];
        $flash = [
            ['success', 'bg-green-100 border-green-400 text-green-800'],
            ['warning', 'bg-amber-100 border-amber-400 text-amber-800'],
            ['error',   'bg-red-100 border-red-400 text-red-800'],
        ];
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @foreach($flash as [$kunci, $kelas])
                @if(session($kunci))
                    <div class="border px-4 py-3 rounded {{ $kelas }}">{{ session($kunci) }}</div>
                @endif
            @endforeach

            {{-- PERLU PERHATIAN: pengganti dari penugasan Superadmin yang sedang cuti --}}
            @if($perhatian)
                @foreach($perhatian['pltBerhalangan'] as $r)
                    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="text-sm text-red-900">
                            <strong>{{ $r['penugasan']->pengganti->name }}</strong>
                            ({{ $r['penugasan']->jenis }} {{ $r['penugasan']->nama_jabatan }})
                            sedang cuti sampai {{ $fmt($r['cuti']->tanggal_selesai) }}. Persetujuan cuti bisa tertahan.
                        </div>
                        <a href="{{ route('superadmin.penugasan.tukar.form', $r['penugasan']->id) }}"
                           class="shrink-0 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 text-center">Tukar Pengganti</a>
                    </div>
                @endforeach
            @endif

            {{-- TAB + TOMBOL --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="inline-flex bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
                    <a href="{{ route('superadmin.penugasan.index') }}"
                       class="px-4 py-1.5 rounded-md text-sm font-semibold {{ $tab === 'berlaku' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">Berlaku &amp; Terjadwal</a>
                    <a href="{{ route('superadmin.penugasan.index', ['tab' => 'riwayat']) }}"
                       class="px-4 py-1.5 rounded-md text-sm font-semibold {{ $tab === 'riwayat' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">Riwayat</a>
                </div>
                <a href="{{ route('superadmin.penugasan.create') }}"
                   class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 text-center">+ Tunjuk Plh / Plt</a>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-left">Jabatan</th>
                                <th class="px-4 py-3 text-left">Kepala Definitif</th>
                                <th class="px-4 py-3 text-left">Pengganti</th>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                @if($tab === 'riwayat')<th class="px-4 py-3 text-left">Keterangan</th>@endif
                                @if($tab === 'berlaku')<th class="px-4 py-3"></th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($penugasans as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $p->jenis === 'PLT' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">{{ $p->jenis }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $p->nama_jabatan }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $p->pejabatDefinitif->name ?? 'Lowong' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $p->pengganti->name }}
                                        @if($p->nomor_surat)<div class="text-xs font-normal text-gray-400">No. {{ $p->nomor_surat }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ $p->tanggal_mulai->format('d M Y') }} –
                                        {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d M Y') : 'sampai dicabut' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $warnaStatus[$p->status_efektif] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($p->status_efektif) }}</span>
                                    </td>
                                    @if($tab === 'riwayat')
                                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">{{ $p->keterangan_akhir ?? '-' }}</td>
                                    @endif
                                    @if($tab === 'berlaku')
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('superadmin.penugasan.tukar.form', $p->id) }}" class="text-blue-600 font-semibold hover:underline mr-3">Tukar</a>
                                            <form method="POST" action="{{ route('superadmin.penugasan.cabut', $p->id) }}" class="inline form-cabut"
                                                  data-nama="{{ $p->pengganti->name }}" data-jabatan="{{ $p->nama_jabatan }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="keterangan">
                                                <button class="text-red-600 font-semibold hover:underline">Cabut</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-paginasi :paginator="$penugasans" />
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.form-cabut').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                konfirmasiSubmit(this, {
                    icon: 'warning',
                    title: 'Cabut Penugasan?',
                    html: 'Penugasan <b>' + escHtml(this.dataset.nama) + '</b> sebagai ' + escHtml(this.dataset.jabatan) + ' akan diakhiri sekarang.',
                    input: 'textarea',
                    inputLabel: 'Alasan pencabutan',
                    inputPlaceholder: 'mis. Pejabat definitif sudah dilantik',
                    inputValidator: (v) => !v ? 'Alasan pencabutan wajib diisi.' : undefined,
                    inputName: 'keterangan',
                    confirmButtonText: 'Ya, Cabut',
                    confirmButtonColor: '#dc2626',
                });
            });
        });
    </script>
</x-superadmin-layout>