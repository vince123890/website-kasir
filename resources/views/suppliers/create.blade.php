@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Supplier</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new supplier to your system</p>
        </div>
        <x-button type="secondary" href="{{ route('suppliers.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Suppliers
        </x-button>
    </div>

    <!-- Create Form -->
    <form action="{{ route('suppliers.store') }}" method="POST" x-data="supplierForm()">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Contact Information -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                <div class="space-y-4">
                    <x-form.input
                        name="name"
                        label="Supplier Name"
                        :value="old('name')"
                        required
                        placeholder="Enter supplier name"
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Supplier Code <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <input
                                type="text"
                                name="code"
                                x-model="code"
                                required
                                placeholder="SUP-00001"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 uppercase"
                            >
                            <button
                                @click.prevent="generateCode"
                                type="button"
                                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                            >
                                Generate
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Auto-generated or enter manually</p>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form.input
                        name="contact_person"
                        label="Contact Person"
                        :value="old('contact_person')"
                        required
                        placeholder="Enter contact person name"
                    />

                    <x-form.input
                        name="phone"
                        label="Phone"
                        :value="old('phone')"
                        required
                        placeholder="081234567890"
                    />

                    <x-form.input
                        name="email"
                        label="Email"
                        type="email"
                        :value="old('email')"
                        placeholder="supplier@example.com (optional)"
                    />
                </div>
            </x-card>

            <!-- Right Column - Address & Business Information -->
            <div class="space-y-6">
                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="address"
                                rows="3"
                                required
                                placeholder="Enter complete address"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.input
                            name="city"
                            label="City"
                            :value="old('city')"
                            placeholder="Enter city"
                        />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <select
                                name="province"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">- Select Province -</option>
                                <option value="DKI Jakarta" {{ old('province') == 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                                <option value="Jawa Barat" {{ old('province') == 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                                <option value="Jawa Tengah" {{ old('province') == 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                                <option value="Jawa Timur" {{ old('province') == 'Jawa Timur' ? 'selected' : '' }}>Jawa Timur</option>
                                <option value="Banten" {{ old('province') == 'Banten' ? 'selected' : '' }}>Banten</option>
                                <option value="Yogyakarta" {{ old('province') == 'Yogyakarta' ? 'selected' : '' }}>Yogyakarta</option>
                            </select>
                            @error('province')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.input
                            name="postal_code"
                            label="Postal Code"
                            :value="old('postal_code')"
                            placeholder="12345"
                        />
                    </div>
                </x-card>

                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
                            <select
                                name="payment_terms"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">- Select Payment Terms -</option>
                                <option value="COD" {{ old('payment_terms') == 'COD' ? 'selected' : '' }}>COD (Cash on Delivery)</option>
                                <option value="Net 7" {{ old('payment_terms') == 'Net 7' ? 'selected' : '' }}>Net 7 Days</option>
                                <option value="Net 14" {{ old('payment_terms') == 'Net 14' ? 'selected' : '' }}>Net 14 Days</option>
                                <option value="Net 30" {{ old('payment_terms') == 'Net 30' ? 'selected' : '' }}>Net 30 Days</option>
                                <option value="Net 45" {{ old('payment_terms') == 'Net 45' ? 'selected' : '' }}>Net 45 Days</option>
                                <option value="Net 60" {{ old('payment_terms') == 'Net 60' ? 'selected' : '' }}>Net 60 Days</option>
                            </select>
                            @error('payment_terms')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tax ID / NPWP
                            </label>
                            <input
                                type="text"
                                name="tax_id"
                                x-model="taxId"
                                @input="formatNPWP"
                                placeholder="XX.XXX.XXX.X-XXX.XXX"
                                maxlength="20"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <p class="mt-1 text-xs text-gray-500">Format: XX.XXX.XXX.X-XXX.XXX (optional)</p>
                            @error('tax_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.radio
                            name="is_active"
                            label="Supplier Status"
                            :options="['1' => 'Active', '0' => 'Inactive']"
                            :selected="old('is_active', '1')"
                        />
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Form Actions -->
        <x-card class="mt-6">
            <div class="flex items-center justify-end space-x-4">
                <x-button type="secondary" href="{{ route('suppliers.index') }}">
                    Cancel
                </x-button>
                <x-button type="primary" submit>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Supplier
                </x-button>
            </div>
        </x-card>
    </form>
</div>

<script>
function supplierForm() {
    return {
        code: '{{ old('code') }}',
        taxId: '{{ old('tax_id') }}',

        async generateCode() {
            // Simple client-side generation (will be validated server-side)
            const timestamp = Date.now().toString().slice(-5);
            this.code = 'SUP-' + timestamp.padStart(5, '0');
        },

        formatNPWP() {
            // Remove all non-numeric characters
            let clean = this.taxId.replace(/[^0-9]/g, '');

            // Limit to 15 digits
            clean = clean.substring(0, 15);

            // Format: XX.XXX.XXX.X-XXX.XXX
            if (clean.length >= 2) {
                let formatted = clean.substring(0, 2);
                if (clean.length >= 3) formatted += '.' + clean.substring(2, 5);
                if (clean.length >= 6) formatted += '.' + clean.substring(5, 8);
                if (clean.length >= 9) formatted += '.' + clean.substring(8, 9);
                if (clean.length >= 10) formatted += '-' + clean.substring(9, 12);
                if (clean.length >= 13) formatted += '.' + clean.substring(12, 15);
                this.taxId = formatted;
            }
        }
    }
}
</script>
@endsection

@php
    $title = 'Create Supplier';
    $breadcrumb = [
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
        ['label' => 'Create', 'url' => route('suppliers.create')]
    ];
@endphp
