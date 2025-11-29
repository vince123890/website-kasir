@props(['action', 'title' => 'Confirm Deletion', 'message' => 'Are you sure you want to delete this item? This action cannot be undone.'])

<div x-data="{ show: false }"
     x-on:open-confirm-delete.window="show = true"
     x-on:close-confirm-delete.window="show = false"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50" @click="show = false"></div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="inline-block w-full max-w-md overflow-hidden text-left align-middle bg-white rounded-lg shadow-xl transform">

            <div class="p-6">
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Title -->
                <h3 class="mt-4 text-lg font-medium text-center text-gray-900">{{ $title }}</h3>

                <!-- Message -->
                <p class="mt-2 text-sm text-center text-gray-500">{{ $message }}</p>

                <!-- Actions -->
                <div class="mt-6 flex space-x-3">
                    <button @click="show = false" type="button" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>

                    <form action="{{ $action }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
