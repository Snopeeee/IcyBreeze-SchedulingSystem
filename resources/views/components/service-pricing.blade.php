@props(['service', 'unitTypes'])

<div class="pricing-showcase">
    <article class="service-card cleaning-overview-card">
        <div class="service-card-top">
            <div class="service-icon"><i class="ph ph-wind" aria-hidden="true"></i></div>
            <span class="popular-label">Professional service</span>
        </div>
        <h3>{{ $service->name }}</h3>
        <p>{{ $service->description }}</p>
        <ul>
            <li><i class="ph-fill ph-check-circle" aria-hidden="true"></i> Cleaning and visual inspection</li>
            <li><i class="ph-fill ph-check-circle" aria-hidden="true"></i> Drainage and cooling performance check</li>
            <li><i class="ph-fill ph-check-circle" aria-hidden="true"></i> Estimated duration: {{ $service->duration_minutes }} minutes for the first unit</li>
        </ul>
        <a class="text-link" href="{{ route('how') }}">Review Service Process <i class="ph ph-arrow-right" aria-hidden="true"></i></a>
    </article>
    <div class="unit-price-grid">
        @foreach($unitTypes as $unitType)
            <article class="unit-price-card">
                <div><span>{{ str_contains($unitType->name, 'Window') ? 'Window unit' : 'Split unit' }}</span><h3>{{ $unitType->name }}</h3></div>
                <div class="unit-price-bottom">
                    <div class="unit-price-amount"><strong>{{ $unitType->formatted_price }}</strong><small>per unit</small></div>
                    <a class="unit-book-link" href="{{ route('booking.create', ['unit' => $unitType->id]) }}" aria-label="Schedule {{ $unitType->name }} service">Schedule <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </article>
        @endforeach
        <p class="price-note"><i class="ph ph-info" aria-hidden="true"></i> Rates are shown per unit and apply to appointments within Iligan City.</p>
    </div>
</div>
