<?php
/**
 * Класс реализует тип записей in_team
 */
namespace INTEAM;
class CPT_Team
{
/* ---------------------------------- Определения ----------------------------------*/	
	
	/**
	 * Тип данных
	 * @static
	 */
	const TYPE = 'in_team';
	
	/**
	 * Название параметра в настройках
	 * @static
	 */
	const SETTINGS_SLUG = 'slug_team';	
	
	/**
	 * Значение слага по умолчанию
	 * @static
	 */
	const SETTINGS_SLUG_DEFAULT = 'team';	

	/**
	 * Таксономия Отдел
	 * @static
	 */
	const TAXONOMY_DEPARTMENT = 'in_team_department';
	
	/**
	 * Название параметра в настройках
	 * @static
	 */
	const SETTINGS_SLUG_TAXONOMY_DEPARTMENT = 'slug_department';	
	
	/**
	 * Значение слага по умолчанию
	 * @static
	 */
	const SETTINGS_SLUG_TAXONOMY_DEPARTMENT_DEFAULT = 'department';	
	
	/**
	 * Название среднего размера фотографий
	 * @static
	 */
	const IMAGE_MEDIUM = 'in_team_medium';
	
	/**
	 * Ширина среднего размера фотографий
	 * @static
	 */
	const IMAGE_MEDIUM_WIDTH = 285;
	
	/**
	 * Высота среднего размера фотографий
	 * @static
	 */
	const IMAGE_MEDIUM_HEIGHT = 300;

	/**
	 * Параметр Ширина среднего размера фотографий
	 * @static
	 */
	const SETTINGS_IMAGE_MEDIUM_WIDTH = 'in_team_medium_width';
	
	/**
	 * Параметр Высота среднего размера фотографий
	 * @static
	 */
	const SETTINGS_IMAGE_MEDIUM_HEIGHT = 'in_team_medium_height';

	/**
	 * Сортировка вывода сотрудников
	 * @static
	 */
	const ORDER_BY_DEFAULT = 'menu_order';

	/**
	 * Параметр Ширина среднего размера фотографий
	 * @static
	 */
	const SETTINGS_ORDER_BY = 'order_by';	
	
/* ------------------------------------ Свойства -----------------------------------*/	
	/**
	 * Ссылка на основной объект плагина
	 * @var INTEAM\Plugin
	 */
	protected $plugin;
	
	/**
	 * Основной слаг типа
	 * @var string
	 */
	protected $slug;

	/**
	 * Слаг Отделов
	 * @var string
	 */
	protected $slugDepartment;	
	
	
	
/* ------------------------------------- Методы ------------------------------------*/	
	/**
	 * Конструктор
	 * инициализирует параметры и загружает данные
	 * @param INTEAM\Plugin plugin		Ссылка на основной объект плагина 
	 */
	public function __construct( $plugin )
	{
		// Основной объект плагина
		$this->plugin = $plugin;
		
		// Слаг CPT
		$this->slug = $this->plugin->settings->get( self::SETTINGS_SLUG, self::SETTINGS_SLUG_DEFAULT );
		
		// Слаг отделов
		$this->slugDepartment = $this->plugin->settings->get( self::SETTINGS_SLUG_TAXONOMY_DEPARTMENT, self::SETTINGS_SLUG_TAXONOMY_DEPARTMENT_DEFAULT );
		
		// Регистрация типа и таксономии
		$this->registerTaxonomyDepartment();
		$this->registerCPT();
		
		// Регистрация размеров фотографий
		$this->registerimageSizes();
		
		// Хук на выборку данных
		add_action( 'pre_get_posts', array( $this, 'setQueryParams' ) );
		
	}
	
	/**
	 * Регистрация типа данных 
	 */	
	protected function registerCPT()
	{
		$labels = array(
			'name'                => __( 'Employees', INTEAM ),
			'singular_name'       => __( 'Employee', INTEAM ),
			'menu_name'           => __( 'Our Team', INTEAM ),
			'parent_item_colon'   => __( 'Parent Employee', INTEAM ),
			'all_items'           => __( 'All Employees', INTEAM ),
			'view_item'           => __( 'View Employee', INTEAM ),
			'add_new_item'        => __( 'New Employee', INTEAM ),
			'add_new'             => __( 'Add Employee', INTEAM ),
			'edit_item'           => __( 'Edit Employee', INTEAM ),
			'update_item'         => __( 'Update Employee', INTEAM ),
			'search_items'        => __( 'Search Employee', INTEAM ),
			'not_found'           => __( 'Not found', INTEAM ),
			'not_found_in_trash'  => __( 'Not found in trash', INTEAM ),
		);
		$rewrite = array(
			'slug'                => $this->slug,
			'with_front'          => true,
			'pages'               => true,
			'feeds'               => true,
		);
		$args = array(
			'label'               => self::TYPE,
			'description'         => __( 'Employee details', INTEAM ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', ),
			'taxonomies'          => array( self::TAXONOMY_DEPARTMENT ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 20,
			'menu_icon'           => '',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
		);
		register_post_type( self::TYPE, $args );		
	}
	
	/**
	 * Регистрация таксономии Отдел 
	 */	
	protected function registerTaxonomyDepartment()	
	{
		// Таксономия отделов
		$labels = array(
			'name'                       => __( 'Departments', INTEAM ),
			'singular_name'              => __( 'Department', INTEAM ),
			'menu_name'                  => __( 'Departments', INTEAM ),
			'all_items'                  => __( 'All departments', INTEAM ),
			'parent_item'                => __( 'Parent department', INTEAM ),
			'parent_item_colon'          => __( 'Parent department', INTEAM ),
			'new_item_name'              => __( 'New department', INTEAM ),
			'add_new_item'               => __( 'Add department', INTEAM ),
			'edit_item'                  => __( 'Edit department', INTEAM ),
			'update_item'                => __( 'Update department', INTEAM ),
			'separate_items_with_commas' => __( 'Separate departments by comma', INTEAM ),
			'search_items'               => __( 'Search department', INTEAM ),
			'add_or_remove_items'        => __( 'Add or remove department', INTEAM ),
			'choose_from_most_used'      => __( 'Most used departments', INTEAM ),
			'not_found'                  => __( 'Not found', INTEAM ),
		);
		$rewrite = array(
			'slug'                       => $this->slugDepartment,
			'with_front'                 => false,
			'hierarchical'               => true,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'query_var'                  => self::TAXONOMY_DEPARTMENT,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( self::TAXONOMY_DEPARTMENT, array( self::TYPE ), $args );		
	}
	
	/**
	 * Регистрация размеров для фотографий сотрудников 
	 */	
	protected function registerimageSizes()
	{
		// Средний размер
		add_image_size( self::IMAGE_MEDIUM, 
			$this->plugin->settings->get( self::SETTINGS_IMAGE_MEDIUM_WIDTH, 	self::IMAGE_MEDIUM_WIDTH ), 
			$this->plugin->settings->get( self::SETTINGS_IMAGE_MEDIUM_HEIGHT, 	self::IMAGE_MEDIUM_HEIGHT ), 
			true );
	}		
	
	/**
	 * Установка параметров вывода данных
	 * @param WP_Query $query	Объект который запрашивается
	 */	
	public function setQueryParams( $query )
	{	
		if ( $query->get('post_type') == self::TYPE && $query->is_main_query() )
		{
			$query->set( 'orderby', $this->plugin->settings->get( self::SETTINGS_ORDER_BY, self::ORDER_BY_DEFAULT ) );
			$query->set( 'order', 'ASC' );
			$query->set( 'posts_per_page', -1 );
		}
	}
	
}