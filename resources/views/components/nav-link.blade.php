@props(['active'])

@php
$classes = ($active ?? false)
    ? 'text-[#2A65F3] after:bg-[#2A65F3]'
    : 'text-gray-500 hover:text-gray-900 after:bg-transparent hover:after:bg-gray-300';
@endphp

<a {{ $attributes->merge(['class' => "relative inline-flex items-center py-1 text-sm font-semibold transition-colors duration-200 after:absolute after:inset-x-0 after:-bottom-1 after:h-0.5 after:rounded-full after:transition-colors after:duration-200 $classes"]) }}>
    {{ $slot }}
</a>