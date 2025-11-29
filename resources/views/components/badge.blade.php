@props(['color' => 'gray', 'text' => ''])

@php
    $classes = match($color) {
        'green' => 'bg-green-100 text-green-800 border-green-200',
        'red' => 'bg-red-100 text-red-800 border-red-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'blue' => 'bg-blue-100 text-blue-800 border-blue-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
        'pink' => 'bg-pink-100 text-pink-800 border-pink-200',
        'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
        default => 'bg-gray-100 text-gray-800 border-gray-200',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $classes }}">
    {{ $text ?: $slot }}
</span>
