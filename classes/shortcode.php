<?php
/**
 * Класс реализует шорткоды плагина
 */
namespace INTEAM;
class Shortcode
{
	/**
	 * Конструктор
	 * инициализирует шорткоды
	 * @param INTEAM\Plugin plugin		Ссылка на основной объект плагина 
	 */
	public function __construct( $plugin )
	{
		$shortcodes = array(
			'inteam_position',		// Показывает должность сотрудника
			'inteam_email',			// Показывает E-mail сотрудника
			'inteam_phone',			// Показывает телефон сотрудника
			'inteam_departament',	// Показывает отдел сотрудника
		);
		
		// Регистрируем их!
		foreach( $shortcodes as $shortcode )
		{
			add_shortcode( $shortcode, array( __CLASS__, $shortcode ) );
		}
	}
	
	/* ---------------------------------- Функция вывода данных ----------------------------------*/
	/**
	 * Выводит поле данных для пользователяё
	 * @param int		$userId 	ID пользователя 
	 * @param string	$field 		Поле, которое нужно вывести 
	 * @param string	$label 		Текст перед выводом 
	 */	
	public static function getUserData( $userId, $field, $label ) 
	{
		if ( empty( $userId ) || empty( $field ) )
			return false;
		
		$user = get_userdata( $userId );
		if ( empty( $user ) )
			return false;
		
		if ( ! empty( $label ) )
			$label = $label . ' ';
		
		return $label . $user->$field;
	}	
	
	/**
	 * Общзая функция для шорткодов, связанных с полями пользователя
	 * @param mixed		$atts 		Переданные атрибуты шорткода 
	 * @param string	$content 	Контент шорткода 
	 * @param string	$field 		Поле, которое нужно вывести 
	 */	
	public static function userDataShortcode( $atts, $content, $field ) 
	{
		// Если не заданы User ID и Post ID выводим пусто
		if ( empty( $atts['user_id'] ) && empty( $atts['post_id'] ) )
			return false;
		
		if ( empty( $atts['user_id'] ) )
		{
			// Читаем ID связанного пользователя
			$linkedUserId = get_post_meta( $atts['post_id'], CPT_Team::META_LINKED_USER , true );
			if ( empty( $linkedUserId ) )
				return false;
			
			// Возвращаем данные
			return self::getUserData( $linkedUserId, $field, $atts['label'] );			
		}
		else
		{
			// Задан User ID - выводим
			return self::getUserData( $atts['user_id'], $field, $atts['label'] );
		}
		
		return false;
	}	
	
	
	/* ---------------------------------- Функции шорткодов ----------------------------------*/	
	public static function inteam_position( $atts, $content = '' ) 
	{
		$atts = shortcode_atts( array(
			'label' 	=> '',				// Вывод названия перед значением
			'user_id' 	=> 0,				// Идентификатор пользователя
			'post_id' 	=> 0,				// Идентификатор записи in_team
		), $atts, __FUNCTION__ );

		return self::userDataShortcode( $atts, $content, UserProfile::FIELD_POSITION );
	}	
	
	public static function inteam_email( $atts, $content = '' ) 
	{
		$atts = shortcode_atts( array(
			'label' 	=> '',				// Вывод названия перед значением
			'user_id' 	=> 0,				// Идентификатор пользователя
			'post_id' 	=> 0,				// Идентификатор записи in_team
		), $atts, __FUNCTION__ );

		return self::userDataShortcode( $atts, $content, 'user_email' );
	}	
	
	public static function inteam_phone( $atts, $content = '' ) 
	{
		$atts = shortcode_atts( array(
			'label' 	=> '',				// Вывод названия перед значением
			'user_id' 	=> 0,				// Идентификатор пользователя
			'post_id' 	=> 0,				// Идентификатор записи in_team
		), $atts, __FUNCTION__ );

		return self::userDataShortcode( $atts, $content, 'billing_phone' );
	}	
	
	public static function inteam_departament( $atts, $content = '' ) 
	{
		$atts = shortcode_atts( array(
			'label' 	=> '',				// Вывод названия перед значением
			'user_id' 	=> 0,				// Идентификатор пользователя
			'post_id' 	=> 0,				// Идентификатор записи in_team
		), $atts, __FUNCTION__ );

		// Если НЕ задан post ID, ищем запись в команде по user_id
		if ( empty( $atts['post_id'] ) )
		{
			if ( empty( $atts['user_id'] ) )
				return false;
			
			// Параметры запроса по мета-полю
			$args = array(
			'post_type'		=>	CPT_Team::TYPE,
			'meta_query'	=>	array(
				array( '_linked_user_id'	=>	$atts['user_id'] )
				)
			);
			
			// Выполняем запрос
			$my_query = new WP_Query( $args );		
			if( $my_query->have_posts() ) 
			{
				$my_query->the_post();
				$atts['post_id'] = get_the_ID();
			}
			// Сброс данных для основного цикла
			wp_reset_postdata();			
		}
		
		
		// Если задан post ID, читаем таксономию текущей записи
		if ( ! empty( $atts['post_id'] ) )
		{
			// Читаем отделы для этой записи
			$departament = '';
			$departs = get_the_terms( $atts['post_id'], CPT_Team::TAXONOMY_DEPARTMENT, array( 'fields' => 'names' ) );
			foreach ( $departs as $depart )
				$departament .= $depart->name . ', ';
				
			if ( ! empty( $atts['label'] ) )
				$atts['label'] = $atts['label'] . ' ';
			
			if ( mb_strlen( $departament ) > 0 )
				$departament = mb_substr( $departament, 0, mb_strlen( $departament ) - 2 );
			
			return $atts['label'] . $departament;		
		}
		
		return false;		
	}	
}