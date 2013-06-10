<?php

/**
 * Halp Web Form Class
 * 
 * - Responsible for the creation of the HTML <form> element
 * - Provides methods for creating form field objects and storing them in the form object
 * - Takes form field values from a Halp_Form_Handler and populates stored fields with values
 * - Takes form field errors from a Halp_Form_Validator and populates stored fields with errors
 * - Progressive Render Mode: (off by default) allows developers to manually control the rendering of the form
 *   elements as they create them. {@see Halp_Form::progressive_render() for details}
 * - Automatically inserts a hidden <input name="form_is_submitted"> to determine if the form has been submitted.
 *   For use when using default field values, {@see Halp_Form_Field::set_default() for details}
 *   
 * @todo Provide examples of formatting for $this->values|$this->errors arrays
 * @todo Provide examples of available $options for this form object (in __construct PHPDoc)
 * 
 * Testing:
 */
class Halp_Form {
	
	/** @type bool Whether or not Progressive Render Mode is on */
	protected $progressive_render = false;
	
	/** @type bool Whether or not the form as been submitted */
	protected $is_submitted = false;
	
	/** @type array Associative array of HTML attributes to be applied to the <form> element */
	protected $attributes = array();
	
	/** @type array Default values for $this->attributes */
	protected $default_attributes = array(
			'accept' => null,
			'accept-charset' => null,
			'action' => '',
			'autocomplete' => null,
			'enctype' => null,
			'method' => 'post',
			'name' => null,
			'novalidate' => null,
			'target' => null,
			'class' => null,
			'id' => null
	);
	
	/* @type array CSS classes to be applied to the <form> element */
	protected $classes = array();
	
	/** @type array Associative array of options used in creation of the form object */ 
	protected $options = array();
	
	/** @type array Default values for $this->options */
	protected $default_options = array(
			'render_tag' => true,
			'render_is_submitted_field' => true,
			'before' => '',
			'before_inner' => '',
			'after_inner' => '',
			'after' => ''
	);
	
	/** @type array Array of stored Halp_Form_Field objects & HTML strings to be rendered */
	protected $fields = array();
	
	/** @type array Global field HTML attributes {@see Halp_Form_Field::$attributes for details} */
	protected $field_attributes = array();
	
	/** @type array Global field options {@see Halp_Form_Field::$options for details} */
	protected $field_options = array();
	
	/** @type string|null HTML tag to wrap all form fields in. No wrapper if value is null */
	protected $field_wrapper_tag = null;
	
	/** @type array Global field wrapper HTML attributes. {@see Halp_Form_Field::$wrapper_attributes for details} */ 
	protected $field_wrapper_attributes = array();
	
	/** @type array Associative array of form field errors. {@see Halp_Form::set_errors() for details} */
	protected $errors = array();
	
	/** @type array Associative array of form field values. {@see Halp_Form::set_values() for details}  */
	protected $values = array();
	
	/** @type bool Whether or not to render field errors */
	protected $render_errors = true;
	
	/** @type bool Whether or not to render HTML tab indexes for fields */
	protected $render_tab_indexes = false;
	
	/** @type int Counter that keeps track of the current HTML tab index */
	protected $last_tab_index = 0;
	
	/** @type bool Whether or not to render the automatic <input name="form_is_submitted"> element. {@see Halp_Form_Field::set_default() for details} */
	protected $render_is_submitted_field = true;
	
	/**
	 * Constructor method for Halp Form object
	 * 
	 * @param array $attributes Associative array of HTML attributes to be applied to the <Form> tag
	 * @param array $options Associative array of options for the form object
	 * @param array $values Associative array of values to populate form fields with
	 * @param array $errors Associative array of errors to populate form fields with
	 */
	public function __construct( $attributes = array(), $options = array(), $values = array(), $errors = array() ) {
		
		if ( !is_array($attributes) ) $attributes = array();
		$this->attributes = array_merge( $this->default_attributes, $attributes );
		
		if ( !is_array($options) ) $options = array();
		$this->options = array_merge( $this->default_options, $options );
		
		if ( !is_array($errors) ) $errors = array();
		$this->errors = $errors;
		
		if ( !is_array($values) ) $values = array();
		$this->values = $values;
		
		// Hidden form_is_submitted field for use in field population overrides
		if ( array_key_exists('render_is_submitted_field', $this->options) && $this->options['render_is_submitted_field'] == true ) {
			$this->add_field('hidden', 'form_is_submitted');
			if ( array_key_exists('form_is_submitted', $values) ) $this->is_submitted = true;
		}
		
	}
	
