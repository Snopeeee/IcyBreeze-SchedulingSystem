<?php get_header(); ?>

<section class="page-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">Our local service area</span>
        <h1>Aircon cleaning across Iligan City</h1>
        <p>IcyBreeze currently accepts one-time and recurring home aircon cleaning appointments within Iligan City only.</p>
    </div>
</section>

<section class="section">
    <div class="site-shell">
        <div class="section-heading">
            <span class="eyebrow">Popular service locations</span>
            <h2>Iligan barangays we regularly route around</h2>
            <p>This is a helpful sample, not a limit. Enter any Iligan City barangay when you book.</p>
        </div>
        <div class="city-grid">
            <?php foreach ( array( 'Pala-o', 'Tibanga', 'Tubod', 'Mahayahay', 'San Miguel', 'Del Carmen', 'Hinaplanon', 'Kauswagan', 'Tambacan', 'Santiago', 'Suarez', 'Buru-un' ) as $barangay ) : ?>
                <div class="city-card"><i class="ph-fill ph-map-pin"></i><?php echo esc_html( $barangay ); ?></div>
            <?php endforeach; ?>
        </div>
        <div class="content-grid" style="margin-top:42px">
            <article class="info-card">
                <h2>Service hours</h2>
                <p>Appointments are available Monday through Saturday at 8:00 AM, 10:00 AM, 1:00 PM, and 3:00 PM. Actual completion time varies by quantity.</p>
            </article>
            <article class="info-card">
                <h2>Share a precise service pin</h2>
                <p>You can optionally share your current location during booking. Only the office and assigned technician use it to find the service address and plan the day’s route.</p>
                <a class="text-link" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Book in Iligan <i class="ph ph-arrow-right"></i></a>
            </article>
        </div>
    </div>
</section>

<?php get_footer(); ?>

