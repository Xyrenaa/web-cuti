@props(['paginator'])

{{-- Pagination seragam dengan halaman Admin (approval / rekap) --}}
<div class="p-6 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
    <div>Menampilkan {{ $paginator->firstItem() ?? 0 }} sampai {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</div>

    <div class="flex flex-wrap items-center justify-center gap-2 mt-4 md:mt-0">
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Sebelumnya</a>
        @endif

        @foreach ($paginator->linkCollection()->slice(1, -1) as $link)
            @if ($link['label'] === '...')
                <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">...</span>
            @elseif ($link['active'])
                <span class="px-4 py-2 bg-[#2a64f5] text-white rounded-xl font-bold shadow-md shadow-blue-500/20">{{ $link['label'] }}</span>
            @else
                <a href="{{ $link['url'] }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">{{ $link['label'] }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold text-gray-600 transition">Selanjutnya</a>
        @else
            <span class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 font-bold">Selanjutnya</span>
        @endif
    </div>
</div>