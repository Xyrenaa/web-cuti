<x-app-layout>
    <div class="bg-[#2A65F3] w-full py-8 px-4 sm:px-6 lg:px-24 shadow-md">
        <div class="text-blue-100 text-sm mb-2">
            <a href="{{ route('pegawai.detail', $pengajuan->id) }}" class="hover:underline">Detail Pengajuan</a> / Perbaiki
        </div>
        <h1 class="text-white text-3xl font-bold">Perbaiki &amp; Ajukan Ulang</h1>
    </div>

    <div class="bg-[#F8FAFC] min-h-screen py-10 px-4 sm:px-6 lg:px-24">
        <div class="max-w-3xl mx-auto space-y-6">
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">{{ session('error') }}</div>
            @endif
            @if($pengajuan->catatan_penolakan)
                <div class="p-4 rounded-lg border bg-amber-50 border-amber-200">
                    <p class="text-xs font-bold uppercase text-amber-700 mb-1">Catatan Revisi dari Atasan</p>
                    <p class="text-sm text-amber-800">{{ $pengajuan->catatan_penolakan }}</p>
                </div>
            @endif

            <form action="{{ route('pengajuan.revisi.kirim', $pengajuan->id) }}" method="POST" enctype="multipart/form-data"
                  class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Cuti</label>
                    <select name="jenis_cuti_id" required class="w-full border-gray-300 rounded-md">
                        @foreach($jenisCutis as $jc)
                            <option value="{{ $jc->id }}" @selected(old('jenis_cuti_id', $pengajuan->jenis_cuti_id) == $jc->id)>{{ $jc->nama_cuti }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" required class="w-full border-gray-300 rounded-md"
                               value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" required class="w-full border-gray-300 rounded-md"
                               value="{{ old('tanggal_selesai', \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('Y-m-d')) }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Selama Cuti</label>
                    <input type="text" name="lokasi" required maxlength="255" class="w-full border-gray-300 rounded-md" value="{{ old('lokasi', $pengajuan->lokasi) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan</label>
                    <textarea name="alasan" rows="3" required class="w-full border-gray-300 rounded-md">{{ old('alasan', $pengajuan->alasan) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Surat Pengajuan</label>
                    <p class="text-xs text-gray-500 mb-2">Saat ini: {{ \App\Models\PengajuanCuti::namaBerkas($pengajuan->surat_pengajuan, $pengajuan->surat_pengajuan_nama) }} (kosongkan jika tidak diganti)</p>
                    <input type="file" name="surat_pengajuan" accept=".doc,.docx,.pdf,.jpg,.jpeg,.png" class="block w-full text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Pendukung</label>
                    <p class="text-xs text-gray-500 mb-2">Memilih file baru menggantikan seluruh bukti lama (maks. 5).</p>
                    <input type="file" name="bukti_pendukung[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full text-sm">
                </div>

                @if($errors->any())
                    <div class="text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('pegawai.detail', $pengajuan->id) }}" class="px-6 py-2.5 border border-gray-300 rounded-md text-gray-700 font-semibold">Kembali</a>
                    <button type="submit" class="px-6 py-2.5 bg-[#2A65F3] text-white font-semibold rounded-md hover:bg-blue-700">Kirim Ulang</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>