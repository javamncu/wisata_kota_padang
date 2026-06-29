@props(['title', 'name', 'options' => [], 'selected' => []])

<div>
    <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ $title }}</h3>
    @foreach ($options as $value => $label)
        <label class="flex items-center gap-2 text-sm text-gray-600 py-0.5">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}"
                   @checked(in_array($value, $selected, true))
                   class="rounded text-emerald-600 focus:ring-emerald-500">
            {{ $label }}
        </label>
    @endforeach
</div>
