@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Store Settings</h2>
            <p class="mt-1 text-sm text-gray-600">Configure {{ $store->name }} settings</p>
        </div>
        <x-button type="secondary" href="{{ route('stores.show', $store->id) }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Store
        </x-button>
    </div>

    <!-- Settings Tabs -->
    <x-card x-data="{ activeTab: 'general' }">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button
                    @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                    General Settings
                </button>
                <button
                    @click="activeTab = 'receipt'"
                    :class="activeTab === 'receipt' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                    Receipt Settings
                </button>
                <button
                    @click="activeTab = 'hours'"
                    :class="activeTab === 'hours' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                    Operating Hours
                </button>
            </nav>
        </div>

        <!-- Settings Form -->
        <form action="{{ route('stores.updateSettings', $store->id) }}" method="POST" class="mt-6">
            @csrf
            @method('PUT')

            <!-- General Settings Tab -->
            <div x-show="activeTab === 'general'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select
                                name="timezone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                @foreach($timezones as $timezone)
                                    <option value="{{ $timezone }}" {{ old('timezone', $store->timezone) === $timezone ? 'selected' : '' }}>
                                        {{ $timezone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select
                                name="currency"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                @foreach($currencies as $code => $name)
                                    <option value="{{ $code }}" {{ old('currency', $store->currency) === $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tax Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                            <input
                                type="number"
                                name="tax_rate"
                                value="{{ old('tax_rate', $store->tax_rate) }}"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>

                        <div class="flex items-center pt-7">
                            <input
                                type="checkbox"
                                name="tax_included"
                                value="1"
                                {{ old('tax_included', $store->tax_included) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            >
                            <label class="ml-2 block text-sm text-gray-900">Tax included in price</label>
                        </div>
                    </div>

                    <!-- Tax Calculation Preview -->
                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4" x-data="taxCalculator()">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Tax Calculation Preview</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-blue-700">Sample Amount:</span>
                                <span class="font-medium text-blue-900">Rp 100,000</span>
                            </div>
                            <div class="flex justify-between" x-show="!taxIncluded">
                                <span class="text-blue-700">Tax (<span x-text="taxRate"></span>%):</span>
                                <span class="font-medium text-blue-900">Rp <span x-text="calculateTax()"></span></span>
                            </div>
                            <div class="flex justify-between border-t border-blue-300 pt-2">
                                <span class="text-blue-700 font-medium">Total:</span>
                                <span class="font-bold text-blue-900">Rp <span x-text="calculateTotal()"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Rounding Settings</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rounding Method</label>
                        <select
                            name="rounding_method"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            x-model="roundingMethod"
                        >
                            @foreach($roundingMethods as $value => $label)
                                <option value="{{ $value }}" {{ old('rounding_method', $store->rounding_method) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rounding Preview -->
                    <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4" x-data="roundingPreview()">
                        <h4 class="text-sm font-medium text-green-900 mb-2">Rounding Preview</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-green-700">Original Amount:</span>
                                <span class="font-medium text-green-900">Rp 12,345</span>
                            </div>
                            <div class="flex justify-between border-t border-green-300 pt-2">
                                <span class="text-green-700 font-medium">After Rounding:</span>
                                <span class="font-bold text-green-900">Rp <span x-text="applyRounding(12345)"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Settings Tab -->
            <div x-show="activeTab === 'receipt'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Content</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Header</label>
                            <textarea
                                name="receipt_header"
                                rows="4"
                                placeholder="Text to appear at the top of receipts (e.g., store address, contact info)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('receipt_header', $store->receipt_header) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">This text will appear at the top of all receipts</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Footer</label>
                            <textarea
                                name="receipt_footer"
                                rows="4"
                                placeholder="Text to appear at the bottom of receipts (e.g., Thank you message, return policy)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('receipt_footer', $store->receipt_footer) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">This text will appear at the bottom of all receipts</p>
                        </div>
                    </div>
                </div>

                <!-- Receipt Preview -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Preview</h3>
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-6 max-w-md mx-auto font-mono text-sm">
                        <div class="text-center border-b border-gray-300 pb-3 mb-3 whitespace-pre-line">
                            {{ $store->receipt_header ?: '[Receipt Header]' }}
                        </div>
                        <div class="space-y-1 border-b border-gray-300 pb-3 mb-3">
                            <div class="flex justify-between">
                                <span>Item 1</span>
                                <span>Rp 10,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Item 2</span>
                                <span>Rp 15,000</span>
                            </div>
                        </div>
                        <div class="space-y-1 mb-3">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>Rp 25,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tax ({{ $store->tax_rate }}%):</span>
                                <span>Rp {{ number_format($store->tax_rate * 250, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-bold border-t border-gray-300 pt-1">
                                <span>Total:</span>
                                <span>Rp {{ number_format(25000 + ($store->tax_rate * 250), 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="text-center border-t border-gray-300 pt-3 whitespace-pre-line">
                            {{ $store->receipt_footer ?: '[Receipt Footer]' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Hours Tab -->
            <div x-show="activeTab === 'hours'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Operating Hours</h3>
                    <div class="space-y-4">
                        @php
                            $days = [
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday',
                            ];
                        @endphp

                        @foreach($days as $key => $label)
                            @php
                                $dayData = $operatingHours[$key] ?? ['open' => '08:00', 'close' => '17:00', 'is_open' => true];
                            @endphp
                            <div class="flex items-center space-x-4 bg-gray-50 p-4 rounded-lg" x-data="{ isOpen: {{ $dayData['is_open'] ? 'true' : 'false' }} }">
                                <div class="w-32">
                                    <span class="font-medium text-gray-900">{{ $label }}</span>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="operating_hours[{{ $key }}][is_open]"
                                        value="1"
                                        x-model="isOpen"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                    <label class="ml-2 text-sm text-gray-700">Open</label>
                                </div>
                                <div class="flex items-center space-x-2" x-show="isOpen">
                                    <input
                                        type="time"
                                        name="operating_hours[{{ $key }}][open]"
                                        value="{{ $dayData['open'] }}"
                                        class="px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <span class="text-gray-500">to</span>
                                    <input
                                        type="time"
                                        name="operating_hours[{{ $key }}][close]"
                                        value="{{ $dayData['close'] }}"
                                        class="px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                </div>
                                <div x-show="!isOpen" class="text-sm text-gray-500">
                                    Closed
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t pt-6 flex items-center justify-end space-x-4 mt-6">
                <x-button type="secondary" href="{{ route('stores.show', $store->id) }}">
                    Cancel
                </x-button>
                <x-button type="primary" submit>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Settings
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
function taxCalculator() {
    return {
        taxRate: {{ $store->tax_rate ?? 11 }},
        taxIncluded: {{ $store->tax_included ? 'true' : 'false' }},

        calculateTax() {
            return (100000 * this.taxRate / 100).toLocaleString('id-ID');
        },

        calculateTotal() {
            if (this.taxIncluded) {
                return '100,000';
            } else {
                return (100000 + (100000 * this.taxRate / 100)).toLocaleString('id-ID');
            }
        }
    }
}

function roundingPreview() {
    return {
        roundingMethod: '{{ old('rounding_method', $store->rounding_method) }}',

        applyRounding(amount) {
            switch(this.roundingMethod) {
                case 'round_up':
                    return Math.ceil(amount).toLocaleString('id-ID');
                case 'round_down':
                    return Math.floor(amount).toLocaleString('id-ID');
                case 'round_nearest':
                    return Math.round(amount).toLocaleString('id-ID');
                case 'round_nearest_5':
                    return (Math.round(amount / 5) * 5).toLocaleString('id-ID');
                case 'round_nearest_10':
                    return (Math.round(amount / 10) * 10).toLocaleString('id-ID');
                case 'round_nearest_100':
                    return (Math.round(amount / 100) * 100).toLocaleString('id-ID');
                case 'round_nearest_1000':
                    return (Math.round(amount / 1000) * 1000).toLocaleString('id-ID');
                default:
                    return amount.toLocaleString('id-ID');
            }
        }
    }
}
</script>
@endsection

@php
    $title = 'Store Settings - ' . $store->name;
    $breadcrumb = [
        ['label' => 'Stores', 'url' => route('stores.index')],
        ['label' => $store->name, 'url' => route('stores.show', $store->id)],
        ['label' => 'Settings', 'url' => route('stores.settings', $store->id')]
    ];
@endphp
