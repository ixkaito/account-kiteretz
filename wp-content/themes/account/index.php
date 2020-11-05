<?php if( ! is_user_logged_in() ):

	wp_redirect( wp_login_url() );
	exit;

else:

	get_header();

		if ( have_posts() ) : ?>

			<div class="filter js-filter">
				<button data-show="all">すべて</button>
				<button data-show="bill">請求</button>
				<button data-show="quotation">見積</button>
				<button data-show="receipt">領収</button>
				<button data-show="payment">支払</button>
				<button data-show="unchecked">未記帳</button>
			</div>

			<ul class="index js-index">

				<?php while ( have_posts() ) : the_post(); ?>

					<li class="<?php the_status(); echo get_field( 'account-booking' ) ? ' checked' : ' unchecked' ?> all">

						<a href="<?php the_permalink(); ?>">

							<p>No.<?php the_account_ID(); ?>　｜　<?php account_date(); ?>　｜　<?php the_field( 'client' ); ?>　<?php the_honorific(); ?></p>

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
