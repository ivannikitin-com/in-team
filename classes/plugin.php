<?php
/**
 * Класс реализует основной функционал плагина
 */
namespace INTEAM;
class Plugin
{	
	/**
	 * Параметры плагина
	 * @var INTEAM\Settings
	 */
	public $settings;
	
	/**
	 * Дополнения к профилю пользователя
	 * @var INTEAM\UserProfile
	 */
	public $userProfile;	
	
	/**
	 * Шорткоды
	 * @var INTEAM\Shortcode
	 */
	public $shortcode;	

	/**
	 * Экземпляр класса Singleton
	 */
    protected static $_instance;

	/**
	 * Возвращает экземпляр класса
	 */
    public static function get() {
        if (self::$_instance === null) {
            self::$_instance = new self;  
        }
 
        return self::$_instance;
    }

	/**
	 * Конструктор
	 * Инициализация плагина
	 */
	private function __construct()
	{
		$this->settings 	= new Settings( INTEAM ); 	// Инициализируем параметры
		$this->userProfile 	= new UserProfile( $this );	// Инициализируем дополнения к профилю пользователя		
		//$this->shortcode 	= new Shortcode( $this );	// Инициализируем шорткоды
		
		// Перезапись базового URL для авторов
		$this->rewrite_author_base();

		// Включаем наш загрузчик шаблонов
		add_filter( 'template_include', array( $this, 'load_template' ) );
		
	}

	/**
	 * Перезапись базового URL для авторов
	 * 
	 * https://weusewp.com/tutorial/change-author-url-slug-base/
	 * https://wp-kama.ru/function/wp_rewrite 
	 * 
	 * Важно после изменения сделать сброс постоянных ссылок
	 */
	public function rewrite_author_base() {
		$base_url = $this->settings->get_base_slug();
		if ( empty( $base_url ) ) return; 

		global $wp_rewrite;
		$wp_rewrite->author_base = $base_url;
	} 
	
	/**
	 * Загружает и возвращает шаблон для отображения страницы автора
	 * http://wordpress.stackexchange.com/questions/155871/create-template-author-with-a-plugin
	 *
	 * @param string	$template	Имя загружаемого шаблона
	 * @return string				Имя загружаемого шаблона
	 */
	public function load_template( $template ) {
		// Если это подзапрос, шаблоны не подставляем! Например, так сделано в WooCommerce
		// https://docs.woocommerce.com/wc-apidocs/source-class-WC_Template_Loader.html#7-119
        if ( is_embed() ) return $template;
		
		// Если это не страница автора, ничего не делаем!
		if ( ! is_author() ) return $template;

		global $wp_query;

		//error_log( 'ID: ' . get_the_ID() );

	
		// Если это не запрос пользователя, ничего не делаем
		if ( ! isset( $wp_query ) || 
			 ! isset( $wp_query->queried_object ) ||
			 ! ( $wp_query->queried_object instanceof \WP_User ) ) 
			return $template;

		// Если пользователь не входит в нужную роль, переадресация на основной слаг
		if ( ! in_array($this->settings->get_team_role(), $wp_query->queried_object->roles) ) {
			$team_url = get_option( 'home' ) . '/' .
				( ! empty( $this->settings->get_base_slug() ) ) ? 
					$this->settings->get_base_slug() . '/' 
					: ''; 
			wp_redirect( $team_url, 301 );
			exit;
		}

		// Определяем шаблон страницы 
		$template_file = 'profile.php';

		// и указываем, где его искать
		$find = array();
		$find[] = $template_file;							// В текущей папке							
		$find[] = INTEAM . '/' . $template_file; 			// В теме, в папке с названием плагина					
		
		$template = locate_template( array_unique( $find ) );
		if ( ! $template ) 
		{ 
			// Шаблон не найден, подгружаем из плагина
			$template = INTEAM_PATH . 'templates/' . $template_file;
		}
		return $template;		
	}
	
}