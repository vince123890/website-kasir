@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Categories Management</h2>
            <p class="mt-1 text-sm text-gray-600">Manage product categories</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('categories.export') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export to CSV
            </x-button>
            <x-button type="primary" href="{{ route('categories.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Category
            </x-button>
        </div>
    </div>

    <!-- Search and Filters -->
    <x-card>
        <form method="GET" action="{{ route('categories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, slug..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                    <select name="parent_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        <option value="null" {{ (($filters['parent_id'] ?? '') === 'null') ? 'selected' : '' }}>Main Categories Only</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ (($filters['parent_id'] ?? '') == $parent->id) ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="1" {{ (($filters['is_active'] ?? '') === '1') ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (($filters['is_active'] ?? '') === '0') ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex space-x-2">
                <x-button type="primary" submit>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </x-button>
                <x-button type="secondary" href="{{ route('categories.index') }}">
                    Reset
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Categories Table -->
    <x-card>
        @if($categories->count() > 0)
            <!-- Bulk Actions Bar -->
            <div class="mb-4" x-data="bulkActions()">
                <div x-show="selectedIds.length > 0" class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <span class="text-sm text-blue-700">
                        <span x-text="selectedIds.length"></span> kategori dipilih
                    </span>
                    <button
                        @click="showBulkDeleteModal = true"
                        type="button"
                        class="text-sm text-red-600 hover:text-red-900 font-medium"
                    >
                        Hapus Terpilih
                    </button>
                </div>

                <!-- Bulk Delete Modal -->
                <div x-show="showBulkDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBulkDeleteModal = false"></div>
                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 shadow-xl max-w-lg w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Hapus Kategori Terpilih</h3>
                            <form action="{{ route('categories.bulkDelete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="category_ids" :value="JSON.stringify(selectedIds)">

                                <p class="text-sm text-gray-600 mb-4">
                                    Anda akan menghapus <span x-text="selectedIds.length"></span> kategori.
                                    Jika kategori memiliki produk, Anda harus memilih kategori tujuan untuk reassignment.
                                </p>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pindahkan produk ke kategori:</label>
                                    <select name="reassign_to_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Kategori (Opsional) -</option>
                                        @foreach($parentCategories as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex space-x-3">
                                    <x-button type="secondary" @click="showBulkDeleteModal = false">Batal</x-button>
                                    <x-button type="danger" submit>Hapus</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        @change="toggleAll($event)"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($categories as $category)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :checked="selectedIds.includes({{ $category->id }})"
                                            @change="toggleSelect({{ $category->id }})"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $category->slug }}</code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $category->parent ? $category->parent->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $category->products_count }} produk
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-badge :color="$category->is_active ? 'green' : 'red'" :text="$category->is_active ? 'Active' : 'Inactive'" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('categories.edit', $category->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>

                                            @if($category->products_count > 0)
                                                <button
                                                    @click="showDeleteWithReassign({{ $category->id }}, {{ $category->products_count }})"
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            @else
                                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Hapus kategori ini?')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Delete with Reassign Modal -->
                <div x-show="showReassignModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showReassignModal = false"></div>
                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 shadow-xl max-w-lg w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Hapus Kategori dengan Produk</h3>
                            <form :action="'/categories/' + deleteId" method="POST">
                                @csrf
                                @method('DELETE')

                                <p class="text-sm text-gray-600 mb-4">
                                    Kategori ini memiliki <span x-text="deleteProductsCount"></span> produk.
                                    Silakan pilih kategori tujuan untuk memindahkan produk-produk tersebut.
                                </p>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pindahkan produk ke kategori:</label>
                                    <select name="reassign_to_category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Kategori -</option>
                                        @foreach($parentCategories as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex space-x-3">
                                    <x-button type="secondary" @click="showReassignModal = false">Batal</x-button>
                                    <x-button type="danger" submit>Hapus & Pindahkan</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <x-pagination :paginator="$categories" />
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No categories found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
                <div class="mt-6">
                    <x-button type="primary" href="{{ route('categories.create') }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Category
                    </x-button>
                </div>
            </div>
        @endif
    </x-card>
</div>

<script>
function bulkActions() {
    return {
        selectedIds: [],
        showBulkDeleteModal: false,
        showReassignModal: false,
        deleteId: null,
        deleteProductsCount: 0,

        toggleAll(event) {
            if (event.target.checked) {
                this.selectedIds = [
                    @foreach($categories as $category)
                        {{ $category->id }},
                    @endforeach
                ];
            } else {
                this.selectedIds = [];
            }
        },

        toggleSelect(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds.push(id);
            }
        },

        showDeleteWithReassign(id, productsCount) {
            this.deleteId = id;
            this.deleteProductsCount = productsCount;
            this.showReassignModal = true;
        }
    }
}
</script>
@endsection

@php
    $title = 'Categories Management';
    $breadcrumb = [
        ['label' => 'Categories', 'url' => route('categories.index')]
    ];
@endphp
