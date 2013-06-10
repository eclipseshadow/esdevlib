<?php

class Halp_Slider_Toolkit extends Halp_Module {
	
	/** @type bool Whether or not all dependency class are loaded */
	protected static $classes_loaded = false;
	
	/**
	 * Loads all dependency classes
	 *
	 * Classes that the Slider Toolkit depends on
	 */
	public static function init() {
	
		if ( !self::$classes_loaded ) {
			//require_once 'classes/some-needed-class.php';
		}
	
	}
	
}