@props([
    'href' => '#',
    'label' => '',
    'icon' => '',
    'active' => false,
    'isButton' => false,
    'type' => 'button',
])

@if($isButton)
    <button type="{{ $type }}" class="gcs-item gcs-item--footer {{ $active ? 'is-active' : '' }}" data-tooltip="{{ $label }}" title="{{ $label }}">
        <span class="gcs-item__icon" aria-hidden="true">{!! $icon !!}</span>
        <span class="gcs-item__label">{{ $label }}</span>
    </button>
@else
    <a href="{{ $href }}" class="gcs-item gcs-item--footer {{ $active ? 'is-active' : '' }}" data-tooltip="{{ $label }}" title="{{ $label }}">
        <span class="gcs-item__icon" aria-hidden="true">{!! $icon !!}</span>
        <span class="gcs-item__label">{{ $label }}</span>
    </a>
@endif
