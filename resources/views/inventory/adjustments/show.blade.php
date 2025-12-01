<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock Adjustment - {{ $adjustment->adjustment_number }}
            </h2>
            <span class="px-3 py-1 text-sm font-semibold rounded-full
                @if($adjustment->statusBadge['color'] === 'gray') bg-gray-100 text-gray-800
                @elseif($adjustment->statusBadge['color'] === 'yellow') bg-yellow-100 text-yellow-800
                @elseif($adjustment->statusBadge['color'] === 'blue') bg-blue-100 text-blue-800
                @elseif($adjustment->statusBadge['color'] === 'green') bg-green-100 text-green-800
                @elseif($adjustment->statusBadge['color'] === 'red') bg-red-100 text-red-800
                @endif">
                {{ $adjustment->statusBadge['label'] }}
            </span>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showRejectModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mb-6 flex flex-wrap gap-2">
                @if($adjustment->canEdit)
                    <a href="{{ route('inventory.adjustments.edit', $adjustment->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Edit
                    </a>
                @endif

                @if($adjustment->canSubmit)
                    <form action="{{ route('inventory.adjustments.submit', $adjustment->id) }}" method="POST" class="inline" onsubmit="return confirm('Submit stock adjustment for approval?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Submit for Approval
                        </button>
                    </form>
                @endif

                @if($adjustment->canApprove && auth()->user()->hasRole('Tenant Owner'))
                    <form action="{{ route('inventory.adjustments.approve', $adjustment->id) }}" method="POST" class="inline" onsubmit="return confirm('Approve this stock adjustment?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Approve
                        </button>
                    </form>

                    <button @click="showRejectModal = true" type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Reject
                    </button>
                @endif

                @if($adjustment->canApply)
                    <form action="{{ route('inventory.adjustments.apply', $adjustment->id) }}" method="POST" class="inline" onsubmit="return confirm('Apply stock adjustment? This will update the stock quantity!')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                            Apply to Stock
                        </button>
                    </form>
                @endif

                @if($adjustment->canEdit)
                    <form action="{{ route('inventory.adjustments.destroy', $adjustment->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this stock adjustment?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                @endif

                <a href="{{ route('inventory.adjustments.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Back to List
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Adjustment Info Card -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Adjustment Information</h3>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Adjustment Number</dt>
                                <dd class="mt-1 text-sm font-medium">{{ $adjustment->adjustment_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Adjustment Date</dt>
                                <dd class="mt-1 text-sm">{{ $adjustment->adjustment_date->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Store</dt>
                                <dd class="mt-1 text-sm">{{ $adjustment->store->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Product</dt>
                                <dd class="mt-1 text-sm">{{ $adjustment->product->name }} ({{ $adjustment->product->sku }})</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Type</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($adjustment->typeBadge['color'] === 'green') bg-green-100 text-green-800
                                        @elseif($adjustment->typeBadge['color'] === 'red') bg-red-100 text-red-800
                                        @endif">
                                        {{ $adjustment->typeBadge['label'] }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Quantity</dt>
                                <dd class="mt-1 text-sm font-medium">{{ number_format($adjustment->quantity, 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Reason</dt>
                                <dd class="mt-1 text-sm">{{ $adjustment->reasonLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Current Stock</dt>
                                <dd class="mt-1 text-sm font-medium">{{ $currentStock ? number_format($currentStock->quantity, 0, ',', '.') : '0' }}</dd>
                            </div>
                            @if($adjustment->notes)
                                <div class="col-span-2">
                                    <dt class="text-sm text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm">{{ $adjustment->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Workflow Timeline Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Workflow Timeline</h3>
                        <div class="space-y-4">
                            <!-- Created -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Created</p>
                                    <p class="text-xs text-gray-500">{{ $adjustment->createdBy?->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $adjustment->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            @if($adjustment->submitted_at)
                                <!-- Submitted -->
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M4 8V6a6 6 0 1112 0v2h1a2 2 0 012 2v8a2 2 0 01-2 2H3a2 2 0 01-2-2v-8a2 2 0 012-2h1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Submitted</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->submittedBy?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->submitted_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($adjustment->approved_at)
                                <!-- Approved -->
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Approved</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->approvedBy?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->approved_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($adjustment->rejected_at)
                                <!-- Rejected -->
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Rejected</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->rejectedBy?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->rejected_at->format('d M Y H:i') }}</p>
                                        @if($adjustment->rejection_reason)
                                            <p class="text-xs text-red-600 mt-1">{{ $adjustment->rejection_reason }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($adjustment->applied_at)
                                <!-- Applied -->
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Applied to Stock</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->applied_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div @click="showRejectModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                    <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Stock Adjustment</h3>

                        <form action="{{ route('inventory.adjustments.reject', $adjustment->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                                <textarea name="rejection_reason" rows="4" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
