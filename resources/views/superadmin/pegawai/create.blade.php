<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('superadmin.pegawai.index') }}" class="hover:underline">Data Pegawai</a> / Tambah
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">Tambah Pegawai Baru</h2>
    </x-slot>

    @php
        $input = 'mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500';
        $label = 'block text-sm font-semibold text-gray-700';
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-blue-50 border border-blue-200 text-blue-900 text-sm rounded-xl px-5 py-4 leading-relaxed">
                Akun dibuat dengan <strong>kata sandi awal password123</strong> dan email sementara
                <strong>&lt;NIP&gt;@otban3.com</strong>. Saat login pertama, pegawai otomatis diminta mengisi
                email aktif dan mengganti kata sandi. Role otomatis <strong>Pegawai</strong>.
            </div>

            <form id="form-tambah" method="POST" action="{{ route('superadmin.pegawai.store') }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5"
                  x-data="{
                      nip: '{{ old('nip') }}',
                      bagian: '{{ old('bagian_bidang_id') }}',
                      sub: '{{ old('sub_bagian_seksi_id') }}',
                      subs: @js($bagians->mapWithKeys(fn ($b) => [$b->id => $b->subBagianSeksis->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama])->values()]))
                  }">
                @csrf

                <div>
                    <label class="{{ $label }}">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus class="{{ $input }}">
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $label }}">NIP</label>
                    <input type="text" name="nip" x-model="nip" inputmode="numeric" required
                           @input="nip = nip.replace(/\D/g, '').slice(0, 18)"
                           placeholder="18 digit angka" class="{{ $input }}">
                    <p class="text-xs text-gray-400 mt-1"><span x-text="nip.length"></span>/18 digit</p>
                    @error('nip')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Bagian / Bidang</label>
                        <select name="bagian_bidang_id" x-model="bagian" @change="sub = ''" required class="{{ $input }}">
                            <option value="">-- Pilih --</option>
                            @foreach($bagians as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                        @error('bagian_bidang_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Sub Bagian / Seksi</label>
                        <select name="sub_bagian_seksi_id" x-model="sub" required class="{{ $input }}">
                            <option value="">-- Pilih --</option>
                            <template x-for="s in (subs[bagian] || [])" :key="s.id">
                                <option :value="s.id" x-text="s.nama" :selected="String(s.id) === String(sub)"></option>
                            </template>
                        </select>
                        @error('sub_bagian_seksi_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">Tambah Pegawai</button>
                    <a href="{{ route('superadmin.pegawai.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('form-tambah').addEventListener('submit', function (e) {
            // Cadangan: kalau helper popup tidak termuat, form dikirim biasa.
            if (typeof konfirmasiSubmit !== 'function' || typeof Swal === 'undefined') {
                return;
            }

            e.preventDefault();

            const sub  = this.elements['sub_bagian_seksi_id'];
            const unit = sub.selectedOptions[0] ? sub.selectedOptions[0].text : '';

            konfirmasiSubmit(this, {
                icon: 'question',
                title: 'Tambah Pegawai Baru?',
                html: 'Akun <b>' + escHtml(this.elements['name'].value.trim()) + '</b> (NIP ' + escHtml(this.elements['nip'].value)
                    + ') di <b>' + escHtml(unit) + '</b> akan dibuat dengan kata sandi awal <b>password123</b>.',
                confirmButtonText: 'Ya, Tambahkan',
            });
        });
    </script>
</x-superadmin-layout>