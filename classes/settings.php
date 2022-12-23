<?php
/**
 * Класс реализует загрузку и сохранение любых параметров
 */
namespace INTEAM;
class Settings
{
	/**
	 * Используемые настройки
	 */
	const TEAM_PAGE = 'inteam-page';	// Страница вывода команды
	const TEAM_SLUG = 'inteam-slug';	// Слаг страницы команды
	const TEAM_ROLE = 'inteam-role';	// Роль пользователей команды

	/**
	 * Название опции в Wordpress
	 * @var string
	 */
	protected $_name;	
	
	
	/**
	 * Массив хранения параметров
	 * @var mixed
	 */
	protected $_params;
	
	/**
	 * Конструктор
	 * инициализирует параметры и загружает данные
	 */
	public function __construct()
	{
		// Название опции в Wordpress,используется имя класса
		$this->_name = get_class( $this );
		
		// Загружаем параметры
		$this->load();
		
		// Если это работа в админке
		if ( is_admin() )
		{			
			// Меню настроек
			add_action( 'admin_menu', array( $this, 'add_menu' ) );

			// Кастомизация профиля пользователя
			add_action( 'show_user_profile', array( $this, 'customize_profile' ) );
		}
	}
	
	/**
	 * Загрузка параметров в массив из БД Wordpress
	 */
	public function load()
	{
		$this->_params = get_option( $this->_name, array() );
	}
	
	/**
	 * Сохранение параметров в БД Wordpress
	 */
	public function save()
	{
		// Сохранение параметров
		update_option( $this->_name, $this->_params );

		// Установка правил перезаписи URL
		Plugin::get()->rewrite_author_base();

		// Сброс постоянных ссылок
		flush_rewrite_rules();
	}

	/**
	 * Чтение параметра
	 * @param string	$param		Название параметра
	 * @param mixed 	$default	Значение параметра по умолчанию, если его нет или он пустой
	 * @return mixed				Возвращает параметр
	 */
	public function get( $param, $default = false )
	{
		if ( ! isset( $this->_params[ $param ] ) )
			return $default;
		
		if ( empty( $this->_params[ $param ] ) )
			return $default;
		
		return $this->_params[ $param ];
	}
	
	/**
	 * Сохранение параметра
	 * @param string	$param		Название параметра
	 * @param mixed 	$value		Значение параметра
	 */
	public function set( $param, $value )
	{
		$this->_params[ $param ] = $value;
	}
	
	/**
	 * Возвращает URL перезаписи страниц командны 
	 */	
	public function get_base_slug() {
		return $this->get( self::TEAM_SLUG );
	}

	/**
	 * Возвращает роль пользователя команды 
	 */	
	public function get_team_role() {
		return $this->get( self::TEAM_ROLE );
	}	

	/** ==========================================================================================
	 * Добавляет пункт меню настроек плагина в меню пользователей
	 */
	public function add_menu()
	{
		// Если у пользователя нет прав, ничего не делаем
		if ( ! current_user_can( 'edit_users' ) ) return;

		// Добавляем пункт меню
		add_users_page(
			__( 'Наша команда', INTEAM ),			// Название страницы
			__( 'Наша команда', INTEAM ),			// Название меню
			'manage_options',						// Права на редактирование
			INTEAM,									// Слаг настроек
			array( $this, 'show_settings_page' ),	// Функция отрисовки
			3										// Позиция в меню
		);	
	}
	
	/** 
	 * Выводит страницу настроек плагина
	 */
	public function show_settings_page( )
	{	
		$nonce_field = INTEAM;
		$nonce_action = 'save-settings';
		$nonce_error = false;
		
		// Обработка формы
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			if ( ! isset( $_POST[$nonce_field] ) || ! wp_verify_nonce( $_POST[$nonce_field], $nonce_action ) ) 
			{
				$nonce_error = true;
			} 
			else 
			{
				// Сохраним переданные данные настроек
				$this->set( self::TEAM_PAGE, sanitize_text_field( $_POST[ self::TEAM_PAGE ] ) );
				$this->set( self::TEAM_ROLE, sanitize_text_field( $_POST[ self::TEAM_ROLE ] ) );

				// Узнаем слаг страницы команды
				$this->set( self::TEAM_SLUG, get_post_field( 'post_name', $this->get(self::TEAM_PAGE) ) );

				// Сохраним настройки
				$this->save();
			}		
		}
		
?>
<h1><?php esc_html_e( 'Наша команда', INTEAM ) ?></h1>
<p><?php esc_html_e( 'Плагин in-team управляет выводом сотрудников нашей команды', INTEAM ) ?></p>
<?php if ( $nonce_error ) _e( 'Ошибка! Поле nonce устарело, попробуйте еще раз.', INTEAM ) ?>
<hr>
<p><?php esc_html_e( 'Для настройки нужно создать страницу вывода команды сотрудников и указать ее здесь в поле ниже.', INTEAM ) ?></p>
<p><?php esc_html_e( 'Например, вы создали страницу со слагом \'team\'. Плагин сформирует вывод отдельных членов команды со слагом \'team/name/\', где name -- это слаг или короткое имя пользователя.', INTEAM ) ?></p>
<p><?php esc_html_e( 'Это будет выполняться только для пользователей с указанной ролью, для всех остальных пользователей будет выполнена переадресация на общую страницу команды.', INTEAM ) ?></p>
<hr>

<form id="inteam-settings" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
	<?php wp_nonce_field( $nonce_action, $nonce_field ) ?>
	
	<div class="inteam-field">
		<label for="<?php echo self::TEAM_PAGE ?>"><?php esc_html_e( 'Страница вывода команды', INTEAM ) ?></label>
		<div class="inteam-input">
			<?php wp_dropdown_pages( array( 
				'id' 		=> self::TEAM_PAGE, 
				'name' 		=> self::TEAM_PAGE, 
				'selected' 	=> $this->get( self::TEAM_PAGE ) 
				) ); 
			?>
		</div>
	</div>
	
	<div class="inteam-field">
		<label for="<?php echo self::TEAM_ROLE ?>"><?php esc_html_e( 'Роль пользователей команды', INTEAM ) ?></label>
		<div class="inteam-input">
			<select id="<?php echo self::TEAM_ROLE ?>" name="<?php echo self::TEAM_ROLE ?>">
				<?php wp_dropdown_roles( $this->get( self::TEAM_ROLE ) ) ?>
			</select>
		</div>
	</div>
	
	<?php submit_button() ?>
</form>
<?php	
	}

	/**
	 * Добавляем редактор биографии в профиль пользователя
	 * https://wordpress.stackexchange.com/questions/5012/how-to-use-tinymce-for-user-biographical-info
	 * https://wp-kama.ru/question/wp-editor-initialize
	 */
	public function customize_profile() {
		wp_enqueue_editor();
		?>
		<script>
			document.addEventListener("DOMContentLoaded", function(event) {
				var id = 'description';

				wp.editor.initialize(id, {
					tinymce: {
						wpautop: true
					},
					quicktags: true
				});
			});
		</script>
		<?php
	}
}