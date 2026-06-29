@props(['name', 'value', 'label'])

<label class="cursor-pointer">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="peer sr-only"
           @checked((string) request($name, '') === (string) $value)>
    <span class="block rounded-xl border border-gray-200 bg-white px-4 py-3 text-center text-sm text-gray-700 transition
                 hover:border-emerald-300
                 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:ring-1 peer-checked:ring-emerald-500">
        {{ $label }}
    </span>
</label>
