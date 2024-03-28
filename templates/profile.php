<?php
/**
 * Шаблон профиля вывода члена команды
 *
 * Этот шаблон может быть переопределен копированием этого файла в тему сайта your_theme/in-team/profile.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); 

// This sets the $user variable
// https://codex.wordpress.org/Author_Templates
$user = ( isset($_GET[ 'author_name' ] ) ) ? get_user_by('slug', $author_name) : get_userdata( intval( $author) ) ;
?>

<section id="content">
	<?php
		/**
		 * inteam_before_main_content hook
		 */
		do_action( 'inteam_before_main_content' );
	?>
    <h2><?php echo apply_filters('inteam_display_name' , $user->display_name, $user ); ?></h2>

	<?php
		// Аватар пользователя
		// https://wp-kama.ru/function/get_avatar
		echo apply_filters('inteam_avatar', 
			get_avatar( 
				$user->user_email, 
				apply_filters('inteam_avatar_size', 150, $user),
				apply_filters('inteam_avatar_default', '', $user),
				apply_filters('inteam_avatar_alt', $user->display_name, $user),
				apply_filters('inteam_avatar_args', array(), $user )
			), 	$user );
	?>

	<div>
		<?php
			/**
			 * inteam_before_contacts hook
			 */
			do_action( 'inteam_before_contacts' );
		?>
		<h3><?php esc_html_e( 'Контакты', INTEAM ) ?></h3>
		<?php 
			// Формируем массив контактных данных пользователя
			$contacts = apply_filters( 'inteam_contacts', array(
				__( 'E-mail', INTEAM ) 			=> $user->user_email,
				__( 'Рабочий телефон', INTEAM ) => get_user_meta( $user->ID, 'work_phone', true ),
				__( 'Личный телефон', INTEAM ) => get_user_meta( $user->ID, 'billing_phone', true ),
			), $user );
		?>
		<table>
			<tbody>
				<?php foreach ( $contacts as $key => $value ):
						if ( empty( $value ) ) continue ?>
					<tr>
						<td><?php echo esc_html( $key ) ?></td>
						<td><?php echo esc_html( $value ) ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
		<?php
			/**
			 * inteam_before_contacts hook
			 */
			do_action( 'inteam_after_contacts' );
		?>		
	</div>

	<?php
		/**
		 * inteam_before_user_description hook
		 */
		do_action( 'inteam_before_user_description' );
	?>	
	<div><?php echo apply_filters('inteam_user_description' , $user->user_description, $user ); ?></div>
	<?php
		/**
		 * inteam_after_user_description hook
		 */
		do_action( 'inteam_after_user_description' );
	?>

	<?php  if ( have_posts() && apply_filters('inteam_show_user_posts' , true ) ): ?>
		<?php
			/**
			 * inteam_before_posts hook
			 */
			do_action( 'inteam_before_posts' );
		?>		
		<h3><?php esc_html_e( 'Публикации', INTEAM ) ?></h3>
		<ul>
			<?php while ( have_posts() ) : the_post(); ?>
				<li>
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>">
						<?php echo apply_filters('inteam_post_title', get_the_title(), get_post(), $user ) ?>
					</a>
					<?php echo apply_filters('inteam_post_description', get_the_time('d.m.Y'), get_post(), $user ) ?>
				</li>
			<?php endwhile ?>
		</ul>
		<?php
			/**
			 * inteam_after_posts hook
			 */
			do_action( 'inteam_after_posts' );
		?>		
	<?php endif ?> 
</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
