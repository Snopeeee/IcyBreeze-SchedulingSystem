<?php get_header(); ?>

<section class="page-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">A refreshingly simple process</span>
        <h1>From booking to cooler air</h1>
        <p>Know what to expect at every step of your IcyBreeze appointment.</p>
    </div>
</section>

<section class="section">
    <div class="site-shell">
        <div class="steps-grid">
            <article class="step-card"><span class="step-number">01</span><div class="step-icon"><i class="ph ph-list-checks"></i></div><h3>Tell us about your unit</h3><p>Choose your aircon unit type, quantity, and preferred schedule.</p></article>
            <article class="step-card"><span class="step-number">02</span><div class="step-icon"><i class="ph ph-envelope-open"></i></div><h3>Receive your confirmation</h3><p>We save your booking immediately and give you a secure link to manage it.</p></article>
            <article class="step-card"><span class="step-number">03</span><div class="step-icon"><i class="ph ph-user-check"></i></div><h3>Welcome your technician</h3><p>Our team arrives in your selected window, protects the area, and starts the service.</p></article>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="site-shell why-grid">
        <div>
            <div class="section-heading" style="margin-bottom:0">
                <span class="eyebrow">During the visit</span>
                <h2>A careful clean, not a rushed wash</h2>
                <p>We inspect first, clean the right components, flush the drain, and test cooling before we leave.</p>
            </div>
            <ul class="check-list">
                <li><i class="ph-fill ph-check-circle"></i> Work area is prepared and protected</li>
                <li><i class="ph-fill ph-check-circle"></i> Filters, covers, coils, and accessible components are cleaned</li>
                <li><i class="ph-fill ph-check-circle"></i> Drainage and air flow are checked</li>
                <li><i class="ph-fill ph-check-circle"></i> Unit is tested and service notes are shared</li>
            </ul>
            <a class="button button-navy" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Start a booking</a>
        </div>
        <div class="why-visual"></div>
    </div>
</section>

<?php get_footer(); ?>

