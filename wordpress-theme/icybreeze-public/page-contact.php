<?php get_header(); ?>

<section class="page-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">We are happy to help</span>
        <h1>Contact IcyBreeze</h1>
        <p>Questions about a service, schedule, or location? Reach our local team during business hours.</p>
    </div>
</section>

<section class="section">
    <div class="site-shell content-grid">
        <article class="info-card">
            <div class="feature-icon"><i class="ph ph-phone-call"></i></div>
            <h2>Call or text</h2>
            <p>For appointment changes within 24 hours or an urgent booking question.</p>
            <a class="text-link" href="tel:+639171232723">0917 123 2723 <i class="ph ph-arrow-up-right"></i></a>
        </article>
        <article class="info-card">
            <div class="feature-icon"><i class="ph ph-envelope-simple"></i></div>
            <h2>Email</h2>
            <p>For general questions, care plans, and business inquiries.</p>
            <a class="text-link" href="mailto:hello@icybreeze.ph">hello@icybreeze.ph <i class="ph ph-arrow-up-right"></i></a>
        </article>
        <article class="info-card">
            <div class="feature-icon"><i class="ph ph-clock"></i></div>
            <h2>Service hours</h2>
            <p>Monday to Saturday<br>8:00 AM to 5:00 PM</p>
        </article>
        <article class="info-card">
            <div class="feature-icon"><i class="ph ph-map-pin"></i></div>
            <h2>Coverage</h2>
            <p>Iligan City only.</p>
            <a class="text-link" href="<?php echo esc_url( icybreeze_page_url( 'coverage' ) ); ?>">See Iligan coverage <i class="ph ph-arrow-right"></i></a>
        </article>
    </div>
</section>

<?php get_footer(); ?>

