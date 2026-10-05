<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Superadmin</div>
        <h2 class="font-bold text-3xl text-white leading-tight">Plh dari Kepala</h2>
    </x-slot>

    @php
        $fmt = fn ($d) => \Carbon\Carbon::parse($d)->format('d M Y');
        $badge = [
            'belum_ditunjuk' => ['Belum ditunjuk', 'bg-red-100 text-red-700'],
            'menunggu_final' => ['Menunggu finalisasi Admin', 'bg-blue-100 text-blue-700'],
            'terjadwal'      => ['Terjadwal', 'bg-blue-100 text-blue-700'],
            'aktif'          => ['Aktif', 'bg-green-100 text-green-700'],
            'selesai'        => ['Selesai', 'bg-gray-100 text-gray-600'],
        ];
        $tabs = ['berjalan' => 'Berjalan & Akan Datang', 'selesai' => 'Selesai', 'penukaran' => 'Riwayat Penukaran'];
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

            <div class="bg-blue-50 border border-blue-200 text-blue-900 text-sm rounded-xl px-5 py-3">
                PLH ditunjuk oleh <strong>kepala sendiri</strong> pada pengajuan cutinya. Superadmin hanya
                <strong>menukar</strong> bila PLH tersebut ternyata berhalangan.
            </div>

                        @if($perhatian)
                @foreach($perhatian['tanpaPlh'] as $r)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="text-sm text-amber-900">
                            <strong>{{ $r['kepala']->name }}</strong> ({{ $r['label'] }}) cuti
                            {{ $fmt($r['pengajuan']->tanggal_mulai) }} – {{ $fmt($r['pengajuan']->tanggal_selesai) }}
                            dan <strong>belum menunjuk PLH</strong>.
                        </div>
                        <a href="{{ route('superadmin.plh.tukar.form', $r['pengajuan']->id) }}"
                           class="shrink-0 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-700 text-center">Tetapkan PLH</a>
                    </div>
                @endforeach

                @foreach($perhatian['plhBerhalangan'] as $r)
                    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="text-sm text-red-900">
                            <strong>{{ $r['plh']->name }}</strong> (PLH {{ $r['pengajuan']->user->name }}) memiliki cuti
                            {{ $fmt($r['cuti']->tanggal_mulai) }} – {{ $fmt($r['cuti']->tanggal_selesai) }}
                            yang bertabrakan dengan masa PLH-nya.
                        </div>
                        <a href="{{ route('superadmin.plh.tukar.form', $r['pengajuan']->id) }}"
                           class="shrink-0 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 text-center">Tukar PLH</a>
                    </div>
                @endforeach
            @endif

            <div class="inline-flex bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
                @foreach($tabs as $k => $nama)
                    <a href="{{ route('superadmin.plh.index', $k === 'berjalan' ? [] : ['tab' => $k]) }}"
                       class="px-4 py-1.5 rounded-md text-sm font-semibold {{ $tab === $k ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">{{ $nama }}</a>
                @endforeach
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                @if($tab === 'penukaran')
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Waktu</th>
                                <th class="px-4 py-3 text-left">Kepala</th>
                                <th class="px-4 py-3 text-left">PLH Lama</th>
                                <th class="px-4 py-3 text-left">PLH Baru</th>
                                <th class="px-4 py-3 text-left">Alasan</th>
                                <th class="px-4 py-3 text-left">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($log as $l)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $l->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $l->pengajuan->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $l->lama->name ?? '(belum ada)' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $l->baru->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">{{ $l->alasan }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $l->pelaku->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada penukaran PLH.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <x-paginasi :paginator="$log" />
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Kepala yang Cuti</th>
                                <th class="px-4 py-3 text-left">Periode Cuti</th>
                                <th class="px-4 py-3 text-left">PLH</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                @if($tab === 'berjalan')<th class="px-4 py-3"></th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pengajuans as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800">{{ $p->user->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $p->user->getRoleNames()->first() }} · {{ $p->user->subBagianSeksi->nama ?? $p->user->bagianBidang->nama ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $fmt($p->tanggal_mulai) }} – {{ $fmt($p->tanggal_selesai) }}</td>
                                    <td class="px-4 py-3">
                                        @if($p->plh)
                                            <div class="font-semibold text-gray-800">{{ $p->plh->name }}</div>
                                            @if($p->plh_bentrok)
                                                <div class="text-xs text-red-600 font-semibold">
                                                    Berhalangan: cuti {{ $fmt($p->plh_bentrok->tanggal_mulai) }} – {{ $fmt($p->plh_bentrok->tanggal_selesai) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge[$p->status_plh][1] }}">{{ $badge[$p->status_plh][0] }}</span>
                                    </td>
                                    @if($tab === 'berjalan')
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('superadmin.plh.tukar.form', $p->id) }}" class="text-blue-600 font-semibold hover:underline">
                                                {{ $p->plh ? 'Tukar PLH' : 'Tetapkan PLH' }}
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <x-paginasi :paginator="$pengajuans" />
                @endif
                </div>
            </div>
        </div>
    </div>
</x-superadmin-layout>