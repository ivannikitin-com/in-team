<?php
/**
 * The Template for displaying department content
 *
 * This template can be overridden by copying it to yourtheme/in-team/profile.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header(); ?>
<section>
	<?php
		/**
		 * inteam_before_main_content hook
		 */
		do_action( 'inteam_before_main_content' );
	?>
	
	<?php while ( have_posts() ) : the_post(); ?>
		<div class="single in-team">
			<h1><?php the_title() ?></h1>
			<?php if ( has_post_thumbnail() ): ?>
				<?php the_post_thumbnail() ?>
			<?php endif; ?>
			<?php the_content(); ?>
		</div>
	<?php endwhile; // end of the loop. ?>

	<?php
		/**
		 * inteam_after_main_content hook
		 */
		do_action( 'inteam_after_main_content' );
	?>

	<?php
		/**
		 * inteam_sidebar hook.
		 */
		do_action( 'inteam_sidebar' );
	?>
</section>
<?php get_footer(); ?>