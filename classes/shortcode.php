<?php
/**
 * Класс реализует шорткоды плагина
 */
namespace INTEAM;
class Shortcode
{
	/**
	 * Конструктор
	 * Регистрирует шорткоды
	 */
	public function __construct()
	{		
		// Вывод поля из профиля пользователя
		add_shortcode( 'inteam_field', array( $this, 'inteam_field' ) );

		// Вывод списка команды
		add_shortcode( 'inteam_members', array( $this, 'inteam_members' ) );
	}
	
	/**
	 * Выводит поле из профиля пользователя
	 * @param mixed  $atts 		Ассоциативный массив атрибутов указанных в шорткоде	
	 * @param string $content 	Текст шорткода, когда используется контентный шорткод	
	 * @param string $tag 		Имя шорткода. Передается в хуки.
	 * @return string			Функция шорткода должна вернуть данные, а не выводить их - return, а не echo.	
	 */
	public function inteam_field( $atts, $content='', $tag='' ) {
		$atts = shortcode_atts( array(
			'field' 	=> '',	// Идентификатор поля для вывода
			'before' 	=> '',	// Вывод любой строки перед значением
			'after' 	=> '',	// Вывод любой строки после значения
			'user_id' 	=> 0	// Идентификатор пользователя
		), $atts, $tag );

		return  
			$atts[ 'before' ] . 
			get_user_meta( $atts[ 'user_id' ], $atts[ 'field' ], true ) .
			$content .
			$atts[ 'after' ];
	}	

	/**
	 * Выводит список членов команды
	 * @param mixed  $atts 		Ассоциативный массив атрибутов указанных в шорткоде	
	 * @param string $content 	Текст шорткода, когда используется контентный шорткод	
	 * @param string $tag 		Имя шорткода. Передается в хуки.
	 * @return string			Функция шорткода должна вернуть данные, а не выводить их - return, а не echo.	
	 */
	public function inteam_members( $atts, $content='', $tag='' ) {
		$atts = shortcode_atts( array(
			'before' => '',	 // Вывод любой строки перед результатом
			'after'  => '',	 // Вывод любой строки после результата
			'number' => 20,  // Число возвращаемых пользователей
			'paged'	 => 1,   // Номер страницы списка возвращаемых пользователей
			'search' => '',  // Запрос для поиска пользователей, например, Иван* или *Викторович или *Виктор*
		), $atts, $tag );

		// URL страницы профиля
		$profile_url = 
			get_option( 'home' ) . '/' .
			Plugin::get()->settings->get_base_slug();

		error_log( '$profile_url: ' . $profile_url );

		// Запрос пользователей
		$users = get_users( array(
			'role__in' => Plugin::get()->settings->get_team_role(),
			'number' => $atts[ 'number' ],
			'paged' => $atts[ 'paged' ],
			'search' => $atts[ 'search' ]
		) );

		// Найдем шаблон вывода
		$file = 'member.php';
		$template = locate_template( array(
			$file,					// В текущей папке темы (дочерней или родительской)
			INTEAM . '/' . $file	// В папке темы с названием плагина
		) );
		// Если шаблон не найден, берем из плагина
		if ( empty( $template ) ) $template = INTEAM_PATH . 'templates/' . $file;

		// Формируем вывод
		ob_start();
		foreach ($users as $user) {
			@include( $template );
		}

		// Формируем результат
		$result = ob_get_contents();
		ob_end_clean();

		return  
			$atts[ 'before' ] . 
			$result .
			$content .
			$atts[ 'after' ];
	}
}