@props([
    'href' => '#',
    'label' => '',
    'active' => false,
    'icon' => '',
])

<a
    href="{{ $href }}"
    class="gcs-item {{ $active ? 'is-active' : '' }}"
    data-tooltip="{{ $label }}"
    title="{{ $label }}"
>
    <span class="gcs-item__icon" aria-hidden="true">{!! $icon !!}</span>
    <span class="gcs-item__label">{{ $label }}</span>
</a>
