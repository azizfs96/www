@props([
    'title' => '',
])

<section class="gcs-section">
    @if($title !== '')
        <div class="gcs-section__title">{{ $title }}</div>
    @endif
    <div class="gcs-section__body">
        {{ $slot }}
    </div>
</section>
