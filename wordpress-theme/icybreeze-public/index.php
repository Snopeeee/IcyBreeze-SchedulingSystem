<?php get_header(); ?>
<section class="page-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">IcyBreeze Aircon Cleaning</span>
        <h1><?php bloginfo( 'name' ); ?></h1>
        <p><?php bloginfo( 'description' ); ?></p>
    </div>
</section>
<section class="section wordpress-content">
    <div class="site-shell">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="info-card">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No content is available yet.</p>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>

