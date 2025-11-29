@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Store</h2>
            <p class="mt-1 text-sm text-gray-600">Update store information and settings</p>
        </div>
        <x-button type="secondary" href="{{ route('stores.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Stores
        </x-button>
    </div>

    <!-- Edit Form -->
    <x-card>
        <form action="{{ route('stores.update', $store->id) }}" method="POST" enctype="multipart/form-data" x-data="storeForm()">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.input
                                name="name"
                                label="Store Name"
                                :value="old('name', $store->name)"
                                required
                                placeholder="Enter store name"
                                x-on:input="generateCode"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Store Code <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="code"
                                x-model="code"
                                required
                                placeholder="STORE-CODE"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 uppercase"
                            >
                            <p class="mt-1 text-xs text-yellow-600">⚠️ Warning: Changing code may affect integrations!</p>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <x-form.input
                                name="address"
                                label="Address"
                                :value="old('address', $store->address)"
                                required
                                placeholder="Enter complete address"
                            />
                        </div>

                        <x-form.input
                            name="city"
                            label="City"
                            :value="old('city', $store->city)"
                            required
                            placeholder="e.g., Jakarta"
                        />

                        <x-form.input
                            name="province"
                            label="Province"
                            :value="old('province', $store->province)"
                            required
                            placeholder="e.g., DKI Jakarta"
                        />

                        <x-form.input
                            name="postal_code"
                            label="Postal Code"
                            :value="old('postal_code', $store->postal_code)"
                            placeholder="12345"
                        />

                        <x-form.input
                            name="phone"
                            label="Phone Number"
                            :value="old('phone', $store->phone)"
                            required
                            placeholder="+62 21-1234-5678"
                        />

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email Address"
                            :value="old('email', $store->email)"
                            placeholder="store@example.com"
                        />
                    </div>
                </div>

                <!-- Settings Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Store Settings</h3>
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
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form.input
                                name="tax_rate"
                                type="number"
                                label="Tax Rate (%)"
                                :value="old('tax_rate', $store->tax_rate)"
                                placeholder="11"
                                step="0.01"
                                min="0"
                                max="100"
                            />
                        </div>

                        <div class="flex items-center pt-7">
                            <x-form.checkbox
                                name="tax_included"
                                label="Tax included in price"
                                :checked="old('tax_included', $store->tax_included)"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rounding Method</label>
                            <select
                                name="rounding_method"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                @foreach($roundingMethods as $value => $label)
                                    <option value="{{ $value }}" {{ old('rounding_method', $store->rounding_method) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rounding_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Logo Upload Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Store Logo</h3>
                    <div class="space-y-4">
                        @if($store->logo)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                                <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-24 w-24 rounded-lg object-cover border">
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $store->logo ? 'Change Logo' : 'Upload Logo' }}</label>
                            <input
                                type="file"
                                name="logo"
                                accept="image/jpeg,image/jpg,image/png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <p class="mt-1 text-xs text-gray-500">Allowed formats: JPEG, JPG, PNG. Max size: 2MB</p>
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Receipt Settings Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Header</label>
                            <textarea
                                name="receipt_header"
                                rows="3"
                                placeholder="Text to appear at the top of receipts"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('receipt_header', $store->receipt_header) }}</textarea>
                            @error('receipt_header')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Footer</label>
                            <textarea
                                name="receipt_footer"
                                rows="3"
                                placeholder="Text to appear at the bottom of receipts"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('receipt_footer', $store->receipt_footer) }}</textarea>
                            @error('receipt_footer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Settings Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Settings</h3>
                    <div class="space-y-4">
                        <x-form.radio
                            name="is_active"
                            label="Store Status"
                            :options="['1' => 'Active', '0' => 'Inactive']"
                            :selected="old('is_active', $store->is_active ? '1' : '0')"
                        />
                    </div>
                </div>

                <!-- Store Metadata -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Store Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-600 ml-2">{{ $store->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Updated:</span>
                                <span class="text-gray-600 ml-2">{{ $store->updated_at->format('d M Y H:i') }}</span>
                            </div>
                            @if($store->activated_at)
                                <div>
                                    <span class="font-medium text-gray-700">Activated At:</span>
                                    <span class="text-gray-600 ml-2">{{ $store->activated_at->format('d M Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-between">
                    <div class="flex space-x-3">
                        <form action="{{ route('stores.destroy', $store->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this store? All data will be lost!');">
                            @csrf
                            @method('DELETE')
                            <x-button type="danger" submit>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Store
                            </x-button>
                        </form>
                    </div>
                    <div class="flex items-center space-x-4">
                        <x-button type="secondary" href="{{ route('stores.index') }}">
                            Cancel
                        </x-button>
                        <x-button type="primary" submit>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Store
                        </x-button>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>

<script>
function storeForm() {
    return {
        code: '{{ old('code', $store->code) }}',

        generateCode(event) {
            const name = event.target.value;
            this.code = name
                .toUpperCase()
                .replace(/[^A-Z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    }
}
</script>
@endsection

@php
    $title = 'Edit Store - ' . $store->name;
    $breadcrumb = [
        ['label' => 'Stores', 'url' => route('stores.index')],
        ['label' => $store->name, 'url' => route('stores.show', $store->id)],
        ['label' => 'Edit', 'url' => route('stores.edit', $store->id')]
    ];
@endphp
