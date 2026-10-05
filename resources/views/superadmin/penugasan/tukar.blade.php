<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('superadmin.penugasan.index') }}" class="hover:underline">Plh &amp; Plt</a> / Tukar Pengganti
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">Tukar Pengganti</h2>
    </x-slot>

    @php
        $input = 'mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500';
        $label = 'block text-sm font-semibold text-gray-700';
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mb-3">Penugasan saat ini</p>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-400">Jenis</dt><dd class="font-semibold">{{ $lama->label_jenis }}</dd></div>
                    <div><dt class="text-gray-400">Jabatan</dt><dd class="font-semibold">{{ $lama->nama_jabatan }}</dd></div>
                    <div><dt class="text-gray-400">Kepala definitif</dt><dd class="font-semibold">{{ $lama->pejabatDefinitif->name ?? 'Lowong' }}</dd></div>
                    <div><dt class="text-gray-400">Pengganti saat ini</dt><dd class="font-semibold">{{ $lama->pengganti->name }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-400">Periode</dt>
                        <dd class="font-semibold">{{ $lama->tanggal_mulai->format('d M Y') }} – {{ $lama->tanggal_selesai ? $lama->tanggal_selesai->format('d M Y') : 'sampai dicabut' }}</dd></div>
                </dl>

                @if($cutiPengganti)
                    <div class="mt-4 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
                        {{ $lama->pengganti->name }} sedang cuti
                        ({{ \Carbon\Carbon::parse($cutiPengganti->tanggal_mulai)->format('d M Y') }} – {{ \Carbon\Carbon::parse($cutiPengganti->tanggal_selesai)->format('d M Y') }}).
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('superadmin.penugasan.tukar', $lama->id) }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5">
                @csrf

                <div>
                    <label class="{{ $label }}">Pengganti Baru</label>
                    <select name="pengganti_id" required class="{{ $input }}">
                        <option value="">-- Pilih pengganti --</option>
                        @foreach($opsi as $o)
                            <option value="{{ $o['id'] }}" @selected(old('pengganti_id') == $o['id'])>{{ $o['rek'] ? '★ ' : '' }}{{ $o['nama'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">★ = pegawai yang langsung berada di bawah kepala tersebut.</p>
                    @error('pengganti_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $label }}">Alasan Penukaran</label>
                    <input type="text" name="alasan" value="{{ old('alasan') }}" required
                           placeholder="mis. Pengganti sebelumnya sedang cuti / dinas luar" class="{{ $input }}">
                    @error('alasan')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $label }}">Nomor Surat Perintah Baru <span class="font-normal text-gray-400">(opsional)</span></label>
                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" class="{{ $input }}">
                </div>

                <p class="text-xs text-gray-500">
                    Penugasan lama ditutup dengan status <em>dialihkan</em>, lalu penugasan baru berlaku mulai hari ini
                    dengan tanggal selesai yang sama. Riwayatnya tercatat.
                </p>

                <div class="pt-2 flex items-center gap-3">
                    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">Tukar Pengganti</button>
                    <a href="{{ route('superadmin.penugasan.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-superadmin-layout>