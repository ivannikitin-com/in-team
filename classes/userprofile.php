<?php
/**
 * Класс реализует дополнительные поля в профиле сотрудника
 */
namespace INTEAM;
class UserProfile
{
	/**
	 * Определяет ассоциативный массив дополнительных мета-свойств пользователя:
	 * мета-поле => название
	 * 
	 * @return mixed
	 */
	static function get_extra_fields() {
		return array(
			'position'    => __( 'Должность', INTEAM ),
			'work_phone'  => __( 'Рабочий телефон', INTEAM ),
			'telegram' 	  => __( 'Телеграм', INTEAM ),
		);
	}
	
	/**
	 * Массив полей пользователя
	 * @var mixed
	 */
	private $extra_fields;

	/**
	 * Конструктор
	 * инициализирует параметры и загружает данные
	 */
	public function __construct()
	{
		// Добавляемые поля в профиль пользователя
		$this->extra_fields = apply_filters( 'inteam_extra_fields', self::get_extra_fields() );

		if ( ! empty( $this->extra_fields ) ) {
			// Отображение дополнительных полей в профиле
			add_action('show_user_profile', array( $this, 'render' ) );
			add_action('edit_user_profile', array( $this, 'render' ) );
			
			// Сохранение дополнительных полей в профиле
			add_action('personal_options_update', array( $this, 'save' ) );
			add_action('edit_user_profile_update', array( $this, 'save' ) );				
		}
	}
	
	/**
	 * Показывает дополнительные поля
	 */
	public function render( $user )
	{ ?>
		<h3><?php esc_html_e( 'Наша команда', INTEAM ) ?></h3>
		<table class="form-table">
			<?php foreach( $this->extra_fields as $field => $title ): ?>
				<tr>
					<th>
						<label for="<?php echo esc_attr( $field ) ?>">
							<?php echo esc_html( $title ) ?>
						</label>
					</th>
					<td>
						<input type="text" 
							name="<?php echo esc_attr( $field ) ?>" 
							id="<?php echo esc_attr( $field ) ?>" 
							value="<?php echo esc_attr( get_the_author_meta( $field, $user->ID ) ); ?>" 
							class="regular-text" /><br />
						<span class="description"><?php esc_html( $title ) ?></span>
					</td>
				</tr>
			<?php endforeach ?>
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
		foreach( $this->extra_fields as $field => $title ) {
			update_usermeta( $user_id, $field, ( isset( $_POST[ $field ] ) )
				? sanitize_text_field( $_POST[ $field ] )
				: ''
			);
		}
	}	
}
