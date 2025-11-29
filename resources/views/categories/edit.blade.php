@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Category</h2>
            <p class="mt-1 text-sm text-gray-600">Update category information</p>
        </div>
        <x-button type="secondary" href="{{ route('categories.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Categories
        </x-button>
    </div>

    <!-- Edit Form -->
    <x-card>
        <form action="{{ route('categories.update', $category->id) }}" method="POST" x-data="categoryForm()">
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
                                label="Category Name"
                                :value="old('name', $category->name)"
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
                            <p class="mt-1 text-xs text-yellow-600">⚠️ Warning: Changing slug may affect product URLs!</p>
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
                            >{{ old('description', $category->description) }}</textarea>
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
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Leave empty to make this a main category</p>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Category Statistics -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Category Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Products Count:</span>
                                <span class="text-gray-600 ml-2">{{ $category->products_count }} produk</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-600 ml-2">{{ $category->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Updated:</span>
                                <span class="text-gray-600 ml-2">{{ $category->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
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
                            :selected="old('is_active', $category->is_active ? '1' : '0')"
                        />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-between">
                    <div class="flex space-x-3">
                        @if($category->products_count > 0)
                            <div x-data="{ showDeleteModal: false }">
                                <button
                                    @click="showDeleteModal = true"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Category
                                </button>

                                <!-- Delete with Reassign Modal -->
                                <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                    <div class="flex items-center justify-center min-h-screen px-4">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
                                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 shadow-xl max-w-lg w-full">
                                            <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Category with Products</h3>
                                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <p class="text-sm text-gray-600 mb-4">
                                                    This category has {{ $category->products_count }} products.
                                                    Please select a category to reassign these products.
                                                </p>

                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reassign products to:</label>
                                                    <select name="reassign_to_category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                                        <option value="">- Select Category -</option>
                                                        @foreach($parentCategories as $parent)
                                                            @if($parent->id != $category->id)
                                                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="flex space-x-3">
                                                    <x-button type="secondary" @click="showDeleteModal = false">Cancel</x-button>
                                                    <x-button type="danger" submit>Delete & Reassign</x-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <x-button type="danger" submit>
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Category
                                </x-button>
                            </form>
                        @endif
                    </div>
                    <div class="flex items-center space-x-4">
                        <x-button type="secondary" href="{{ route('categories.index') }}">
                            Cancel
                        </x-button>
                        <x-button type="primary" submit>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Category
                        </x-button>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>

<script>
function categoryForm() {
    return {
        slug: '{{ old('slug', $category->slug) }}',

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
    $title = 'Edit Category - ' . $category->name;
    $breadcrumb = [
        ['label' => 'Categories', 'url' => route('categories.index')],
        ['label' => $category->name, 'url' => '#'],
        ['label' => 'Edit', 'url' => route('categories.edit', $category->id')]
    ];
@endphp
