<?php

/*
 * Halp is a code library, composed of helper functions and modules that make the
 * coding life of a developer (specifically wordpress developer) easier
 * 
 * @todo separate all Wordpress-related modules/code to keep the core of the Halp lib WP agnostic
 */
class Halp {
	
	/** @type array Wordpress reserved words */
	protected static $_reserved = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category','category__and', 'category__in', 'category__not_in',
			'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category',
			'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb',
			'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type',
			'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts',
			'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in','tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy',
			'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year' );
	
	protected static $_abstraction_class = null;
	
	/**
	 * Loads a class for a module or from the Halp classes directory
	 * 
	 * Class files are expected to be named "class-<the class name in lowercase w/ hyphens for spacing>.php
	 * 
	 * @param	string $class_name The class name
	 * @param	boolean $is_module The class is part of a module
	 * 
	 * @return	boolean|Halp_Error Returns true if class is/was loaded, otherwise Halp_Error
	 */
	public static function load_class( $class_name = '', $is_module = false ) {
		
		$directory = $is_module ? HALP_PATH .'/modules/' : HALP_PATH .'/classes/';
		$directory .= $is_module ? strtolower( str_replace( '-', '_', $class_name ) ). '/' : '';
		$file = $directory .'class-'. strtolower( str_replace( '_', '-', $class_name ) ) .'.php';
		
		// Load class
		
		if ( !class_exists($class_name) ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
			else {
				return new Halp_Error('Halp Error', __('Failed to load class: '. $class_name));
			}
			
			if ( $is_module && !is_subclass_of($class_name, 'Halp_Module') ) {
				return new Halp_Error('Halp Error', __('Class: '. $class_name .' must be a subclass of Halp_Module in order to be loaded as a module'));
			}
		}
		
		return true;
		
	}
	
	/**
	 * Loads a module based on its class name
	 * 
	 * - Modules are expected to be in a directory that is lowercase, matching the class name
	 * - See Halp::loadClass for details on classname convention
	 * 
	 * @param	string	$class_name	The class name of the module
	 * 
	 * @return	boolean|Halp_ERROR	Returns true if module class is/was loaded, otherwise Halp_Error
	 */
	public static function load_module( $class_name = '' ) {
		
		$loaded = self::load_class( $class_name, true );
		
		if ( is_halp_error($loaded) ) return $loaded;
		
		// Load Module Dependencies
		
		$deps = $class_name::get_dependencies();
		
		foreach( $deps as $dep_class_name ) {
			self::load_module( $dep_class_name );
		}
		
		$class_name::init();
		
	}
	
	/**
	 * Detects which CMS is being used (if any) and sets the pointer
	 * to the appropriate abstraction class for common tasks like
	 * enqueueing scripts/styles, etc for modules
	 */
	public static function set_cms_abstraction() {
		
		self::$_abstraction_class = 'Halp_Wordpress_Abstraction';
		self::load_class( self::$_abstraction_class );
		
	}
	
	/**
	 * Generic javascript enqueueing method
	 *
	 * Uses abstraction classes to properly perform enqueues based on the
	 * CMS currently being used (ex. Wordpress, Joomla, Drupal)
	 */
	public static function enqueue_script() {
		
		$args = func_get_args();
		
		if ( !is_null( self::$_abstraction_class ) ) {
			call_user_func_array( array(self::$_abstraction_class, 'enqueue_script'), $args);
		}
		
	}
	
	/**
	 * Generic style/css enqueueing method
	 * 
	 * Uses abstraction classes to properly perform enqueues based on the
	 * CMS currently being used (ex. Wordpress, Joomla, Drupal)
	 */
	public static function enqueue_style() {
		
		$args = func_get_args();
		
		if ( !is_null( self::$_abstraction_class ) ) {
			call_user_func_array( array(self::$_abstraction_class, 'enqueue_script'), $args);
		}
		
	}
	
	/**
	 * Beautifies a string. Capitalize words and remove underscores
	 *
	 * @param 	string	$string
	 * 
	 * @return 	string
	 */
	public static function beautify( $string ) {
		
		return ucwords( str_replace( '_', ' ', $string ) );
		
	}
	
	/**
	 * Uglifies a string. Remove underscores and lower strings
	 *
	 * @param	string	$string
	 * 
	 * @return	string
	 */
	public static function uglify( $string ) {
		
		return str_replace( '-', '_', sanitize_title( $string ) );
		
	}
	
	/**
	 * Makes a word plural
	 *
	 * @param	string	$string
	 * 
	 * @return	string
	 */
	public static function pluralize( $string ) {
		
		$last = $string[strlen( $string ) - 1];
	
		if( $last != 's' )
		{
			if( $last == 'y' )
			{
				$cut = substr( $string, 0, -1 );
				//convert y to ies
				$string = $cut . 'ies';
			}
			else
			{
				// just attach a s
				$string = $string . 's';
			}
		}
	
		return $string;
		
	}
	
	/**
	 * Check if the term is reserved by Wordpress
	 *
	 * @param	string	$term
	 * 
	 * @return	boolean
	 */
	public static function is_reserved_term( $term ) {
		
		if( ! in_array( $term, self::$_reserved ) ) return false;
		 
		return new Halp_Error( 'reserved_term_used', __( "Use of a reserved term", 'cuztom' ) );
		
	}
	
	/**
	 * Breaks up a string of CSS classes into an array
	 * 
	 * @param string $string_classes The string of CSS classes
	 * 
	 * @return array Array of CSS classes
	 */
	public static function str_to_classes( $string_classes = '' ) {
	
		return preg_split('/[\ \n]+/', $string_classes);
	
	}
	
	/**
	 * Adds a CSS class to an array of classes if it doesn't already exist
	 * 
	 * @param string $class
	 * @param array $classes
	 * 
	 * @return array The updated array of CSS classes
	 * 
	 * @internal
	 */
	public static function _add_class( $class = '', $classes = array() ) {
	
		if ( in_array($class, $classes) || empty($class) ) return;
	
		array_push($classes, $class);
	
		return $classes;
	
	}
	
	/**
	 * Removes a CSS class from an array of classes if it exists
	 *
	 * @param string $class
	 * @param array $classes
	 *
	 * @return array The updated array of CSS classes
	 * 
	 * @internal
	 */
	public static function _remove_class( $class = '', $classes = array() ) {
	
		if ( false !== ($key = array_search($class, $classes)) ) {
			unset( $classes[$key] );
		}
	
		return $classes;
	
	}

}

Halp::set_cms_abstraction();
