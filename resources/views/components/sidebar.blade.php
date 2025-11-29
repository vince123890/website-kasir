@props(['menus'])

<div x-data="{ open: true, activeSubmenu: null }" class="flex flex-col h-screen bg-gray-900 text-white w-64 fixed left-0 top-0 transition-all duration-300" :class="{ 'w-64': open, 'w-20': !open }">
    <!-- Logo & Toggle -->
    <div class="flex items-center justify-between p-4 border-b border-gray-800">
        <div class="flex items-center space-x-3" x-show="open">
            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="text-xl font-bold">KASIR-5</span>
        </div>
        <button @click="open = !open" class="p-2 rounded-lg hover:bg-gray-800 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
        @foreach($menus as $menu)
            @if(isset($menu['submenu']))
                <!-- Menu with Submenu -->
                <div x-data="{ submenuOpen: false }" class="mb-1">
                    <button @click="submenuOpen = !submenuOpen; activeSubmenu = submenuOpen ? '{{ $menu['id'] ?? $loop->index }}' : null"
                            class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-800 transition-colors duration-200"
                            :class="{ 'bg-gray-800': submenuOpen }">
                        <div class="flex items-center space-x-3">
                            <span class="text-xl">{!! $menu['icon'] ?? '' !!}</span>
                            <span x-show="open" class="font-medium">{{ $menu['label'] }}</span>
                        </div>
                        <svg x-show="open" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': submenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="submenuOpen && open" x-collapse class="bg-gray-800 bg-opacity-50">
                        @foreach($menu['submenu'] as $submenuItem)
                            <a href="{{ $submenuItem['url'] }}"
                               class="flex items-center px-4 py-2 pl-12 hover:bg-gray-700 transition-colors duration-200 {{ request()->is(ltrim($submenuItem['url'], '/')) ? 'bg-gray-700 border-l-4 border-indigo-500' : '' }}">
                                <span class="text-sm">{{ $submenuItem['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Single Menu Item -->
                <a href="{{ $menu['url'] }}"
                   class="flex items-center px-4 py-3 hover:bg-gray-800 transition-colors duration-200 mb-1 {{ request()->is(ltrim($menu['url'], '/')) ? 'bg-gray-800 border-l-4 border-indigo-500' : '' }}">
                    <span class="text-xl">{!! $menu['icon'] ?? '' !!}</span>
                    <span x-show="open" class="ml-3 font-medium">{{ $menu['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <!-- User Info at Bottom -->
    <div class="border-t border-gray-800 p-4">
        <div class="flex items-center space-x-3" x-show="open">
            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-400 hover:bg-gray-800 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span x-show="open" class="ml-3">Logout</span>
            </button>
        </form>
    </div>
</div>
