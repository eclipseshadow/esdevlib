<?php

/**
 * Toolkit for building, handling and validating web forms
 * 
 * Contains shorthand methods for creating form and field objects
 */

class Halp_Form_Toolkit extends Halp_Module {
	
	/** @type bool Whether or not all dependency class are loaded */
	protected static $classes_loaded = false;
	
	/**
	 * Loads all dependency classes
	 * 
	 * Classes that the Form Toolkit depend on include the individual form
	 * field classes as well as the handler and validator classes
	 */
	public static function init() {
		
		if ( !self::$classes_loaded ) {
			require_once 'classes/class-halp-form.php';
			require_once 'classes/class-halp-form-handler.php';
			require_once 'classes/class-halp-form-validator.php';
			require_once 'classes/class-halp-form-field.php';
			require_once 'classes/class-halp-form-field-button.php';
			require_once 'classes/class-halp-form-field-checkbox.php';
			require_once 'classes/class-halp-form-field-checkbox-group.php';
			require_once 'classes/class-halp-form-field-hidden.php';
			require_once 'classes/class-halp-form-field-password.php';
			require_once 'classes/class-halp-form-field-radio-group.php';
			require_once 'classes/class-halp-form-field-select.php';
			require_once 'classes/class-halp-form-field-submit.php';
			require_once 'classes/class-halp-form-field-text.php';
			require_once 'classes/class-halp-form-field-textarea.php';
		}
		
	}
	
	/**
	 * Creates a Halp_Form object
	 * 
	 * @uses The same parameters as Halp_Form::__construct()
	 * 
	 * @param array $attributes Associative array of HTML attributes to be applied to the <Form> tag
	 * @param array $options Associative array of options for the form object
	 * @param array $values Associative array of values to populate form fields with
	 * @param array $errors Associative array of errors to populate form fields with
	 * 
	 * @see Halp_Form::__construct() for further details on these parameters
	 * 
	 * @return Halp_Form Form object
	 */
	public static function create_form( $attributes = array(), $options = array(), $values = array(), $errors = array() ) {
		
		return new Halp_Form( $attributes, $options, $values, $errors );
		
	}
	
	/**
	 * Creates a Halp_Form_Field object
	 * 
	 * @uses The same parameters as Halp_Form_Field::__construct() plus $field_type for class mapping
	 * @uses Halp_Form_Field::map_field_class() to map chosen $field_type to the appropriate class
	 * 
	 * @param string $field_type {@see Halp_Form_Field::$type_class_maps for available $field_type's}
	 * @param string $field_name Field name to be used in the HTML input element(s)
	 * @param string $label Textual label to be used in <label> tags where applicable
	 * @param array $attributes Associative array of HTML attributes to be applied to the field tag(s)
	 * @param array $options Associative array of options for the field object
	 * @param array $wrapper_attributes Associative array of HTML attributes to be applied to the field wrapper's tag(s)
	 * 
	 * @see Halp_Form_Field::__construct() for further details on these parameters
	 * 
	 * @return Halp_Form_Field Form field object
	 */
	public static function create_field( $field_type = 'text', $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
		
		$field_classname = Halp_Form_Field::map_field_class( $field_type );
		
		return new $field_classname( $field_name, $label, $attributes, $options, $wrapper_attributes );
		
	}
	
	/**
	 * Creates a Halp_Form_Validator object
	 * 
	 * @param array $request Associate array of form values to validate ($_POST|$_GET)
	 * 
	 * @return Halp_Form_Validator Form validator object
	 */
	public static function create_validator( $request = array() ) {
		
		return new Halp_Form_Validator( $request );
		
	}
	
	/**
	 * Creates a Halp_Form_Handler object
	 *
	 * @return Halp_Form_Handler Form handler object
	 */
	public static function create_handler() {
		
		return new Halp_Form_Handler();
		
	}
	
}