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
<main class="paper">
	<header class="site-head">
		<h1 class="site-title">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		</h1>
		<?php if ( get_bloginfo( 'description' ) ) : ?>
			<p class="site-tagline"><?php bloginfo( 'description' ); ?></p>
		<?php endif; ?>
	</header>

	<section class="posts">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="post-card">
					<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="post-meta"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></p>
					<div class="post-excerpt"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<p class="empty">No content found yet.</p>
		<?php endif; ?>
	</section>

	<footer class="site-foot">
		<p>Demo Minimal theme. Clean, paper-like fallback for the WordPress deploy demo.</p>
	</footer>
</main>
<?php wp_footer(); ?>
</body>
</html>
