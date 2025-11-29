@props(['type' => 'primary', 'href' => null, 'icon' => null])

@php
    $classes = match($type) {
        'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white border-transparent focus:ring-indigo-500',
        'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border-gray-300 focus:ring-indigo-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white border-transparent focus:ring-red-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white border-transparent focus:ring-green-500',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white border-transparent focus:ring-yellow-500',
        default => 'bg-indigo-600 hover:bg-indigo-700 text-white border-transparent focus:ring-indigo-500',
    };

    $baseClasses = 'inline-flex items-center px-4 py-2 border rounded-md font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $classes]) }}>
        @if($icon)
            {!! $icon !!}
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $baseClasses . ' ' . $classes, 'type' => 'button']) }}>
        @if($icon)
            {!! $icon !!}
        @endif
        {{ $slot }}
    </button>
@endif