	/**
	 * Checks to see if the form has been submitted
	 * 
	 * Uses an automatically generated hidden <input name="form_is_submitted"> to determine if the form has been submitted.
 	 * For use when using default field values, {@see Halp_Form_Field::set_default() for details}
 	 * 
 	 * @return bool Whether or not the form has been submitted
	 */
	public function is_submitted() {
		
		return $this->is_submitted;
		
	}
	
	/**
	 * Disables/Enables error output for form fields
	 * 
	 * @param bool $disable Whether or not to disable errors
	 */
	public function disable_errors( $disable = true ) {
	
		$this->render_errors = true == $disable ? false : true;
	
	}
	
	/**
	 * Enable rendering of HMTL tab indexes (off by default)
	 * 
	 * Form field objects are in charge of checking the parent form object's last tab index
	 * using Halp_Form::get_last_tab_index() and updating it using Halp_Form::set_last_tab_index()
	 * based on how many HTML form inputs it renders that support tab indexes
	 * 
	 * @param bool $enable Whether or not to enable tab indexes
	 */
	public function enable_tab_indexes( $enable = true ) {
		
		$this->render_tab_indexes = $enable;
		
	}
	
	/**
	 * Checks to see if rendering of HTML tab indexes is enabled
	 * 
	 * @see Halp_Form::enable_tab_indexes for details
	 * 
	 * @return bool Whether or not tab indexes are enabled
	 */
	public function tab_indexes_enabled() {
		
		return $this->render_tab_indexes;
		
	}
	
	/**
	 * Sets the HTML tab index of the last form field element
	 * 
	 * @see Halp_Form::enable_tab_indexes for details
	 * 
	 * @param int $index Last tab index
	 */
	public function set_last_tab_index( $index = 1 ) {
		
		$this->last_tab_index = $index;
		
	}
	
	/**
	 * Retrieves the last HTML tab index
	 * 
	 * @see Halp_Form::enable_tab_indexes for details
	 * 
	 * @return int Last tab index
	 */
	public function get_last_tab_index() {
		
		return $this->last_tab_index;
		
	}
	
	/**
	 * Sets an HTML attribute for the <form> element
	 * 
	 * @param string $attribute_name Attribute name
	 * @param string|null $value Attribute value. If null, it won't be rendered
	 */
	public function set_attribute( $attribute_name = '', $value = null ) {
		
		$this->attributes[$attribute_name] = $value;
		
	}
	
	/**
	 * Sets an option for the Halp_Form object
	 * 
	 * @see Halp_Form::__construct() for details on options
	 * 
	 * @param string $option_name Option name
	 * @param string|null $value Option value
	 */
	public function set_option( $option_name = '', $value = null ) {
	
		$this->options[$option_name] = $value;
	
	}
	
	
	/**
	 * Sets the HTML attributes to be applied to the <form> element
	 *
	 * @param array $attributes Associative array of HTML attributes
	 * @param bool $override Whether or not to override existing attributes
	 */
	public function set_attributes( $attributes = array(), $override = true ) {
	
		if ( true == $override ) {
			$this->attributes = array_merge( $this->attributes, $attributes );
		}
		else {
			$this->attributes = array_merge( $attributes, $this->attributes );
		}
	
	}
	
	/**
	 * Sets options for the Halp_Form object
	 *
	 * @see Halp_Form::__construct() for details on options
	 *
	 * @param array $options Associative array of options
	 * @param bool $override Whether or not to override existing options
	 */
	public function set_options( $options = array(), $override = true ) {
	
		if ( true == $override ) {
			$this->options = array_merge( $this->options, $options );
		}
		else {
			$this->options = array_merge( $options, $this->options );
		}
	
	}
	/**
	 * Sets the form field wrapper and it's HTML attributes
	 * 
	 * @param string $tag HTML tag
	 * @param array $attributes Associative array of HTML attributes to be applied to the field wrapper element
	 */
	public function set_field_wrapper( $tag = 'li', $attributes = array() ) {
		
		$this->field_wrapper_tag = $tag;
		$this->field_wrapper_attributes = $attributes;
		
	}
	
