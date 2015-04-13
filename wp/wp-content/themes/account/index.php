<?php if( ! is_user_logged_in() ):

	wp_redirect( wp_login_url() );
	exit;

else:

	get_header();

		if ( have_posts() ) : ?>

			<ul class="index">

				<?php while ( have_posts() ) : the_post(); ?>

					<li class="<?php the_status(); ?>">

						<a href="<?php the_permalink(); ?>">

							<p>No.<?php the_account_ID(); ?> | <?php the_time( 'Y年m月d日' ); ?> | <?php the_field( 'client' ); ?>　<?php the_honorific(); ?></p>

							<h1>
								<?php the_title(); ?>
								<span class="total">&yen;<?php echo number_format( get_total() ); ?></span>
							</h1>


						</a>

					</li>

				<?php endwhile; ?>

			</ul>

		<?php endif;

	get_footer();

endif;