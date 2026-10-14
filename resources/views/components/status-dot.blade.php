@props(['status' => 'menunggu'])

@php
    $s = strtolower((string) $status);
    $warna = match (true) {
        str_contains($s, 'disetujui')  => 'bg-emerald-500/10 text-emerald-700',
        str_contains($s, 'ditolak')    => 'bg-red-500/10 text-red-700',
        str_contains($s, 'dibatalkan') => 'bg-gray-500/10 text-gray-600',
        str_contains($s, 'revisi')     => 'bg-orange-500/10 text-orange-700',
        default                        => 'bg-amber-500/10 text-amber-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap $warna"]) }}>{{ $slot }}</span>