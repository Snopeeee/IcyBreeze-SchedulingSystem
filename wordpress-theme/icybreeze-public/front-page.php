<?php get_header(); ?>

<section class="hero">
    <div class="site-shell">
        <div class="hero-content">
            <span class="hero-badge"><i class="ph-fill ph-snowflake"></i> Cleaner air, cooler life.</span>
            <h1>Breathe cleaner.<br><em>Live cooler.</em></h1>
            <p class="hero-copy">Professional aircon cleaning,<br>booked in minutes.</p>
            <div class="hero-assurances">
                <span><i class="ph ph-shield-check"></i><strong>Trained &amp; verified<br>aircon professionals</strong></span>
                <span><i class="ph ph-seal-check"></i><strong>Satisfaction<br>focused service</strong></span>
            </div>
            <div class="hero-actions">
                <a class="button button-navy" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Book a cleaning <i class="ph ph-arrow-right"></i></a>
            </div>
            <div class="hero-price-row">
                <span>Standard cleaning from <strong>₱700</strong></span>
                <span class="coverage-pill"><i class="ph-fill ph-map-pin"></i> Iligan City only</span>
            </div>
        </div>
    </div>
</section>

<section class="booking-band" id="how-booking-works">
    <div class="site-shell">
        <h2>How booking works</h2>
        <div class="booking-band-grid">
            <div class="booking-band-step">
                <div class="booking-band-icon"><i class="ph ph-calendar-check"></i></div>
                <div><strong>1. Choose a date &amp; time</strong><span>Pick a schedule that works for you.</span></div>
            </div>
            <i class="ph ph-caret-right booking-band-arrow"></i>
            <div class="booking-band-step">
                <div class="booking-band-icon"><i class="ph ph-clipboard-text"></i></div>
                <div><strong>2. We clean your aircon</strong><span>Your assigned technician follows your service location.</span></div>
            </div>
            <i class="ph ph-caret-right booking-band-arrow"></i>
            <div class="booking-band-step">
                <div class="booking-band-icon"><i class="ph ph-check-circle"></i></div>
                <div><strong>3. Breathe cleaner air</strong><span>Enjoy a cooler, healthier home.</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft" id="services">
    <div class="site-shell">
        <div class="section-heading">
            <span class="eyebrow">Standard cleaning prices</span>
            <h2>One careful clean, priced by unit type</h2>
            <p>No horsepower tiers and no confusing service levels. Choose your aircon type and see the exact regular price.</p>
        </div>
        <div class="pricing-showcase">
            <article class="service-card standard-service-card">
                <div class="service-card-top">
                    <div class="service-icon"><i class="ph ph-wind"></i></div>
                    <span class="popular-label">Only service</span>
                </div>
                <h3>Standard Cleaning</h3>
                <p>One careful standard-cleaning service for window and split-type air conditioners, including cleaning, drainage inspection, and an operational cooling check.</p>
                <ul>
                    <li><i class="ph-fill ph-check-circle"></i> Careful cleaning and inspection</li>
                    <li><i class="ph-fill ph-check-circle"></i> Drainage and operational cooling check</li>
                    <li><i class="ph-fill ph-check-circle"></i> Window and split-type units</li>
                </ul>
                <a class="button button-navy button-small" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Book standard cleaning <i class="ph ph-arrow-right"></i></a>
            </article>
            <div class="unit-price-grid">
                <article class="unit-price-card"><div><span>Window unit</span><h3>Window Type</h3></div><strong>₱700</strong></article>
                <article class="unit-price-card"><div><span>Window unit</span><h3>Window Type Inverter</h3></div><strong>₱750</strong></article>
                <article class="unit-price-card"><div><span>Split unit</span><h3>Split Type</h3></div><strong>₱950</strong></article>
                <article class="unit-price-card"><div><span>Split unit</span><h3>Split Type Inverter</h3></div><strong>₱1,000</strong></article>
                <p class="price-note"><i class="ph ph-info"></i> Prices are per aircon unit for Standard Cleaning within Iligan City.</p>
            </div>
        </div>
    </div>
</section>

<section class="section subscription-section" id="plans">
    <div class="site-shell subscription-layout">
        <div class="subscription-copy">
            <span class="eyebrow">IcyBreeze Care Plans</span>
            <h2>Stay cool without remembering the next cleaning.</h2>
            <p>Choose a recurring plan and we will keep your preferred service, schedule, Iligan address, and location ready for each visit.</p>
            <ul class="check-list">
                <li><i class="ph-fill ph-check-circle"></i> Friendly reminders before every visit</li>
                <li><i class="ph-fill ph-check-circle"></i> Save 5–10% on each scheduled cleaning</li>
                <li><i class="ph-fill ph-check-circle"></i> Pause or change the plan through our team</li>
            </ul>
            <a class="button button-navy" href="<?php echo esc_url( icybreeze_booking_url( 'subscriptions' ) ); ?>">Explore subscriptions <i class="ph ph-arrow-right"></i></a>
        </div>
        <div class="plan-stack">
            <article class="plan-card plan-card-featured">
                <span class="plan-kicker">Best routine</span>
                <div class="plan-icon"><i class="ph ph-arrows-clockwise"></i></div>
                <h3>Quarterly Care</h3>
                <p>Four scheduled cleanings each year for regularly used units.</p>
                <div class="plan-saving"><strong>Save 10%</strong><span>on every visit</span></div>
            </article>
            <article class="plan-card">
                <span class="plan-kicker">Light upkeep</span>
                <div class="plan-icon"><i class="ph ph-calendar-dots"></i></div>
                <h3>Biannual Care</h3>
                <p>Two scheduled cleanings each year for moderate household use.</p>
                <div class="plan-saving"><strong>Save 5%</strong><span>on every visit</span></div>
            </article>
        </div>
    </div>
