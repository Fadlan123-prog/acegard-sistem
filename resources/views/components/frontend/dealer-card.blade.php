@props(['dealer'])

<div class="ace-card overflow-hidden h-100">
    <div class="position-relative" style="aspect-ratio: 16/10; background: var(--ace-bg-card);">
        @if($dealer->image)
            <img src="{{ asset('storage/' . $dealer->image) }}" alt="{{ $dealer->name }}" class="w-100 h-100" style="object-fit: cover;">
        @else
            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-uppercase fw-semibold ace-muted">
                Image
            </div>
        @endif
    </div>

    <div class="p-4">
        <div class="fw-semibold mb-1">{{ $dealer->name }}</div>
        @if($dealer->location)
            <div class="small ace-muted mb-3">{{ $dealer->location }}</div>
        @endif

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $dealer->whatsapp_link ?? '#' }}" class="ace-pill ace-pill-dark whatsapp-btn" target="_blank">Admin</a>

            @if($dealer->maps_url)
                <a href="{{ $dealer->maps_url }}" class="ace-pill" target="_blank">Maps</a>
            @endif

            @if($dealer->instagram_url)
                <a href="{{ $dealer->instagram_url }}" class="ace-pill" target="_blank">Instagram</a>
            @endif
        </div>
    </div>
</div>
