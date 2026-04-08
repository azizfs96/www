@props([
    'collapsed' => false,
])

<aside class="gcs-sidebar {{ $collapsed ? 'is-collapsed' : '' }}" id="gcsSidebar">
    <div class="gcs-sidebar__top">
        {{ $top ?? '' }}
    </div>

    <div class="gcs-sidebar__quick">
        {{ $quick ?? '' }}
    </div>

    <nav class="gcs-sidebar__nav">
        {{ $slot }}
    </nav>

    <div class="gcs-sidebar__footer">
        {{ $footer ?? '' }}
    </div>
</aside>
