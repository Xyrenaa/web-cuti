<x-app-layout>
    <!-- Header Biru -->
    <div class="bg-[#2A65F3] w-full py-6 px-4 sm:px-6 lg:px-24 shadow-md relative z-20">
        <div class="text-blue-100 text-sm mb-1 font-medium tracking-wide">Beranda / Pengajuan Cuti</div>
        <h1 class="text-white text-3xl font-bold">Pengajuan Cuti</h1>
    </div>

    <!-- Notifikasi Sukses -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-6 max-w-6xl mx-auto rounded shadow-sm" role="alert">
        <p class="font-bold">Berhasil!</p>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <!-- Wrapper Konten Utama (Berisi Watermark) -->
    <div class="relative min-h-screen bg-[#F8FAFC] py-12 px-4 sm:px-6 lg:px-24 overflow-hidden">
        
        <!-- Watermark Logo Otban (Faint Background) - Z-index di 0 -->
        <div class="absolute inset-0 flex justify-center items-center pointer-events-none opacity-[0.05] z-0">
            <img src="{{ asset('img/Logo Otban.png') }}" class="w-full max-w-3xl object-contain" alt="Watermark" />
        </div>

        <!-- ==========================================
             SECTION 1: RIWAYAT PENGAJUAN (SINGKAT)
             ========================================== -->
        <h2 class="text-3xl font-extrabold text-center text-gray-800 mb-8 relative z-10">Riwayat Pengajuan</h2>
        
        <!-- Perhatikan bg-white/80 backdrop-blur-md agar transparan! -->
        <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-sm border border-gray-100 overflow-hidden relative z-10 mb-16 max-w-6xl mx-auto">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4 font-semibold">No</th>
                            <th class="px-6 py-4 font-semibold">Tanggal Ajuan</th>
                            <th class="px-6 py-4 font-semibold">Jenis Cuti</th>
                            <th class="px-6 py-4 font-semibold">Durasi</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse ($riwayat as $index => $item)
                        <tr class="hover:bg-blue-50/50 transition">
                            <td class="px-6 py-4">{{ $riwayat->firstItem() + $index }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->jenisCuti->nama_cuti ?? 'Cuti' }}</td>
                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai)) + 1 }} Hari
                            </td>
                            <td class="px-6 py-4">
                                @if($item->status_pengajuan == 'Menunggu Kepala Seksi')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Menunggu</span>
                                @elseif($item->status_pengajuan == 'Disetujui')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Disetujui</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">{{ $item->status_pengajuan }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengajuan.show', $item->id) }}" class="text-[#2A65F3] font-semibold hover:underline">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                                Belum ada riwayat pengajuan cuti.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $riwayat->links() }}
            </div>
        </div>

        <!-- ==========================================
             SECTION 2: FORMULIR PENGAJUAN
             ========================================== -->
        <!-- DETAIL PENGAJUAN -->
         <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
                <h3 class="text-[#2A65F3] text-sm font-bold tracking-widest uppercase mb-4">Detail Pengajuan Cuti</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Cuti <span class="text-red-500">*</span></label>
                        <select name="jenis_cuti_id" required class="w-full bg-white/70 border border-gray-300 rounded-md px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="" disabled selected>Pilih Jenis Cuti</option>
                            @foreach($jenisCutis as $jc)
                                <option value="{{ $jc->id }}">{{ $jc->nama_cuti }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" required class="w-full bg-white/70 border border-gray-300 rounded-md px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_selesai" required class="w-full bg-white/70 border border-gray-300 rounded-md px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <!-- 1. KOLOM LOKASI KETIKA CUTI -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Selama Cuti <span class="text-red-500">*</span></label>
                        <input type="text" name="lokasi" required placeholder="Contoh: Surabaya, Jawa Timur" class="w-full bg-white/70 border border-gray-300 rounded-md px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Cuti <span class="text-red-500">*</span></label>
                    <textarea name="alasan" rows="3" required placeholder="Tuliskan alasan lengkap pengajuan cuti Anda..." class="w-full bg-white/70 border border-gray-300 rounded-md px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <hr class="border-gray-200 mb-6">
                <h3 class="text-[#2A65F3] text-sm font-bold tracking-widest uppercase mb-4">Dokumen Lampiran</h3>

                <!-- 2 & 3. DESAIN UPLOAD KECIL & TERPISAH (WAJIB & OPSIONAL) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    
                    <!-- KOTAK UPLOAD WAJIB (SURAT PENGAJUAN) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Surat Pengajuan (Wajib) <span class="text-red-500">*</span></label>
                        <!-- Tombol Upload Kecil -->
                        <button type="button" onclick="document.getElementById('surat_pengajuan').click()" class="px-4 py-2 bg-blue-50 border border-blue-200 text-[#2A65F3] rounded-md font-semibold text-sm hover:bg-blue-100 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Pilih Dokumen Word
                        </button>
                        <input type="file" id="surat_pengajuan" name="surat_pengajuan" required accept=".doc,.docx" class="hidden" onchange="handleFileUpload(this, 'surat-list', 'surat')">
                        <p class="text-xs text-gray-500 mt-2">Maksimal 5MB. Format: .doc, .docx</p>

                        <!-- List Item Render (Muncul jika ada file dipilih) -->
                        <div id="surat-list" class="mt-3 hidden items-center justify-between p-3 bg-white border border-blue-200 rounded-lg shadow-sm">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <svg class="w-6 h-6 text-[#2A65F3] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span id="surat-filename" class="text-sm font-semibold text-gray-800 truncate"></span>
                            </div>
                            <button type="button" onclick="removeFile('surat_pengajuan', 'surat-list')" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition" title="Hapus">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Pendukung (Opsional)</label>
                        <!-- Tombol Upload -->
                        <button type="button" onclick="document.getElementById('bukti_pendukung').click()" class="px-4 py-2 bg-gray-50 border border-gray-300 text-gray-700 rounded-md font-semibold text-sm hover:bg-gray-100 transition flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Pilih Dokumen (Maks. 5)
                        </button>
                        
                        <!-- PERHATIKAN: name="bukti_pendukung[]" dan ada atribut "multiple" -->
                        <input type="file" id="bukti_pendukung" name="bukti_pendukung[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="hidden" onchange="handleMultipleFiles(this)">
                        
                        <p class="text-xs text-gray-500 mt-2">Bisa pilih lebih dari 1 file (Maks 5). Format: PDF, JPG, PNG, DOCX.</p>

                        <!-- Wadah untuk memunculkan list file yang dipilih -->
                        <div id="bukti-list-container" class="mt-3 hidden flex-col gap-2">
                            <!-- Javascript akan merender daftar filenya di sini -->
                        </div>
                    </div>

                </div>

                <!-- 4. ACTION BUTTONS (DENGAN FUNGSI BATAL DESTRUKTIF) -->
                <div class="flex justify-end gap-4 border-t border-gray-200 pt-6">
                    <button type="button" onclick="konfirmasiBatal()" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-md hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#2A65F3] border border-transparent text-white font-semibold rounded-md hover:bg-blue-700 transition shadow-sm">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>

    <!-- Script untuk mengganti nama teks upload ketika file dipilih -->
  <!-- Script Interaktif UI -->
    <!-- Script Interaktif UI -->
    <script>
        // --- 1. Logika Surat Pengajuan (Single File) ---
        function handleFileUpload(input, listId, type) {
            const listContainer = document.getElementById(listId);
            const fileNameDisplay = document.getElementById(type + '-filename');

            if (input.files && input.files.length > 0) {
                listContainer.classList.remove('hidden');
                listContainer.classList.add('flex');
                fileNameDisplay.textContent = input.files[0].name;
            } else {
                listContainer.classList.add('hidden');
                listContainer.classList.remove('flex');
            }
        }

        function removeFile(inputId, listId) {
            document.getElementById(inputId).value = ''; 
            const listContainer = document.getElementById(listId);
            listContainer.classList.add('hidden');
            listContainer.classList.remove('flex');
        }

        // --- 2. Logika Bukti Pendukung (Multiple Files Maks 5) ---
        let buktiFiles = new DataTransfer(); 

        function handleMultipleFiles(input) {
            if (!input.files || input.files.length === 0) return;

            for (let i = 0; i < input.files.length; i++) {
                let isDuplicate = false;
                for (let j = 0; j < buktiFiles.files.length; j++) {
                    if (buktiFiles.files[j].name === input.files[i].name) {
                        isDuplicate = true;
                        break;
                    }
                }
                
                if (!isDuplicate) {
                    buktiFiles.items.add(input.files[i]);
                }
            }
            
            if (buktiFiles.files.length > 5) {
                alert("Maksimal hanya boleh melampirkan 5 file bukti pendukung.");
                const temp = new DataTransfer();
                for(let k = 0; k < 5; k++){
                    temp.items.add(buktiFiles.files[k]);
                }
                buktiFiles = temp; 
            }

            renderMultiFileList();
        }

        function renderMultiFileList() {
            const container = document.getElementById('bukti-list-container');
            container.innerHTML = ''; 
            
            if (buktiFiles.files.length > 0) {
                container.classList.remove('hidden');
                container.classList.add('flex');
                
                Array.from(buktiFiles.files).forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = "flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm";
                    item.innerHTML = `
                        <div class="flex items-center gap-3 overflow-hidden">
                            <svg class="w-6 h-6 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span class="text-sm font-semibold text-gray-800 truncate">${file.name}</span>
                        </div>
                        <button type="button" onclick="removeSpecificFile(${index})" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition" title="Hapus File Ini">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    `;
                    container.appendChild(item);
                });
            } else {
                container.classList.add('hidden');
                container.classList.remove('flex');
            }
            
            document.getElementById('bukti_pendukung').files = buktiFiles.files;
        }

        function removeSpecificFile(indexToRemove) {
            const tempTransfer = new DataTransfer();
            Array.from(buktiFiles.files).forEach((file, index) => {
                if (index !== indexToRemove) {
                    tempTransfer.items.add(file);
                }
            });
            buktiFiles = tempTransfer;
            renderMultiFileList(); 
        }

        // --- 3. Logika Pop-up Batal ---
        function konfirmasiBatal() {
            if (confirm("⚠ PERINGATAN!\n\nApakah Anda yakin ingin membatalkan pengajuan ini?\nSemua data yang telah Anda ketik dan file yang diunggah akan dihapus secara permanen.")) {
                window.location.href = "{{ route('dashboard') }}";
            }
        }
    </script>
</x-app-layout>