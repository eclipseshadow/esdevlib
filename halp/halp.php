<?php
/**
 * Halp Library
 *
 * PHP Code Library - Helpers & Utilities for Development
 *
 * @package Halp
 * @author Zach Lanich - Eclipse Shadow LLC
 * @version 0.01 alpha
 */

/** Set Global Path Constant */
define( 'HALP_PATH', dirname(__FILE__) );

/** Include the main class */
if ( !class_exists('Halp') ) {
	require_once 'classes/class-halp.php';
}

/** Load other required classes */
Halp::load_class('Halp_Module');
Halp::load_class('Halp_Error');