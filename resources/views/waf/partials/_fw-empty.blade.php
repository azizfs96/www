<div class="cf-empty">
    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3l7 3v6c0 4.6-2.8 7.7-7 9-4.2-1.3-7-4.4-7-9V6l7-3z"/>
    </svg>
    <h4>No {{ $label }} yet</h4>
    <p>This scope has no {{ $label }}. Create one to start filtering traffic.</p>
    <button type="button" class="cf-btn cf-btn-primary" onclick="cfToggleCreate('{{ $type }}')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Create rule
    </button>
</div>
