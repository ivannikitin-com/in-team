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
	 * Название большого размера фотографий
	 * @static
	 */
	const IMAGE_LARGE = 'in_team_large';
	
	/**
	 * Ширина большого размера фотографий
	 * @static
	 */
	const IMAGE_LARGE_WIDTH = 500;
	
	/**
	 * Высота большого размера фотографий
	 * @static
	 */
	const IMAGE_LARGE_HEIGHT = 600;

	/**
	 * Параметр Ширина большого размера фотографий
	 * @static
	 */
	const SETTINGS_IMAGE_LARGE_WIDTH = 'in_team_large_width';
	
	/**
	 * Параметр Высота большого размера фотографий
	 * @static
	 */
	const SETTINGS_IMAGE_LARGE_HEIGHT = 'in_team_large_height';	

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
	
	/**
	 * Метабокс на странице сотрудников
	 * @static
	 */
	const METABOX_ID = 'inteam_metabox';
	
	/**
	 * Поле связи с пользователем
	 * @static
	 */
	const META_LINKED_USER = '_linked_user_id';	
	
	
	
	
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
		
		// Хуки в админской части
		if ( is_admin() ) 
		{
			// Схранение метаданных
			add_action( 'save_post', array( $this, 'savePost' ), 10, 2 );
			
			// Инициализация метабокса
			add_action( 'load-post.php',     array( $this, 'initMetabox' ) );
			add_action( 'load-post-new.php', array( $this, 'initMetabox' ) );
			
			// Колонки в таблице пользователей в админке
			add_filter( 'manage_' . self::TYPE . '_posts_columns' , array( $this, 'addCPTColumns' ) );
			add_action( 'manage_' . self::TYPE . '_posts_custom_column' , array( $this, 'showCPTColumns' ), 10, 2 );
			
			// Поля в быстром редактировании
			add_action( 'quick_edit_custom_box', array( $this, 'addQuickEditBox' ), 10, 2 );
			add_action( 'admin_print_scripts-edit.php', array( $this, 'quickEditScript' ) );
		}		
		
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
			'menu_icon'           => 'dashicons-nametag',
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
			
		// Средний размер
		add_image_size( self::IMAGE_LARGE, 
			$this->plugin->settings->get( self::SETTINGS_IMAGE_LARGE_WIDTH, 	self::IMAGE_LARGE_WIDTH ), 
			$this->plugin->settings->get( self::SETTINGS_IMAGE_LARGE_HEIGHT, 	self::IMAGE_LARGE_HEIGHT ), 
			true );		
	}	
	
	/**
	 * Установка параметров вывода данных
	 * @param WP_Query $query	Объект который запрашивается
	 */	
	public function setQueryParams( $query )
	{	
		if ( ( $query->get('post_type') == self::TYPE  || is_tax( self::TAXONOMY_DEPARTMENT ) )&& $query->is_main_query() )
		{
			$query->set( 'orderby', $this->plugin->settings->get( self::SETTINGS_ORDER_BY, self::ORDER_BY_DEFAULT ) );
			$query->set( 'order', 'ASC' );
			$query->set( 'posts_per_page', -1 );
		}
	}	
	
	/**
	 * Инициализация метабокса 
	 */	
	public function initMetabox() 
	{
		// Добавляем метабокс
		add_action( 'add_meta_boxes', array( $this, 'addMetabox' ) );
		// Сохранение данных
		//add_action( 'save_post', array( $this, 'savePost' ), 10, 2 );
	}

	/**
	 * Добавление метабокса 
	 */		
	public function addMetabox() {

		add_meta_box( self::METABOX_ID,
			__( 'Team Member', 'in-team' ),
			array( $this, 'renderMetabox' ),
			'in_team',
			'side',
			'core'
		);
	}
	
	/**
	 * Отрисовка метабокса 
	 */	
	public function renderMetabox( $post ) 
	{

		// Retrieve an existing value from the database.
		$inteam_user_id = get_post_meta( $post->ID, self::META_LINKED_USER, true );

		// Set default values.
		if( empty( $inteam_user_id ) ) $inteam_user_id = '';

		// Form fields.
		echo '<table class="form-table">';

		echo '	<tr>';
		echo '		<th><label for="inteam_user_id" class="inteam_user_id_label">' . __( 'Link to User', INTEAM ) . '</label></th>';
		echo '		<td>';
		
		wp_dropdown_users( array( 'id' => 'inteam_user_id', 'name' => 'inteam_user_id', 'class' => 'inteam_user_id_field', 'selected' => $inteam_user_id ) );
		
		echo '			<p class="description">' . __( 'Select the WP user', INTEAM ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '</table>';

	}	

	/**
	 * Сохранение метаданных
	 * @param int $post_id	ID записи	 
	 * @param obj $post		Объект поста	 
	 */	
	public function savePost( $post_id, $post ) 
	{
		// don't save for autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// dont save for revisions
		if ( isset( $post->post_type ) && $post->post_type == 'revision' )
			return $post_id;

		// Сохраняем только для нашего типа
		if ( isset( $post->post_type ) && $post->post_type != self::TYPE )
			return $post_id;		
		
		// Sanitize user input.
		$inteam_new_user_id = isset( $_POST[ 'inteam_user_id' ] ) ? sanitize_text_field( $_POST[ 'inteam_user_id' ] ) : '';

		// Update the meta field in the database.
		update_post_meta( $post_id, self::META_LINKED_USER, $inteam_new_user_id );
	}
	
	/**
	 * Добавление колонок в таблице пользователей в админке
	 * @param mixed $columns	Ассоциативный массив с колонками
	 */
	public function addCPTColumns( $columns ) 
	{
		// Убираем колонки
		unset( $columns['author'] );
		
		// Добавляем новые колонки
		$newColumns = array(
			self::META_LINKED_USER 		=> __( 'Linked to', INTEAM ),
			UserProfile::FIELD_POSITION => __( 'Position', INTEAM ),
		);
		return array_merge( $columns, $newColumns );		
	}	 
	 
	/**
	 * Отображение колонок в таблице пользователей в админке
	 * @param string 	$column		Текущая колонка
	 * @param int 		$post_id	Текущая запись
	 */
	public function showCPTColumns( $column, $post_id ) 
	{
		$linkedUserId = get_post_meta( $post_id , self::META_LINKED_USER , true );
		
		
		switch ( $column ) 
		{
			case self::META_LINKED_USER :	
				if ( $linkedUserId  ) 
				{
					// Сохраним данные для скрипта и покажем их
					echo '<a id="linked_user_id-' . $post_id . 
						'" data-linked-user="' . $linkedUserId . 
						'" href="/wp-admin/user-edit.php?user_id=' . $linkedUserId . '">' .  
						Shortcode::getUserData( $linkedUserId, 'display_name' ) . '</a>';
				}
				break;
				
				
			case UserProfile::FIELD_POSITION :
				echo Shortcode::getUserData( $linkedUserId, UserProfile::FIELD_POSITION );
				break;
		}		
	}
	
	/**
	 * Добавление и отображение полей быстрого редактирования
	 * @param string $column_name	Текущая колонка
	 * @param string $post_type		Текущая тип записи
	 */
	public function addQuickEditBox( $column_name, $post_type ) 
	{
		if ( $post_type == self::TYPE ) 
		{
			 switch( $column_name ) {
				case self::META_LINKED_USER:
					$inteam_user_id = get_post_meta( $post->ID, self::META_LINKED_USER, true );
				   ?><fieldset class="inline-edit-col-right">
					  <div class="inline-edit-group">
						 <label for="inteam_user_id" class="title"><?php esc_html_e( 'Linked to', INTEAM ) ?></label>
						<?php wp_dropdown_users( array( 'id' => 'inteam_user_id', 'name' => 'inteam_user_id', 'class' => 'inteam_user_id_field', 'selected' => $inteam_user_id ) ); ?>
					  </div>
				   </fieldset><?php
				   break;
			 }
		}
	}
	
	/**
	 * Из-за особенностей WordPress поля в окне быстрого редактирования заполняем скриптом
	 */
	public function quickEditScript( ) 
	{
		wp_enqueue_script( INTEAM . '_quick_edit', 
			INTEAM_URL . 'js/quick_edit.js', 
			array( 'jquery', 'inline-edit-post' ), '', true );
	}
	
	
}