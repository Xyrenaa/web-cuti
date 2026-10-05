<x-superadmin-layout>
    <x-slot name="header">
        <div class="text-blue-100 text-sm mb-1 opacity-80">
            <a href="{{ route('superadmin.plh.index') }}" class="hover:underline">Plh dari Kepala</a> / {{ $pengajuan->plh ? 'Tukar' : 'Tetapkan' }}
        </div>
        <h2 class="font-bold text-3xl text-white leading-tight">{{ $pengajuan->plh ? 'Tukar PLH' : 'Tetapkan PLH' }}</h2>
    </x-slot>

    @php
        $input = 'mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500';
        $label = 'block text-sm font-semibold text-gray-700';
        $fmt = fn ($d) => \Carbon\Carbon::parse($d)->format('d M Y');
    @endphp

    <div class="pt-10 pb-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mb-3">Cuti kepala</p>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-400">Kepala yang cuti</dt><dd class="font-semibold">{{ $pengajuan->user->name }}</dd></div>
                    <div><dt class="text-gray-400">Jabatan</dt>
                        <dd class="font-semibold">{{ $pengajuan->user->getRoleNames()->first() }} · {{ $pengajuan->user->subBagianSeksi->nama ?? $pengajuan->user->bagianBidang->nama ?? '-' }}</dd></div>
                    <div><dt class="text-gray-400">Periode cuti</dt><dd class="font-semibold">{{ $fmt($pengajuan->tanggal_mulai) }} – {{ $fmt($pengajuan->tanggal_selesai) }}</dd></div>
                    <div><dt class="text-gray-400">PLH pilihan kepala saat ini</dt><dd class="font-semibold">{{ $pengajuan->plh->name ?? 'Belum ditunjuk' }}</dd></div>
                </dl>
            </div>

            <form id="form-tukar" method="POST" action="{{ route('superadmin.plh.tukar', $pengajuan->id) }}"
                  class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5">
                @csrf

                <div>
                    <label class="{{ $label }}">PLH Baru</label>
                    <select name="plh_user_id" required class="{{ $input }}">
                        <option value="">-- Pilih pegawai --</option>
                        @foreach($opsi as $o)
                            <option value="{{ $o['id'] }}" @disabled(! $o['tersedia']) @selected(old('plh_user_id') == $o['id'])>
                                {{ $o['rek'] ? '★ ' : '' }}{{ $o['nama'] }}{{ $o['bentrok'] ? ' — ' . $o['bentrok'] : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        ★ = calon yang langsung berada di bawah kepala. Pegawai yang berhalangan pada periode ini ditandai dan tidak bisa dipilih.
                    </p>
                    @error('plh_user_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $label }}">Alasan</label>
                    <input type="text" name="alasan" value="{{ old('alasan') }}" required
                           placeholder="mis. PLH sebelumnya sakit / dinas luar" class="{{ $input }}">
                    @error('alasan')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <p class="text-xs text-gray-500">
                    PLH baru, PLH lama, dan kepala yang cuti akan menerima notifikasi. Penukaran tercatat di riwayat.
                </p>

                <div class="pt-2 flex items-center gap-3">
                    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">
                        {{ $pengajuan->plh ? 'Tukar PLH' : 'Tetapkan PLH' }}
                    </button>
                    <a href="{{ route('superadmin.plh.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('form-tukar').addEventListener('submit', function (e) {
            e.preventDefault();
            const pilihan = this.elements['plh_user_id'].selectedOptions[0];
            const nama = pilihan ? pilihan.text.replace('★ ', '').split(' — ')[0] : '';

            konfirmasiSubmit(this, {
                icon: 'question',
                title: @js($pengajuan->plh ? 'Tukar PLH?' : 'Tetapkan PLH?'),
                html: 'PLH untuk <b>' + escHtml(@js($pengajuan->user->name)) + '</b> akan dipegang oleh <b>' + escHtml(nama) + '</b>.',
                confirmButtonText: 'Ya, Lanjutkan',
            });
        });
    </script>
</x-superadmin-layout>