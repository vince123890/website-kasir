@props(['title', 'value', 'icon' => null, 'color' => 'indigo', 'trend' => null, 'trendDirection' => null])

@php
    $colorClasses = match($color) {
        'indigo' => 'bg-indigo-500 text-white',
        'green' => 'bg-green-500 text-white',
        'red' => 'bg-red-500 text-white',
        'yellow' => 'bg-yellow-500 text-white',
        'blue' => 'bg-blue-500 text-white',
        'purple' => 'bg-purple-500 text-white',
        default => 'bg-indigo-500 text-white',
    };
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ $title }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $value }}</p>

                @if($trend)
                    <div class="mt-2 flex items-center text-sm">
                        @if($trendDirection === 'up')
                            <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                            <span class="text-green-600 font-medium">{{ $trend }}</span>
                        @elseif($trendDirection === 'down')
                            <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                            <span class="text-red-600 font-medium">{{ $trend }}</span>
                        @else
                            <span class="text-gray-600">{{ $trend }}</span>
                        @endif
                    </div>
                @endif
            </div>

            @if($icon)
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 {{ $colorClasses }} rounded-lg flex items-center justify-center">
                        {!! $icon !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
