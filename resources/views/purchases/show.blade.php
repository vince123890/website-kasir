@extends('layouts.admin')

@section('page-content')
<div class="space-y-6" x-data="{ showRejectModal: false }">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Purchase Order Details</h2>
            <p class="mt-1 text-sm text-gray-600">PO Number: {{ $purchaseOrder->po_number }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @if($purchaseOrder->canEdit)
                <x-button type="primary" href="{{ route('purchases.edit', $purchaseOrder->id) }}">
                    Edit PO
                </x-button>
            @endif
            <x-button type="secondary" href="{{ route('purchases.index') }}">
                Back to List
            </x-button>
        </div>
    </div>

    <!-- Status & Actions -->
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-600">Status:</span>
                <x-badge
                    :color="$purchaseOrder->statusBadge['color']"
                    :text="$purchaseOrder->statusBadge['label']"
                    class="ml-2"
                />
            </div>
            <div class="flex items-center space-x-3">
                @if($purchaseOrder->canSubmit)
                    <form action="{{ route('purchases.submit', $purchaseOrder->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" onclick="return confirm('Submit for approval?')">
                            Submit for Approval
                        </button>
                    </form>
                @endif

                @if($purchaseOrder->canApprove && auth()->user()->hasRole('Tenant Owner'))
                    <form action="{{ route('purchases.approve', $purchaseOrder->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="return confirm('Approve this PO?')">
                            Approve PO
                        </button>
                    </form>
                    <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject PO
                    </button>
                @endif

                @if($purchaseOrder->canReceive)
                    <form action="{{ route('purchases.receive', $purchaseOrder->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" onclick="return confirm('Mark as received? Stock will be updated.')">
                            Receive PO
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - PO Details -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Order Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">PO Number:</span>
                        <p class="text-gray-900 mt-1"><code class="bg-gray-100 px-2 py-1 rounded">{{ $purchaseOrder->po_number }}</code></p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Supplier:</span>
                        <p class="text-gray-900 mt-1">{{ $purchaseOrder->supplier->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Order Date:</span>
                        <p class="text-gray-900 mt-1">{{ $purchaseOrder->order_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Expected Delivery:</span>
                        <p class="text-gray-900 mt-1">{{ $purchaseOrder->expected_delivery_date->format('d M Y') }}</p>
                    </div>
                    @if($purchaseOrder->notes)
                        <div class="col-span-2">
                            <span class="font-medium text-gray-700">Notes:</span>
                            <p class="text-gray-900 mt-1">{{ $purchaseOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Items Table -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item->product->sku }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50">
                                <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Subtotal:</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($purchaseOrder->tax_amount > 0)
                                <tr class="bg-gray-50">
                                    <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Tax:</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr class="bg-indigo-50">
                                <td colspan="4" class="px-4 py-3 text-base font-bold text-gray-900 text-right">Total Amount:</td>
                                <td class="px-4 py-3 text-base font-bold text-indigo-600 text-right">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Right Column - Workflow Timeline -->
        <div class="space-y-6">
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Workflow Timeline</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Created</p>
                            <p class="text-xs text-gray-500">{{ $purchaseOrder->created_at->format('d M Y H:i') }}</p>
                            @if($purchaseOrder->createdBy)
                                <p class="text-xs text-gray-500">by {{ $purchaseOrder->createdBy->name }}</p>
                            @endif
                        </div>
                    </div>

                    @if($purchaseOrder->submitted_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Submitted</p>
                                <p class="text-xs text-gray-500">{{ $purchaseOrder->submitted_at->format('d M Y H:i') }}</p>
                                @if($purchaseOrder->submittedBy)
                                    <p class="text-xs text-gray-500">by {{ $purchaseOrder->submittedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($purchaseOrder->approved_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Approved</p>
                                <p class="text-xs text-gray-500">{{ $purchaseOrder->approved_at->format('d M Y H:i') }}</p>
                                @if($purchaseOrder->approvedBy)
                                    <p class="text-xs text-gray-500">by {{ $purchaseOrder->approvedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($purchaseOrder->rejected_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Rejected</p>
                                <p class="text-xs text-gray-500">{{ $purchaseOrder->rejected_at->format('d M Y H:i') }}</p>
                                @if($purchaseOrder->rejectedBy)
                                    <p class="text-xs text-gray-500">by {{ $purchaseOrder->rejectedBy->name }}</p>
                                @endif
                                @if($purchaseOrder->rejection_reason)
                                    <p class="text-xs text-red-600 mt-1">Reason: {{ $purchaseOrder->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($purchaseOrder->received_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-green-600 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Received</p>
                                <p class="text-xs text-gray-500">{{ $purchaseOrder->received_at->format('d M Y H:i') }}</p>
                                @if($purchaseOrder->receivedBy)
                                    <p class="text-xs text-gray-500">by {{ $purchaseOrder->receivedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form action="{{ route('purchases.reject', $purchaseOrder->id) }}" method="POST">
                    @csrf
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Purchase Order</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Rejection Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Reject PO
                        </button>
                        <button type="button" @click="showRejectModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $title = 'Purchase Order Details';
    $breadcrumb = [
        ['label' => 'Purchase Orders', 'url' => route('purchases.index')],
        ['label' => $purchaseOrder->po_number, 'url' => route('purchases.show', $purchaseOrder->id')]
    ];
@endphp
