</main>

<footer class="site-footer">
    <div class="site-shell footer-grid">
        <div class="footer-brand">
            <span class="footer-logo-panel">
                <img src="<?php echo esc_url( icybreeze_asset_uri( 'assets/images/icybreeze-logo-brand-inverse.png' ) ); ?>" alt="IcyBreeze Aircon Cleaning — Cleaner Air, Cooler Life">
            </span>
            <p>Clean, reliable, professional, and affordable aircon care for Iligan City.</p>
            <div class="footer-social">
                <a href="tel:+639171232723" aria-label="Call IcyBreeze"><i class="ph-fill ph-phone"></i></a>
                <a href="mailto:hello@icybreeze.ph" aria-label="Email IcyBreeze"><i class="ph-fill ph-envelope-simple"></i></a>
            </div>
        </div>
        <div>
            <h3>Explore</h3>
            <a href="<?php echo esc_url( icybreeze_page_url( 'services' ) ); ?>">Services</a>
            <a href="<?php echo esc_url( icybreeze_page_url( 'how-it-works' ) ); ?>">How it works</a>
            <a href="<?php echo esc_url( icybreeze_booking_url( 'subscriptions' ) ); ?>">Care plans</a>
            <a href="<?php echo esc_url( icybreeze_page_url( 'coverage' ) ); ?>">Iligan coverage</a>
            <a href="<?php echo esc_url( icybreeze_page_url( 'faq' ) ); ?>">Frequently asked questions</a>
        </div>
        <div>
            <h3>Need help?</h3>
            <a href="tel:+639171232723">0917 123 2723</a>
            <a href="mailto:hello@icybreeze.ph">hello@icybreeze.ph</a>
            <a href="<?php echo esc_url( icybreeze_page_url( 'contact' ) ); ?>">Contact us</a>
            <span>Mon–Sat, 8 AM–5 PM</span>
        </div>
        <div class="footer-book">
            <h3>Ready for cleaner air?</h3>
            <p>Choose your unit type and preferred schedule in a few simple steps.</p>
            <a class="button button-yellow" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>">Book now <i class="ph ph-arrow-right"></i></a>
        </div>
    </div>
    <div class="site-shell footer-bottom">
        <span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> IcyBreeze Aircon Cleaning · Iligan City</span>
        <span><a href="<?php echo esc_url( icybreeze_booking_url( 'technician' ) ); ?>">Technician portal</a> · <a href="<?php echo esc_url( icybreeze_booking_url( 'admin' ) ); ?>">Admin portal</a></span>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

