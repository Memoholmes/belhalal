@props([
'disabled' => false,
'label' => null,
'name' => $attributes->get('name'),
'checked' => $attributes->get('checked'),
])
<div class="custom-control custom-switch" x-cloak>
    <input type="hidden" :checked="!{{ $checked }}" name="{{ $name }}" value="0" />
    <input type="checkbox" :checked="{{ $checked }}" class="custom-control-input" value="1" id="lwSwitch_{{ $name }}{{ $checked }}" name="{{ $name }}">
    {{-- update model after click with very short delay --}}
    <label @click="_.defer(function() {
        {{ $checked }} = ! {{ $checked }};
    })" class="custom-control-label" for="lwSwitch_{{ $name }}{{ $checked }}">
        {{ $label }}
    </label>
</div>