	/**
	 * Sets the global HTML attributes to be applied to the form field elements where appropriate
	 * 
	 * @param array $attributes Associative array of HTML attributes
	 */
	public function set_field_attributes( $attributes = array() ) {
	
		$this->field_attributes = $attributes;
	
	}
	
	/**
	 * Sets global options for form field objects
	 * 
	 * @see Halp_Form_Field::__construct() for details on field options
	 * 
	 * @param array $options Associative array of options
	 */
	public function set_field_options( $options = array() ) {
	
		$this->field_options = $options;
	
	}
	
	/**
	 * Sets the values to be used in populating form fields
	 * 
	 * Values can be retrieved from $_POST|$_GET or Halp_Form_Handler::get_values()
	 * 
	 * @param array $values Associative array of values
	 */
	public function set_values( $values = array() ) {
		
		$this->values = $values;
		
	}
	
	/**
	 * Sets the errors to be used in populating form fields
	 * 
	 * Errors can be retrieved from Halp_Form_Validator::get_errors()
	 * 
	 * @param array $errors Associative array of errors
	 */
	public function set_errors( $errors = array() ) {
		
		$this->errors = $errors;
		
	}
	
	/**
	 * Creates and stores Halp_Form_Field of specified $field_type
	 * 
	 * All global field options/attributes/values/errors, etc will be passed on to each field
	 * created using this method
	 * 
	 * @uses Halp_Form_Toolkit::create_field to create Halp_Form_Field object
	 * 
	 * @param string $field_type {@see Halp_Form_Field::$type_class_maps for available $field_type's}
	 * @param string $field_name Field name to be used in the HTML input element(s)
	 * @param string $label Textual label to be used in <label> tags where applicable. No label rendered if null.
	 * @param array $attributes Associative array of HTML attributes to be applied to the field tag(s)
	 * @param array $options Associative array of options for the field object
	 * @param array $wrapper_attributes Associative array of HTML attributes to be applied to the field wrapper's tag(s)
	 * 
	 * @see Halp_Form_Field::__construct() for further details on these parameters
	 * 
	 * @return Halp_Form_Field Form field object
	 */
	public function add_field( $field_type = 'text', $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
		
		$attributes = array_merge($this->field_attributes, $attributes);
		
		$options = array_merge($this->field_options, $options);
		
		$wrapper_attributes = array_merge($this->field_wrapper_attributes, $wrapper_attributes);
		
		$field = Halp_Form_Toolkit::create_field( $field_type, $field_name, $label, $attributes, $options, $wrapper_attributes );
		
		$field->populate( $this->values );
		
		$field->disable_errors( !$this->render_errors );
		
		$field->set_wrapper( $this->field_wrapper_tag, $this->field_wrapper_attributes);
		
		$field->set_form( $this );
		
		if ( array_key_exists( $field_name, $this->errors ) ) $field->set_errors( $this->errors[$field_name] );
		
		array_push( $this->fields, $field );
		
		return $field;
		
	}
	
	/**
	 * Adds raw HTML string to the array of fields to be rendered
	 * 
	 * @param string $html HTML to be rendered
	 */
	public function add_html( $html = '' ) {
		
		array_push( $this->fields, $html );
		
		if ( true === $this->progressive_render ) echo $html;
		
	}
	
	/**
	 * Turns on Progressive Render Mode
	 * 
	 * Progressive Render Mode (off by default) allows developers to control the rendering of all HTML as form
	 * elements are created.
	 * - When on:
	 * -- HTML and other output can be echo'ed or outputted wherever desired in a PHPHMTL file while creating the form fields
	 * -- $form->open() must be called to render opening <form> element, etc
	 * -- $field->render() must be called manually on each field object when it is to be rendered
	 * -- $form->close() must be called to render closing </form> element, etc
	 * - When off:
	 * -- Creating the form object renders the opening <form> element, etc automatically
	 * -- Field objects don't need to be rendered manually
	 * -- When you're done creating the form & its fields, call $form->render() to render all fields and closing </form> element
	 * 
	 * @param bool $on Whether or not to turn on Progressive Render Mode
	 */
	public function progressive_render( $on = true ) {
		
		$this->progressive_render = $on;
		
	}
	
	/**
	 * Renders opening <form> element, etc
	 * 
	 * To be used when Progressive Render Mode is on
	 * 
	 * @see Halp_Form::progressive_render() for details on Progressive Render Mode
	 */
	public function open() {
		
		// Render open tag & any current form elements
		
		$this->_pre_render();
		
		echo $this->options['before'];
		
		echo true === $this->options['render_tag'] ? $this->_render_tag() : '';
		
	}
	
