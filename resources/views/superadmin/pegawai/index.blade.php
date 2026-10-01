<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">Superadmin</div>
        <h2 class="font-bold text-3xl text-white leading-tight">Data Pegawai</h2>
    </x-slot>

    @php
        $warnaRole = [
            'Kepala Kantor'     => 'bg-purple-100 text-purple-700',
            'Kepala TU'         => 'bg-amber-100 text-amber-800',
            'Kepala Bidang'     => 'bg-amber-100 text-amber-800',
            'Kepala Sub-Bagian' => 'bg-amber-100 text-amber-800',
            'Kepala Seksi'      => 'bg-amber-100 text-amber-800',
            'Admin Kepegawaian' => 'bg-emerald-100 text-emerald-700',
            'Pegawai'           => 'bg-gray-100 text-gray-600',
        ];
        $kelas = 'rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500';
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            {{-- FILTER --}}
            <form method="GET" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-3"
                  x-data="{
                      bagian: '{{ request('bagian') }}',
                      seksi: '{{ request('seksi') }}',
                      subs: @js($bagians->mapWithKeys(fn ($b) => [$b->id => $b->subBagianSeksis->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama])->values()])),
                      get daftarSeksi() {
                          return this.bagian ? (this.subs[this.bagian] || []) : Object.values(this.subs).flat();
                      }
                  }">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIP, atau email..."
                           class="{{ $kelas }} md:col-span-2">
                    <select name="role" class="{{ $kelas }}">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(request('role') === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                    <select name="bagian" x-model="bagian" @change="seksi = ''" class="{{ $kelas }}">
                        <option value="">Semua Bagian/Bidang</option>
                        @foreach($bagians as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col md:flex-row gap-3">
                    <select name="seksi" x-model="seksi" class="{{ $kelas }} flex-1">
                        <option value="">Semua Seksi / Sub-Bagian</option>
                        <template x-for="s in daftarSeksi" :key="s.id">
                            <option :value="s.id" x-text="s.nama" :selected="String(s.id) === String(seksi)"></option>
                        </template>
                    </select>
                    <button class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">Terapkan</button>
                    @if(request()->hasAny(['search', 'role', 'bagian', 'seksi']))
                        <a href="{{ route('superadmin.pegawai.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-800 text-center">Reset</a>
                    @endif
                </div>
            </form>

            {{-- TABEL --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">NIP</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Unit Kerja</th>
                                <th class="px-4 py-3 text-left">Role</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pegawais as $p)
                                @php
                                    $kepala     = $p->hasAnyRole(\App\Models\User::ROLE_KEPALA);
                                    $penugasan  = $menjabat->get($p->id);
                                    $digantikan = $kepala && $p->sedangDigantikan();
                                @endphp
                                <tr class="hover:bg-gray-50 {{ $kepala ? 'bg-amber-50/40' : '' }}">
                                    <td class="px-4 py-3 font-semibold text-gray-800 border-l-4 {{ $kepala ? 'border-amber-400' : 'border-transparent' }}">
                                        {{ $p->name }}
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @if($penugasan)
                                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold">
                                                    {{ $penugasan->jenis }} · {{ $penugasan->jabatan_role }}
                                                </span>
                                            @endif
                                            @if($digantikan)
                                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">Sedang digantikan</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $p->nip ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $p->email }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $p->subBagianSeksi->nama ?? $p->bagianBidang->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @foreach($p->roles as $r)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $warnaRole[$r->name] ?? 'bg-gray-100 text-gray-600' }}">{{ $r->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('superadmin.pegawai.edit', $p->id) }}" class="text-blue-600 font-semibold hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada pegawai ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $pegawais->links() }}
                </div>
            </div>
        </div>
    </div>
</x-superadmin-layout>