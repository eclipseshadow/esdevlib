<?php

/*
 * Halp Module is an extendable class that all WP Halp Modules must be extended from
 * in order to be properly loaded
 */
class Halp_Module {
	
	/** @type array Array of module classnames that this module depends on */
	protected static $dependencies = array();
	
	/**
	 * Halp::load_module() uses this to retrieve dependencies for a module
	 * 
	 * @return array Dependencies of the module
	 * 
	 * @internal
	 */
	public static function get_dependencies() {
		
		return self::$dependencies;
		
	}
	
	/**
	 * Halp::load_module() will call this upon loading
	 * 
	 * May be extended in Halp_Module child classes as needed
	 */
	public static function init() {
		
		// Silence is golden
		
	}
	
}