</section>

<section class="section section-ice">
    <div class="site-shell">
        <div class="section-heading center">
            <span class="eyebrow">Explore IcyBreeze</span>
            <h2>Everything for an easier, cooler visit</h2>
            <p>Browse at your own pace, learn what to expect, or jump straight into the service you need.</p>
        </div>
        <div class="explore-grid">
            <a class="explore-card" href="<?php echo esc_url( icybreeze_page_url( 'services' ) ); ?>"><i class="ph ph-broom"></i><span><strong>Compare unit prices</strong><small>See the exact price for your aircon.</small></span><i class="ph ph-arrow-up-right"></i></a>
            <a class="explore-card" href="<?php echo esc_url( icybreeze_page_url( 'how-it-works' ) ); ?>"><i class="ph ph-list-checks"></i><span><strong>See how it works</strong><small>Know each step before the visit.</small></span><i class="ph ph-arrow-up-right"></i></a>
            <a class="explore-card" href="<?php echo esc_url( icybreeze_booking_url( 'subscriptions' ) ); ?>"><i class="ph ph-arrows-clockwise"></i><span><strong>Join a care plan</strong><small>Put regular cleaning on autopilot.</small></span><i class="ph ph-arrow-up-right"></i></a>
            <a class="explore-card" href="<?php echo esc_url( icybreeze_page_url( 'faq' ) ); ?>"><i class="ph ph-chat-circle-dots"></i><span><strong>Ask before booking</strong><small>Get quick answers to common questions.</small></span><i class="ph ph-arrow-up-right"></i></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="site-shell why-grid">
        <div class="why-visual">
            <div class="why-visual-card"><strong>Care you can feel</strong><span>Cleaner coils, better airflow, and a more comfortable room.</span></div>
        </div>
        <div>
            <div class="section-heading" style="margin-bottom:0">
                <span class="eyebrow">Why IcyBreeze</span>
                <h2>Thoughtful care for the air you live in</h2>
                <p>We make professional aircon cleaning straightforward—from the first click to the final cooling check.</p>
            </div>
            <div class="feature-list">
                <div class="feature-row"><div class="feature-icon"><i class="ph ph-user-focus"></i></div><div><h3>Professional technicians</h3><p>Mobile job accounts give our field team the schedule, customer notes, and approved location.</p></div></div>
                <div class="feature-row"><div class="feature-icon"><i class="ph ph-map-trifold"></i></div><div><h3>Smarter Iligan routes</h3><p>Scheduled stops keep their appointment times while route guidance starts from the technician’s current location.</p></div></div>
                <div class="feature-row"><div class="feature-icon"><i class="ph ph-lock-key"></i></div><div><h3>Location with consent</h3><p>You choose whether to share a precise service pin; it is used only to help the assigned technician arrive.</p></div></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="site-shell">
        <div class="section-heading center">
            <span class="eyebrow">Aircon care corner</span>
            <h2>Small habits, happier aircons</h2>
            <p>A few simple checks between professional cleanings can help airflow stay comfortable.</p>
        </div>
        <div class="care-grid">
            <article class="care-card"><span>01</span><i class="ph ph-wind"></i><h3>Watch the airflow</h3><p>Weak airflow, unusual smell, or dripping water are good reasons to schedule a check sooner.</p></article>
            <article class="care-card"><span>02</span><i class="ph ph-broom"></i><h3>Keep the area clear</h3><p>Give indoor and outdoor units breathing room so they can move air without obstruction.</p></article>
            <article class="care-card"><span>03</span><i class="ph ph-calendar-heart"></i><h3>Build a routine</h3><p>Heavy daily use benefits from a regular cleaning rhythm instead of waiting for cooling to drop.</p></article>
        </div>
    </div>
</section>

<section class="section section-ice">
    <div class="site-shell coverage-callout">
        <div>
            <span class="eyebrow">Iligan City service area</span>
            <h2>Local aircon care, routed around Iligan.</h2>
            <p>Appointments and subscriptions are available within Iligan City, Monday through Saturday.</p>
        </div>
        <div class="coverage-actions">
            <a class="button button-cyan" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Check a schedule</a>
            <a class="button button-ghost" href="<?php echo esc_url( icybreeze_page_url( 'coverage' ) ); ?>">View Iligan coverage</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
