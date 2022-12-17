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
	 * Custom Post Type Team
	 * @var INTEAM\CPT_Team
	 */
	public $cptTeam;
	
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
	 * Конструктор
	 * Инициализация плагина
	 */
	public function __construct()
	{
		$this->settings 	= new Settings( INTEAM ); 	// Инициализируем параметры
		//$this->cptTeam 		= new CPT_Team( $this ); 	// Инициализируем CPT Team
		//$this->userProfile 	= new UserProfile( $this );	// Инициализируем дополнения к профилю пользователя		
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
		global $wp_rewrite;
		$wp_rewrite->author_base = 'team';
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
        if ( is_embed() )
            return $template;
		
		// Сформированный файл шаблона
		$file = '';
		
		// Определение нужного шаблона
		if ( is_single() && get_post_type() == CPT_Team::TYPE ) 
		{
			$file = 'member.php'; 		// Шаблон карточки сотрудника		
		}
		elseif ( is_post_type_archive( CPT_Team::TYPE ) )
		{
			$file = 'team.php'; 		// Шаблон вывода всей команды
		}		
		elseif ( is_tax( CPT_Team::TAXONOMY_DEPARTMENT ) )
		{
			$file = 'department.php'; 	// Шаблон вывода отдела
		}

		// Если шаблон определен...
		if ( $file ) 
		{
			// Где искать шаблоны
			$find = array();
			$find[] = $file;							// В текущей папке							
			$find[] = INTEAM . '/' . $file; 			// В теме, в папке с названием плагина					
			
			$template       = locate_template( array_unique( $find ) );
			if ( ! $template ) 
			{ 
				// Шаблон не найден, подгружаем из плагина
				$template = INTEAM_PATH . 'templates/' . $file;
			}
		}
		return $template;		
	}
	
}