	/**
	 * Renders closing </form> element, etc
	 * 
	 * To be used when Progressive Render Mode is on
	 * 
	 * @see Halp_Form::progressive_render() for details on Progressive Render Mode
	 */
	public function close() {
		
		// Render close tag
		
		$this->_post_render();
		
		echo true === $this->options['render_tag'] ? $this->_render_tag( true ) : '';
		
		echo $this->options['after'];
		
	}
	
	/**
	 * Renders all stored fields and HTML
	 * 
	 * Can be used manually when Progressive Render Mode is on instead of calling
	 * $field->render() on each field
	 * 
	 * @see Halp_Form::progressive_render() for details on Progressive Render Mode
	 * 
	 * @param bool $echo Whether or not to echo the output HTML
	 * 
	 * @return string HTML output
	 */
	public function render_fields( $echo = true ) {
		
		// Render Form Elements
		
		$html = '';
		
		foreach( $this->fields as $field ) {
			if ( is_subclass_of($field, 'Halp_Form_Field') ) {
				$html .= $field->render( false );
			}
			else if ( is_string($field) ) {
				$html .= $field;
			}
		}
		
		if ( true === $echo ) echo $html;
		
		return $html;
		
	}
	
	/**
	 * Hook called before beginning to render the HTML output
	 *
	 * Can optionally be overridden in user-defined classes that
	 * inherit Halp_Form (for custom functionality)
	 *
	 * @internal
	 */
	protected function _pre_render() {
		
		$str_classes = array();
		if ( is_string( $this->attributes['class'] ) ) {
			$str_classes = Halp::str_to_classes( $this->attributes['class'] );
		}
		
		$this->classes = array_merge($str_classes, $this->classes);
		
		if ( sizeof( $this->classes ) > 0 ) {
			$this->attributes['class'] = implode( ' ', $this->classes );
		}
		else {
			$this->attributes['class'] = null;
		}
		
	}
	
	/**
	 * Hook called after rendering the HTML output
	 *
	 * Can optionally be overridden in user-defined classes that
	 * inherit Halp_Form (for custom functionality)
	 *
	 * @internal
	 */
	protected function _post_render() {
	
		// Silence is golden
	
	}
	
	/*
	 * Renders all HTML output of the form and its stored fields & HTML
	 * 
	 * @param bool $echo Whether or not to echo the output HTML
	 * 
	 * @return string HTML output
	 */
	public function render( $echo = true ) {
		
		ob_start();
		
		$this->open();
		
		$this->render_fields();
		
		$this->close();
		
		$output = ob_get_contents();
		ob_end_clean();
		
		if ( true === $echo ) echo $output;
		
		return $output;
		
	}
	
	/**
	 * Renders the HTML <form> tag and it's attributes
	 * 
	 * @internal
	 * 
	 * @param bool $close If false, render opening tag, otherwise closing tag
	 * 
	 * @return string HTML output
	 */
	protected function _render_tag( $close = false ) {
		
		if ( false === $close ) {
			
			// Render Open Tag
				
			$html = $this->options['before'];
				
			$html .= '<form ';
				
			foreach( $this->attributes as $att => $val ) {
				if ( is_null( $val ) ) continue;
				
				$html.= $att .'="'. $val .'" ';
			}
				
			$html .= '>';
				
			$html .= $this->options['before_inner'];
				
			return $html;
		}
		else {

			// Render Close Tag
			$html = $this->options['after_inner'];
				
			$html .= '</form>';
				
			$html .= $this->options['after'];
				
			return $html;
			
		}
		
	}
	
	/**
	 * Adds a CSS class to the HTML <form> element
	 * 
	 * @param string|null $class CSS class
	 */
	public function add_class( $class = null ) {
		
		if ( !is_string( $class ) ) return;
		
		$this->classes = Halp::_add_class( $class, $this->classes );
	
	}
	
	/**
	 * Removes a CSS class from the HTML <form> element
	 * 
	 * @param string|null $class CSS class
	 */
	public function remove_class( $class = null ) {
	
		if ( !is_string( $class ) ) return;
		
		$this->classes = Halp::_remove_class( $class, $this->classes );
	
	}
	
}
