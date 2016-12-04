<?php
/**
 * Класс реализует загрузку и сохранение любых параметров
 */
namespace INTEAM;
class Settings
{
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
	 * @param string optionName		Название опции в Wordpress, по умолчанию используется имя класса
	 */
	public function __construct( $optionName = '' )
	{
		if ( empty ( $optionName ) ) $optionName = get_class( $this );
		$this->_name = $optionName;
		
		// Загружаем параметры
		$this->load();
		
		// Если это работа в админке
		if ( is_admin() )
		{
			// Стили для админки
			wp_enqueue_style( INTEAM, INTEAM_URL . 'css/admin.css' );
			
			// Страница настроек
			add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
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
		update_option( $this->_name, $this->_params );
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
	 * Чтение свойства
	 * @param string	$param		Название параметра
	 */
	public function __get( $param )
	{
		return $this->get( $param );
	}
	/**
	 * Запись свойства
	 * @param string	$param		Название параметра
	 */
	public function __set( $param, $value )
	{
		return $this->set( $param, $value );
	}	
	

	/** ==========================================================================================
	 * Добавляет страницу настроект плагина в меню типа данных
	 */
	public function addSettingsPage()
	{
		add_submenu_page(
			'edit.php?post_type=' . CPT_Team::TYPE,
			__( 'IN Team Settings', INTEAM ),
			__( 'Settings', INTEAM ),
			'manage_options',
			INTEAM,
			array( $this, 'showSettingsPage' )
		);		
	}
	
	/** 
	 * Выводит страницу настроект плагина
	 */
	public function showSettingsPage( )
	{	
		$nonceField = INTEAM;
		$nonceAction = 'save-settings';
		$nonceError = false;
		
		// Обработка формы
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			if ( ! isset( $_POST[$nonceField] ) || ! wp_verify_nonce( $_POST[$nonceField], $nonceAction ) ) 
			{
				$nonceError = true;
			} 
			else 
			{
				// process form data
				$this->set( CPT_Team::SETTINGS_SLUG, 						sanitize_text_field( $_POST['inteamSlug'] ) );
				$this->set( CPT_Team::SETTINGS_SLUG_TAXONOMY_DEPARTMENT, 	sanitize_text_field( $_POST['inteamDepartmentSlug'] ) );
				$this->save();
			}		
		}
		
?>
<h1><?php esc_html_e( 'IN Team Settings', INTEAM ) ?></h1>
<p><?php esc_html_e( 'This plugin creates "Our Team" section on site.', INTEAM ) ?></p>
<?php if ( $nonceError ) _e( 'Error: The nonce is not valid!', INTEAM ) ?>

<form id="inteam-settings" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
	<?php wp_nonce_field( $nonceAction, $nonceField ) ?>
	
	<div class="inteam-field">
		<label for="inteamSlug"><?php esc_html_e( 'Team slug', INTEAM ) ?></label>
		<div class="inteam-input">
			<input id="inteamSlug" name="inteamSlug" type="text" value="<?php echo esc_attr( $this->get( CPT_Team::SETTINGS_SLUG ) ) ?>" />
			<p><?php esc_html_e( 'Specify the slug of the Team section. "' . CPT_Team::SETTINGS_SLUG_DEFAULT . '" is using by default if this parameter is empty.', INTEAM ) ?></p>
		</div>
	</div>
	
	<div class="inteam-field">
		<label for="inteamDepartmentSlug"><?php esc_html_e( 'Department slug', INTEAM ) ?></label>
		<div class="inteam-input">
			<input id="inteamDepartmentSlug" name="inteamDepartmentSlug" type="text" value="<?php echo esc_attr( $this->get( CPT_Team::SETTINGS_SLUG_TAXONOMY_DEPARTMENT ) ) ?>" />
			<p><?php esc_html_e( 'Specify the slug of the Department taxomony. "' . CPT_Team::SETTINGS_SLUG_TAXONOMY_DEPARTMENT_DEFAULT . '" is using by default if this parameter is empty.', INTEAM ) ?></p>
		</div>
	</div>
	
	<p>
		<?php esc_html_e( 'Don\'t forget to reset the permalinks', INTEAM) ?>
		<a href="/wp-admin/options-permalink.php"><?php esc_html_e( 'here', INTEAM) ?></a> 
		<?php esc_html_e( 'for flushing Wordpress URL rewrite rules.', INTEAM) ?>
		<?php esc_html_e( 'Just click on [Save] button on Permalinks options page', INTEAM) ?>
	</p>
	
	<?php submit_button() ?>
</form>
<?php	
	}
}