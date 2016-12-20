<?php
/**
 * Класс реализует дополнительные поля в профиле сотрудника
 */
namespace INTEAM;
class UserProfile
{
	/**
	 * Поле Должность
	 * @static
	 */
	const FIELD_POSITION = 'employeeTitle';	
	
/* ------------------------------------- Методы ------------------------------------*/	
	/**
	 * Конструктор
	 * инициализирует параметры и загружает данные
	 * @param INTEAM\Plugin plugin		Ссылка на основной объект плагина 
	 */
	public function __construct( $plugin )
	{	
		// Отображение дополнительных полей в профиле
		add_action('show_user_profile', array( $this, 'render' ) );
		add_action('edit_user_profile', array( $this, 'render' ) );
		
		// Сохранение дополнительных полей в профиле
		add_action('personal_options_update', array( $this, 'save' ) );
		add_action('edit_user_profile_update', array( $this, 'save' ) );		
	}
	
	/**
	 * Показывает дополнительные поля
	 */
	public function render( $user )
	{ ?>
		<h3><?php esc_html_e( 'Team Member', INTEAM ) ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="employeeTitle"><?php esc_html_e( 'Position', INTEAM ) ?></label></th>
				<td>
					<input type="text" name="employeeTitle" id="employeeTitle" value="<?php echo esc_attr( get_the_author_meta( self::FIELD_POSITION, $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description"><?php esc_html_e( 'Team position', INTEAM ) ?></span>
				</td>
			</tr>
		</table>
	<?php 		
	}	
	
	/**
	 * Сохраняет дополнительные поля
	 */
	public function save( $user_id )
	{ 
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;
		
		// Обновление полей
		update_usermeta( $user_id, 'employeeTitle', $_POST['employeeTitle'] );	
	}	
}
