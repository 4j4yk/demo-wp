<?php
/**
 * Minimal front controller template.
 *
 * @package DemoMinimal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<main class="demo-wrap">
	<h1><?php bloginfo( 'name' ); ?></h1>
	<p><?php bloginfo( 'description' ); ?></p>
	<p>Custom minimal theme deployed via GitHub Actions.</p>

	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p>No content found yet.</p>
	<?php endif; ?>
</main>
<?php wp_footer(); ?>
</body>
</html>

