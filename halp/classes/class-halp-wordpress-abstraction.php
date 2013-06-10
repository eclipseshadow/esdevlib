<?php

class Halp_Wordpress_Abstraction {
	
	/**
	 * Javascript enqueueing method
	 * 
	 * Must be be called inside the wp_enqueue_scripts action
	 */
	public static function enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	
		wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
	
	}
	
	/**
	 * Style/css enqueueing method
	 * 
	 * Must be be called inside the wp_enqueue_scripts action
	 */
	public static function enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	
	}
	
}