@props(['name', 'label', 'options' => [], 'selected' => ''])

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>

    <div class="space-y-2">
        @foreach($options as $value => $optionLabel)
            <label class="flex items-center">
                <input type="radio"
                       name="{{ $name }}"
                       value="{{ $value }}"
                       {{ old($name, $selected) == $value ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">

                <span class="ml-2 text-sm text-gray-700">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
