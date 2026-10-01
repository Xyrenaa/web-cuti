<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('superadmin.penugasan.index') }}" class="hover:underline">Plh &amp; Plt</a> / Tunjuk
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">Tunjuk Plh / Plt</h2>
    </x-slot>

    @php
        $input = 'mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500';
        $label = 'block text-sm font-semibold text-gray-700';
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('superadmin.penugasan.store') }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5"
                  x-data="{
                      jenis: '{{ old('jenis', 'PLH') }}',
                      kepala: '{{ old('kepala_id', request('kepala_id')) }}',
                      pengganti: '{{ old('pengganti_id') }}',
                      mulai: '{{ old('tanggal_mulai') }}',
                      selesai: '{{ old('tanggal_selesai') }}',
                      pegawais: @js($pegawais),
                      rekomendasi: @js($rekomendasi),
                      cuti: @js($cuti),
                      init() { if (this.kepala && !this.mulai) { this.isiTanggal(); } },
                      isiTanggal() {
                          const c = this.cuti[this.kepala];
                          if (c && this.jenis === 'PLH') { this.mulai = c.mulai; this.selesai = c.selesai; }
                      },
                      pilihKepala() { this.pengganti = ''; this.isiTanggal(); },
                      get opsi() {
                          const rek = this.rekomendasi[this.kepala] || [];
                          return this.pegawais
                              .filter(p => String(p.id) !== String(this.kepala))
                              .map(p => ({ id: p.id, nama: p.name, rek: rek.includes(p.id) }))
                              .sort((a, b) => (b.rek - a.rek) || a.nama.localeCompare(b.nama));
                      }
                  }">
                @csrf

                {{-- Jenis --}}
                <div>
                    <span class="{{ $label }}">Jenis Penugasan</span>
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex gap-3 p-4 border rounded-lg cursor-pointer" :class="jenis === 'PLH' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" name="jenis" value="PLH" x-model="jenis" @change="isiTanggal()" class="mt-1">
                            <span><strong class="text-sm">Plh — Pelaksana Harian</strong>
                                <span class="block text-xs text-gray-500">Kepala berhalangan sementara (mis. cuti). Ada tanggal selesai.</span></span>
                        </label>
                        <label class="flex gap-3 p-4 border rounded-lg cursor-pointer" :class="jenis === 'PLT' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                            <input type="radio" name="jenis" value="PLT" x-model="jenis" class="mt-1">
                            <span><strong class="text-sm">Plt — Pelaksana Tugas</strong>
                                <span class="block text-xs text-gray-500">Jabatan lowong / berhalangan tetap. Berlaku sampai dicabut.</span></span>
                        </label>
                    </div>
                    @error('jenis')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Jabatan --}}
                <div>
                    <label class="{{ $label }}">Jabatan yang digantikan</label>
                    <select name="kepala_id" x-model="kepala" @change="pilihKepala()" required class="{{ $input }}">
                        <option value="">-- Pilih jabatan --</option>
                        @foreach($opsiKepala as $k)
                            <option value="{{ $k['id'] }}">{{ $k['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Kepala Kantor tidak termasuk karena tidak memakai Plh/Plt.</p>
                    @error('kepala_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Pengganti --}}
                <div>
                    <label class="{{ $label }}">Pegawai Pengganti</label>
                    <select name="pengganti_id" x-model="pengganti" required class="{{ $input }}">
                        <option value="">-- Pilih pengganti --</option>
                        <template x-for="o in opsi" :key="o.id">
                            <option :value="o.id" x-text="(o.rek ? '★ ' : '') + o.nama" :selected="String(o.id) === String(pengganti)"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">★ = pegawai yang langsung berada di bawah kepala tersebut (ditampilkan paling atas).</p>
                    @error('pengganti_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Periode --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="mulai" required class="{{ $input }}">
                        @error('tanggal_mulai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Tanggal Selesai <span class="font-normal text-gray-400" x-text="jenis === 'PLT' ? '(opsional)' : ''"></span></label>
                        <input type="date" name="tanggal_selesai" x-model="selesai" :required="jenis === 'PLH'" class="{{ $input }}">
                        @error('tanggal_selesai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="text-xs text-blue-600" x-show="jenis === 'PLH' && kepala && cuti[kepala]" x-cloak>
                    Tanggal diisi otomatis dari cuti kepala yang sudah disetujui. Silakan ubah bila perlu.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nomor Surat Perintah <span class="font-normal text-gray-400">(opsional)</span></label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Alasan / Catatan <span class="font-normal text-gray-400">(opsional)</span></label>
                        <input type="text" name="alasan" value="{{ old('alasan') }}" class="{{ $input }}">
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Penugasan</button>
                    <a href="{{ route('superadmin.penugasan.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-superadmin-layout>