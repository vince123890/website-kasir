@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Supplier</h2>
            <p class="mt-1 text-sm text-gray-600">Update supplier information</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('suppliers.history', $supplier->id) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Purchase History
            </x-button>
            <x-button type="secondary" href="{{ route('suppliers.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Suppliers
            </x-button>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" x-data="supplierForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Contact Information -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                <div class="space-y-4">
                    <x-form.input
                        name="name"
                        label="Supplier Name"
                        :value="old('name', $supplier->name)"
                        required
                        placeholder="Enter supplier name"
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Supplier Code <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="code"
                            x-model="code"
                            required
                            placeholder="SUP-00001"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 uppercase"
                        >
                        <p class="mt-1 text-xs text-yellow-600">⚠️ Warning: Changing code may affect purchase orders!</p>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form.input
                        name="contact_person"
                        label="Contact Person"
                        :value="old('contact_person', $supplier->contact_person)"
                        required
                        placeholder="Enter contact person name"
                    />

                    <x-form.input
                        name="phone"
                        label="Phone"
                        :value="old('phone', $supplier->phone)"
                        required
                        placeholder="081234567890"
                    />

                    <x-form.input
                        name="email"
                        label="Email"
                        type="email"
                        :value="old('email', $supplier->email)"
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
                            >{{ old('address', $supplier->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.input
                            name="city"
                            label="City"
                            :value="old('city', $supplier->city)"
                            placeholder="Enter city"
                        />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <select
                                name="province"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">- Select Province -</option>
                                <option value="DKI Jakarta" {{ old('province', $supplier->province) == 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                                <option value="Jawa Barat" {{ old('province', $supplier->province) == 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                                <option value="Jawa Tengah" {{ old('province', $supplier->province) == 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                                <option value="Jawa Timur" {{ old('province', $supplier->province) == 'Jawa Timur' ? 'selected' : '' }}>Jawa Timur</option>
                                <option value="Banten" {{ old('province', $supplier->province) == 'Banten' ? 'selected' : '' }}>Banten</option>
                                <option value="Yogyakarta" {{ old('province', $supplier->province) == 'Yogyakarta' ? 'selected' : '' }}>Yogyakarta</option>
                            </select>
                            @error('province')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.input
                            name="postal_code"
                            label="Postal Code"
                            :value="old('postal_code', $supplier->postal_code)"
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
                                <option value="COD" {{ old('payment_terms', $supplier->payment_terms) == 'COD' ? 'selected' : '' }}>COD (Cash on Delivery)</option>
                                <option value="Net 7" {{ old('payment_terms', $supplier->payment_terms) == 'Net 7' ? 'selected' : '' }}>Net 7 Days</option>
                                <option value="Net 14" {{ old('payment_terms', $supplier->payment_terms) == 'Net 14' ? 'selected' : '' }}>Net 14 Days</option>
                                <option value="Net 30" {{ old('payment_terms', $supplier->payment_terms) == 'Net 30' ? 'selected' : '' }}>Net 30 Days</option>
                                <option value="Net 45" {{ old('payment_terms', $supplier->payment_terms) == 'Net 45' ? 'selected' : '' }}>Net 45 Days</option>
                                <option value="Net 60" {{ old('payment_terms', $supplier->payment_terms) == 'Net 60' ? 'selected' : '' }}>Net 60 Days</option>
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
                            :selected="old('is_active', $supplier->is_active ? '1' : '0')"
                        />
                    </div>
                </x-card>

                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-600 ml-2">{{ $supplier->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Updated:</span>
                                <span class="text-gray-600 ml-2">{{ $supplier->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Form Actions -->
        <x-card class="mt-6">
            <div class="flex items-center justify-between">
                <div>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                        @csrf
                        @method('DELETE')
                        <x-button type="danger" submit>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Supplier
                        </x-button>
                    </form>
                </div>
                <div class="flex items-center space-x-4">
                    <x-button type="secondary" href="{{ route('suppliers.index') }}">
                        Cancel
                    </x-button>
                    <x-button type="primary" submit>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Supplier
                    </x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>

<script>
function supplierForm() {
    return {
        code: '{{ old('code', $supplier->code) }}',
        taxId: '{{ old('tax_id', $supplier->tax_id) }}',

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
    $title = 'Edit Supplier - ' . $supplier->name;
    $breadcrumb = [
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
        ['label' => $supplier->name, 'url' => '#'],
        ['label' => 'Edit', 'url' => route('suppliers.edit', $supplier->id)]
    ];
@endphp
