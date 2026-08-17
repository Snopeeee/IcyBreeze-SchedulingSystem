<?php get_header(); ?>

<section class="page-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">Clear care, exact prices</span>
        <h1>Standard aircon cleaning</h1>
        <p>Every booking receives the same careful Standard Cleaning. Your price depends only on the aircon unit type—not horsepower.</p>
    </div>
</section>

<section class="section">
    <div class="site-shell pricing-showcase">
        <article class="service-card standard-service-card">
            <div class="service-card-top">
                <div class="service-icon"><i class="ph ph-wind"></i></div>
                <span class="popular-label">Standard Cleaning only</span>
            </div>
            <h3>Standard Cleaning</h3>
            <p>One careful standard-cleaning service for window and split-type air conditioners, including cleaning, drainage inspection, and an operational cooling check.</p>
            <ul>
                <li><i class="ph-fill ph-check-circle"></i> Professional cleaning and inspection</li>
                <li><i class="ph-fill ph-check-circle"></i> Drainage and performance test</li>
                <li><i class="ph-fill ph-check-circle"></i> About 75 minutes for the first unit</li>
            </ul>
            <a class="button button-navy" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Choose your unit type <i class="ph ph-arrow-right"></i></a>
        </article>
        <div class="unit-price-grid">
            <article class="unit-price-card"><div><span>Standard Cleaning</span><h3>Window Type</h3></div><strong>₱700</strong></article>
            <article class="unit-price-card"><div><span>Standard Cleaning</span><h3>Window Type Inverter</h3></div><strong>₱750</strong></article>
            <article class="unit-price-card"><div><span>Standard Cleaning</span><h3>Split Type</h3></div><strong>₱950</strong></article>
            <article class="unit-price-card"><div><span>Standard Cleaning</span><h3>Split Type Inverter</h3></div><strong>₱1,000</strong></article>
            <p class="price-note"><i class="ph ph-map-pin"></i> Regular prices are per unit and apply throughout Iligan City.</p>
        </div>
    </div>
</section>

<section class="section section-ice">
    <div class="site-shell coverage-callout">
        <div>
            <span class="eyebrow">Have a window or split unit?</span>
            <h2>Pick a convenient Iligan schedule.</h2>
            <p>Tell us whether the unit is inverter or non-inverter and the booking form calculates the total instantly.</p>
        </div>
        <div class="coverage-actions">
            <a class="button button-cyan" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Book now</a>
            <a class="button button-ghost" href="<?php echo esc_url( icybreeze_page_url( 'contact' ) ); ?>">Ask IcyBreeze</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
