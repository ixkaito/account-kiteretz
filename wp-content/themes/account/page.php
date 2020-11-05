<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

	<?php the_title(); ?>

	<?php the_content(); ?>

<?php endwhile; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>