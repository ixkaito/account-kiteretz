<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

	<?php

		$html = '';

		if ( get_status() === 'quotation' ) {
			$doctype      = '御見積書';
			$statement    = '下記の通りお見積り申し上げます。';
			$special_note = get_field( 'special-note' ) ? get_field( 'special-note' ) : '';
		} elseif ( get_status() === 'bill' ) {
			$doctype      = '御請求書';
			$statement    = '下記の通りご請求申し上げます。';
			$special_note = get_field( 'bank' ) ? get_field( 'bank' ) : '';
		} elseif ( get_status() === 'receipt' ) {
			$doctype      = '領収書';
			$statement    = '';
			$special_note = '上記正に領収いたしました';
		}

		$i    = 0;
		$rows = get_field( 'table' );

		if ( $rows ) {
			foreach ( $rows as $key => $row ) {
				$i++;
				$sum = $row['number'] * $row['price'];
				$html .= '<tr>
					<td></td>
					<td class="item">' . $row['item'] . '</td>
					<td class="number">' . $row['number'] . '</td>
					<td class="unit">' . $row['unit'] . '</td>
					<td class="price">' . number_format( $row['price'] ) . '</td>
					<td class="price">' . number_format( $sum ) . '</td>
					<td class="note">' . $row['note'] . '</td>
				</tr>'."\n";
			}
		}

		while( $i < 24 ){
			$html .= '<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>';
			$i++;
		}

		$html .= '<tr class="last">
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>';

		if( get_field( 'tax' ) ){

			$html .= '<tr class="subtotal">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="label">小計</td>
				<td class="price">' . number_format( get_subtotal() ) . '</td>
				<td></td>
			</tr>
			<tr class="tax">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="label">消費税</td>
				<td class="price">' . number_format( get_tax() ) . '</td>
				<td></td>
			</tr>';

		}

		$html .= '<tr class="total">
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td class="label">合計（税込）</td>
			<td class="price">' . number_format( get_total() ) . '</td>
			<td></td>
		</tr>';

	?>

	<div class="docInfo">
		<p class="id">No. <?php the_account_ID(); ?></p>
		<p class="date"><?php
			$date = DateTime::createFromFormat( 'Ymd', get_field( get_status() . '_date' ) );
			echo $date ? $date->format( 'Y/m/d' ) : get_the_time( 'Y/m/d' );
		?></p>

		<p class="logo"><?php the_field( 'logo' ); ?></p>
		<p class="corpInfo"><?php the_field( 'corp-info' ); ?></p>
	</div>

	<div class="summary">

		<h1 class="doctype <?php the_status(); ?>"><?php echo $doctype; ?></h1>

		<h2 class="client"><span class="clientName"><?php the_field('client'); ?></span>　<span><?php the_honorific(); ?></span></h2>
		<p><?php echo $statement; ?></p>

		<h3 class="title">件名　<?php the_title(); ?></h3>

		<p class="total"><b>合計（税込）　&yen;<?php echo number_format( get_total() ); ?></b></p>

		<p class="specialNote"><?php echo $special_note; ?></p>

	</div>

	<table class="priceTable">

		<tr class="thead">
			<th class="check"></th>
			<th class="item">項目</th>
			<th class="number">数量</th>
			<th class="unit">単位</th>
			<th class="price">単価</th>
			<th class="price">金額</th>
			<th class="note">備考</th>
		</tr>

		<?php echo $html; ?>

	</table>

<?php endwhile; ?>

<?php get_footer(); ?>