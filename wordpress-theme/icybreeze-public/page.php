<?php get_header(); ?>
<?php while ( have_posts() ) : the_post(); ?>
    <section class="page-hero">
        <div class="site-shell page-intro">
            <span class="eyebrow">IcyBreeze Aircon Cleaning</span>
            <h1><?php the_title(); ?></h1>
        </div>
    </section>
    <section class="section wordpress-content">
        <div class="site-shell entry-content"><?php the_content(); ?></div>
    </section>
<?php endwhile; ?>
<?php get_footer(); ?>

