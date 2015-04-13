<!DOCTYPE html>
<!--[if lt IE 7]>		<html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 7]>			<html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]>			<html class="no-js lt-ie9" <?php language_attributes(); ?>><![endif]-->
<!--[if gt IE 8]><!-->	<html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php head_title(); ?></title>
<meta name="description" content="<?php echo get_bloginfo('description'); ?>" />
<meta name="keywords" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<meta property="og:type" content="<?php echo is_front_page() ? 'website' : 'article'; ?>" />
<meta property="og:url" content="http://<?php echo is_404() ? home_url() : $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" />
<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />
<?php

	if (is_singular() && ! is_archive() && ! is_front_page() && ! is_home()){
		if(have_posts()): while(have_posts()): the_post();
			echo '<meta property="og:title" content="'.the_title('', '', false).'" />'."\n";
			echo '<meta property="og:description" content="'.mb_substr(get_the_excerpt(), 0, 100).'" />'."\n";
		endwhile; endif;
	} else {
			echo '<meta property="og:title" content="'.get_bloginfo('name').'" />'."\n";
			echo '<meta property="og:description" content="'.get_bloginfo('description').'" />'."\n";
	}

	$post_content	= $post->post_content;
	$search_pattern	= '/<img.*?src=(["\'])(.+?)\1.*?>/i';
	if (has_post_thumbnail() && ! is_archive() && ! is_front_page() && ! is_home()){
		$image_id	= get_post_thumbnail_id();
		$image		= wp_get_attachment_image_src( $image_id, 'full');
		echo '<meta property="og:image" content="'.$image[0].'" />'."\n";
	} else if ( preg_match( $search_pattern, $post_content, $imgurl ) && ! is_archive() && ! is_front_page() && ! is_home()) {
		echo '<meta property="og:image" content="'.$imgurl[2].'" />'."\n";
	} else {
		echo '<meta property="og:image" content="'.get_template_directory_uri().'/_assets/images/og-default.png" />'."\n";
	}

?>
<meta property="fb:admins" content="1047363919" />
<!--<meta property="fb:app_id" content="" />-->

<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php echo get_template_directory_uri(); ?>/_assets/images/favicon.ico">

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/_assets/css/reset.css" />
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/_assets/css/style.css" />
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/_assets/js/modernizr-2.6.2.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">window.jQuery || document.write('<script src="<?php echo get_template_directory_uri(); ?>/_assets/js/jquery-1.10.2.min.js"><\/script>')</script>
<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/_assets/js/IE9.js"></script>
<![endif]-->
<?php
	wp_deregister_script('jquery');
	wp_head();
?>
</head>
<body <?php body_class(); ?>>
<!--[if lt IE 7]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<div id="container" class="container">

	<div id="main" class="main">