<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('superadmin.pegawai.index') }}" class="hover:underline">Data Pegawai</a> / Edit
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">{{ $pegawai->name }}</h2>
    </x-slot>

    @php
        $input = 'mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500';
        $label = 'block text-sm font-semibold text-gray-700';
    @endphp

    @php
        $adalahKepala = $pegawai->hasAnyRole(\App\Models\User::ROLE_KEPALA);
        $bisaDigantikan = $pegawai->hasAnyRole(\App\Models\User::ROLE_BISA_DIGANTIKAN);
        $warnaRole = $pegawai->hasRole('Kepala Kantor') ? 'bg-purple-100 text-purple-700'
            : ($adalahKepala ? 'bg-amber-100 text-amber-800'
            : ($pegawai->hasRole('Admin Kepegawaian') ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'));
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            {{-- ===== DATA PEGAWAI ===== --}}
            <form method="POST" action="{{ route('superadmin.pegawai.update', $pegawai->id) }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5"
                  x-data="{ bagian: '{{ old('bagian_bidang_id', $pegawai->bagian_bidang_id) }}', sub: '{{ old('sub_bagian_seksi_id', $pegawai->sub_bagian_seksi_id) }}',
                            subs: @js($bagians->mapWithKeys(fn ($b) => [$b->id => $b->subBagianSeksis->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama])->values()])) }">
                @csrf @method('PUT')

                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $warnaRole }}">
                        {{ $pegawai->roles->pluck('name')->join(', ') ?: '-' }}
                    </span>
                    <span class="text-xs text-gray-400">(role tidak dapat diubah)</span>
                    @if($bisaDigantikan)
                    <a href="{{ route('superadmin.penugasan.create', ['kepala_id' => $pegawai->id]) }}"
                        class="text-xs font-semibold text-blue-600 hover:underline">Atur Plh/Plt →</a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $pegawai->name) }}" required class="{{ $input }}">
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip', $pegawai->nip) }}" class="{{ $input }}">
                        @error('nip')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Email (dipakai untuk login)</label>
                        <input type="email" name="email" value="{{ old('email', $pegawai->email) }}" required class="{{ $input }}">
                        @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="{{ $input }}">
                            <option value="">-</option>
                            <option value="L" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Status Kepegawaian</label>
                        <select name="status_kepegawaian" class="{{ $input }}">
                            <option value="">-</option>
                            <option value="PNS" @selected(old('status_kepegawaian', $pegawai->status_kepegawaian) === 'PNS')>PNS</option>
                            <option value="PPPK" @selected(old('status_kepegawaian', $pegawai->status_kepegawaian) === 'PPPK')>PPPK</option>
                        </select>
                        @error('status_kepegawaian')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    @if($unitTerkunci)
                        <div class="md:col-span-2 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg px-4 py-3">
                            Unit kerja akun kepala dikunci karena menentukan meja approval cuti:
                            <strong>{{ $pegawai->subBagianSeksi->nama ?? $pegawai->bagianBidang->nama ?? 'Seluruh kantor' }}</strong>.
                        </div>
                    @else
                        <div>
                            <label class="{{ $label }}">Bagian / Bidang</label>
                            <select name="bagian_bidang_id" x-model="bagian" @change="sub = ''" class="{{ $input }}">
                                <option value="">-</option>
                                @foreach($bagians as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama }}</option>
                                @endforeach
                            </select>
                            @error('bagian_bidang_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Sub Bagian / Seksi</label>
                            <select name="sub_bagian_seksi_id" x-model="sub" class="{{ $input }}">
                                <option value="">-</option>
                                <template x-for="s in (subs[bagian] || [])" :key="s.id">
                                    <option :value="s.id" x-text="s.nama" :selected="String(s.id) === String(sub)"></option>
                                </template>
                            </select>
                            @error('sub_bagian_seksi_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>

                <div class="pt-2">
                    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>

            {{-- ===== RESET PASSWORD ===== --}}
            <form method="POST" action="{{ route('superadmin.pegawai.password', $pegawai->id) }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5"
                  x-data="{ pw: '', buatAcak() {
                      const k = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                      const a = new Uint32Array(10); crypto.getRandomValues(a);
                      this.pw = Array.from(a, n => k[n % k.length]).join('');
                  } }">
                @csrf @method('PUT')

                <div>
                    <h3 class="text-lg font-bold text-gray-800">Reset Password</h3>
                    <p class="text-sm text-gray-500">Password lama tidak ditampilkan (tersimpan terenkripsi). Isi password baru lalu sampaikan ke pegawai.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Password Baru</label>
                        <input type="text" name="password" x-model="pw" autocomplete="off" required minlength="8" class="{{ $input }}">
                        @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Ulangi Password Baru</label>
                        <input type="text" name="password_confirmation" x-model="pw" autocomplete="off" required minlength="8" class="{{ $input }}">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="buatAcak()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">Buat Acak</button>
                    <button class="bg-red-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-700"
                            onclick="return confirm('Reset password {{ addslashes($pegawai->name) }}?')">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</x-superadmin-layout>