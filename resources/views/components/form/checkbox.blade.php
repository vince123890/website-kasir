@props(['name', 'label', 'checked' => false, 'value' => '1'])

<div class="mb-4">
    <label class="flex items-center">
        <input type="checkbox"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $value }}"
               {{ old($name, $checked) ? 'checked' : '' }}
               {{ $attributes->merge(['class' => 'w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500']) }}>

        <span class="ml-2 text-sm font-medium text-gray-700">{{ $label }}</span>
    </label>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
