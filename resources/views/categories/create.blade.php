@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Category</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new product category</p>
        </div>
        <x-button type="secondary" href="{{ route('categories.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Categories
        </x-button>
    </div>

    <!-- Create Form -->
    <x-card>
        <form action="{{ route('categories.store') }}" method="POST" x-data="categoryForm()">
            @csrf

            <div class="space-y-6">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.input
                                name="name"
                                label="Category Name"
                                :value="old('name')"
                                required
                                placeholder="Enter category name"
                                x-on:input="generateSlug"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="slug"
                                x-model="slug"
                                required
                                placeholder="category-slug"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 lowercase"
                            >
                            <p class="mt-1 text-xs text-gray-500">URL-friendly identifier (lowercase, dashes only)</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                name="description"
                                rows="3"
                                placeholder="Enter category description (optional)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Hierarchy Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Category Hierarchy</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                        <select
                            name="parent_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">- None (Main Category) -</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Leave empty to create a main category, or select a parent to create a subcategory</p>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Account Settings Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Category Settings</h3>
                    <div class="space-y-4">
                        <x-form.radio
                            name="is_active"
                            label="Category Status"
                            :options="['1' => 'Active', '0' => 'Inactive']"
                            :selected="old('is_active', '1')"
                        />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-end space-x-4">
                    <x-button type="secondary" href="{{ route('categories.index') }}">
                        Cancel
                    </x-button>
                    <x-button type="primary" submit>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Category
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>

<script>
function categoryForm() {
    return {
        slug: '{{ old('slug') }}',

        generateSlug(event) {
            const name = event.target.value;
            this.slug = name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    }
}
</script>
@endsection

@php
    $title = 'Create Category';
    $breadcrumb = [
        ['label' => 'Categories', 'url' => route('categories.index')],
        ['label' => 'Create', 'url' => route('categories.create')]
    ];
@endphp
