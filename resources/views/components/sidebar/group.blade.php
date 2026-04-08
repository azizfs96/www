@props([
    'title' => '',
    'groupId' => '',
    'open' => false,
    'active' => false,
])

<div
    class="gcs-group {{ $open ? 'is-open' : '' }} {{ $active ? 'is-active' : '' }}"
    data-group-id="{{ $groupId }}"
>
    <button type="button" class="gcs-group__toggle" data-group-toggle data-tooltip="{{ $title }}" title="{{ $title }}">
        <span class="gcs-group__title">{{ $title }}</span>
        <span class="gcs-group__chevron" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M8 10l4 4 4-4"></path>
            </svg>
        </span>
    </button>
    <div class="gcs-group__submenu">
        {{ $slot }}
    </div>
</div>
