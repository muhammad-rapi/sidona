@props(['icon', 'label', 'variant' => 'line', 'href' => null])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['action', 'action-'.$variant]) }} aria-label="{{ $label }}" data-tip="{{ $label }}"><x-icon :name="$icon" /></a>
@else
    <button type="button" {{ $attributes->class(['action', 'action-'.$variant]) }} aria-label="{{ $label }}" data-tip="{{ $label }}"><x-icon :name="$icon" /></button>
@endif
