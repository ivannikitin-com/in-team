<?php
/**
 * Шаблон вывода члена команды
 *
 * Этот шаблон может быть переопределен копированием этого файла в тему сайта your_theme/in-team/member.php
 * 
 * Шаблон выполняется в контексте метода shortcode::inteam_members()
 * В шаблоне определены следующие переменные:
 *  WP_User $user           Текущий член команды. Экземпляр WP_User
 *  mixed   $users          Все найденные члены команды. Массив WP_User
 *  mixed   $atts           Переданные атрибуты шорткоду
 *  string  $template       Путь к текущему файлу шаблона
 *  string  $profile_url    URL профилей пользователей, страница Наша команда
 */
?>
<div id="in-team-member-<?php echo esc_attr( $user->user_nicename )?>" class="in-team in-team-member">
    <?php
		/**
		 * inteam_member_before hook
		 */
		do_action( 'inteam_member_before' );
	?>
    <a href="<?php echo $profile_url . '/' . esc_attr( $user->user_nicename ) ?>">
        <?php
        // Аватар пользователя
        // https://wp-kama.ru/function/get_avatar
        echo apply_filters('inteam_member_avatar', 
            get_avatar( 
                $user->user_email, 
                apply_filters('inteam_member_avatar_size', 150, $user),
                apply_filters('inteam_member_avatar_default', '', $user),
                apply_filters('inteam_member_avatar_alt', $user->display_name, $user),
                apply_filters('inteam_member_avatar_args', array(), $user )
            ), 	$user );
        ?>
        <h3><?php echo apply_filters('inteam_member_display_name' , $user->display_name, $user ); ?></h3>
        <div class="position">
            <?php echo apply_filters('inteam_member_position' , $user->position, $user ); ?>
        </div>
    </a>
    <?php
		/**
		 * inteam_member_after hook
		 */
		do_action( 'inteam_member_after' );
	?>
</div>