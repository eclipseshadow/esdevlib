<?php

/**
 * Halp Web Form Field Class
 * 
 * @todo Add support for HTML5 elements (datalist, keygen, output, email, etc)
 * @todo HTML fallback support (use php to detect browser, use JS and non-html inputs to mimic html inputs like "email")
 * @todo Provide examples of available $options for this field object (in __construct PHPDoc)
 * @todo Specify dafault param values in all methods
 */
class Halp_Form_Field {
	
	/** @type array Associative array of Halp_Form_Field child class mappings */
	protected static $type_class_maps = array(
			'checkbox' => 'Halp_Form_Field_Checkbox',
			'checkbox_group' => 'Halp_Form_Field_Checkbox_Group',
			'hidden' => 'Halp_Form_Field_Hidden',
			'password' => 'Halp_Form_Field_Password',
			'radio_group' => 'Halp_Form_Field_Radio_Group',
			'select' => 'Halp_Form_Field_Select',
			'submit' => 'Halp_Form_Field_Submit',
			'text' => 'Halp_Form_Field_Text',
			'textarea' => 'Halp_Form_Field_Textarea',
			'button' => 'Halp_Form_Field_Button'
	);
	
	/** @type Halp_Form|null Parent form object if available */
	protected $form = null;
	
	/** @type array Associative array of HTML attributes to be applied to the form <input> element(s) where applicable */
	protected $attributes = array();
	
	/** @type array Default values for $this->attributes */
	protected $default_attributes = array(
			'type' => 'text',
			'name' => null,
			'class' => null,
			'id' => null,
			'value' => null
	);
	
	/** @type array CSS classes to be applied to the form <input> element(s) where applicable */
	protected $classes = array();
	
	/** @type array Associative array of options used in creation of the form field object */
	protected $options = array();
	
	/** @type array Default values for $this->options */
	protected $default_options = array(
			'required' => false,
			'required_class' => 'field_required',
			'before' => '',
			'after' => '',
			'label_before' => '',
			'label_before_inner' => '',
			'label_after_inner' => '',
			'label_after' => '',
			'error_render_position' => 'bottom_inside', // (top_outside, top_inside, bottom_inside, bottom_outside)
			'error_box_class' => 'field_error'
	);
	
	/** @type string|null HTML tag to wrap the form field in. No wrapper if value is null */
	protected $wrapper_tag = null;
	
	/** @type array Associative array of HTML attributes to be applied to the form field's wrapper */
	protected $wrapper_attributes = array();
	
	/** @type array Default values for $this->wrapper_attributes */
	protected $default_wrapper_attributes = array(
			'id' => null,
			'class' => null
	);
	
	/** @type array CSS classes to be applied to the form field's wrapper */
	protected $wrapper_classes = array();
	
	/** @type string Form field's name to be used in name="" attribute where applicable */
	protected $field_name = '';
	
	/** @type string|null Textual label to be used in <label> tags where applicable. No label rendered if null. */
	protected $label = null;
	
	/** @type array Associative array of errors to populate the form field with */
	protected $errors = array();
	
	/** @type bool Whether or not to render form field errors */
	protected $render_errors = true;
	
	/**
	 * Maps a given $field_type to a Halp_Form_Field child class
	 * 
	 * @see self::$type_class_maps for available $field_type's
	 * 
	 * @param string $field_type
	 * 
	 * @return string The Halp_Form_Field child class name if found
	 */
	public static function map_field_class( $field_type = '' ) {
	
		return array_key_exists( $field_type, self::$type_class_maps ) ? self::$type_class_maps[$field_type] : 'text';
	
	}
	
	/**
	 * Adds a custom Halp_Form_Field child class mapping to self::$type_class_maps
	 * 
	 * For use in extending the available Halp_Form_Field's available using Halp_Form::add_field()
	 * and Halp_Form_Toolkit::create_field()
	 * 
	 * @param string $field_type
	 * @param string Halp_Form_Field child class name
	 */
	public static function add_field_class_mapping( $field_type = '', $field_type_class = '' ) {
		
		self::$type_class_maps[$field_type] = $field_type_class;
		
	}
	
	/**
	 * Constructor method for Halp Form Field object
	 * 
	 * @param string $field_name Field name to be used in the HTML input element(s)
	 * @param string $label Textual label to be used in <label> tags where applicable
	 * @param array $attributes Associative array of HTML attributes to be applied to the field tag(s)
	 * @param array $options Associative array of options for the field object
	 * @param array $wrapper_attributes Associative array of HTML attributes to be applied to the field wrapper's tag(s)
	 */
	public function __construct( $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
		
		$this->field_name = $field_name;
		$this->label = $label;
		
		if ( !is_array($attributes) ) $attributes = array();
		$this->attributes = array_merge( $this->default_attributes, $attributes );
		
		if ( !is_array($options) ) $options = array();
		$this->options = array_merge( $this->default_options, $options );
		
		if ( !is_array($wrapper_attributes) ) $wrapper_attributes = array();
		$this->wrapper_attributes = array_merge( $this->default_wrapper_attributes, $wrapper_attributes );
		
		if ( true == $this->options['required'] ) $this->add_wrapper_class( $this->options['required_class'] );
		
	}
	
	/*
	 * Sets parent Halp_Form object
	 * 
	 * Parent Halp_Form object is necessary for auto-population of values/errors/etc
	 * 
	 * @param Halp_Form|null $form
	 */
	public function set_form( $form = null ) {
		
		$this->form = $form;
		
	}
	
	/**
	 * Disables rendering of field errors
	 * 
	 * @param bool $disable Whether or not to disable errors
	 */
	public function disable_errors( $disable = true ) {
		
		$this->render_errors = true == $disable ? false : true;
		
	}
	
	/**
	 * Sets the field wrapper and it's HTML attributes
	 * 
	 * @param string $tag HTML tag
	 * @param array $attributes Associative array of HTML attributes to be applied to the field wrapper element
	 */
	public function set_wrapper( $tag = 'li', $attributes = array() ) {
	
		$this->wrapper_tag = $tag;
		$this->set_wrapper_attributes( $attributes );
	
	}
	
	/**
	 * Sets an HTML attribute for the field <input> element(s) where applicable
	 * 
	 * @param string $attribute_name Attribute name
	 * @param string|null $value Attribute value. If null, it won't be rendered
	 */
	public function set_attribute( $attribute_name = '', $value = null ) {
	
		$this->attributes[$attribute_name] = $value;
	
	}
	
	/**
	 * Sets an option for the Halp_Form_Field object
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
	 * Sets the HTML attributes to be applied to the field's <input> element(s) where applicable
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
	 * Sets options for the Halp_Form_Field object
	 *
	 * @see Halp_Form_Field::__construct() for details on options
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
	 * Sets the HTML attributes to be applied to the field's wrapper element
	 * 
	 * @param array $wrapper_attributes Associative array of HTML attributes
	 * @param bool $override Whether or not to override existing attributes
	 */
	public function set_wrapper_attributes( $wrapper_attributes = array(), $override = true ) {
		
		if ( true == $override ) {
			$this->wrapper_attributes = array_merge( $this->wrapper_attributes, $wrapper_attributes );
		}
		else {
			$this->wrapper_attributes = array_merge( $wrapper_attributes, $this->wrapper_attributes );
		}
		
	}
	
	/*
	 * Disables the form field by setting its disabled HMTL attribute
	 * 
	 * @param bool $disabled Whether or not to disable the field
	 */
	public function disabled( $disabled = true ) {
		
		$value = true == $disabled ? 'disabled' : null;
		
		$this->set_attribute( 'disabled', $value );
		
	}
	
	/**
	 * Hook called before beginning to render the HTML output
	 * 
	 * To be overridden in Halp_Form_Field child classes when needed
	 * 
	 * @internal
	 */
	protected function _pre_render() {
		
		// Build Form element CSS classes
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
		
		// Build Wrapper CSS classes
		$str_wrapper_classes = array();
		if ( is_string( $this->wrapper_attributes['class'] ) ) {
			$str_wrapper_classes = Halp::str_to_classes( $this->wrapper_attributes['class'] );
		}
		
		$this->wrapper_classes = array_merge($str_wrapper_classes, $this->wrapper_classes);
		
		if ( sizeof( $this->wrapper_classes ) > 0 ) {
			$this->wrapper_attributes['class'] = implode( ' ', $this->wrapper_classes );
		}
		else {
			$this->wrapper_attributes['class'] = null;
		}
		
	}
	
	/**
	 * Hook called after HTML output is rendered
	 *
	 * To be overridden in Halp_Form_Field child classes when needed
	 *
	 * @internal
	 */
	protected function _post_render() {
		
		// Silence is golden
		
	}
	
	/*
	 * Renders all HTML output of the field
	 * 
	 * @param bool $echo Whether or not to echo the output HTML
	 * 
	 * @return string HTML output
	 */
	public function render( $echo = true ) {
		
		ob_start();
		
		$this->_pre_render();
		
		if ( true == $this->render_errors && sizeof($this->errors) > 0 && 'top_outside' == $this->options['error_render_position'] ) {
			$this->render_errors();
		}
		
		echo !is_null( $this->wrapper_tag ) ? $this->_render_wrapper() : '';
		
		if ( true == $this->render_errors && sizeof($this->errors) > 0 && 'top_inside' == $this->options['error_render_position'] ) {
			$this->render_errors();
		}
		
		echo !is_null( $this->label ) ? $this->_render_label() : '';
	
		echo $this->_render_form_el();
		
		if ( true == $this->render_errors && sizeof($this->errors) > 0 && 'bottom_inside' == $this->options['error_render_position'] ) {
			$this->render_errors();
		}
		
		echo !is_null( $this->wrapper_tag ) ? $this->_render_wrapper( true ) : '';
		
		if ( true == $this->render_errors && sizeof($this->errors) > 0 && 'bottom_outside' == $this->options['error_render_position'] ) {
			$this->render_errors();
		}
		
		$this->_post_render();
		
		$output = ob_get_contents();
		ob_end_clean();
	
		if ( $echo ) echo $output;
		
		return $output;
	
	}
	
	/**
	 * Renders the HTML <input> element and it's attributes
	 * 
	 * To be overridden in Halp_Form_Field child classes when needed or
	 * when output is not done using a traditional <input> tag
	 * 
	 * @return string $html HTML output
	 * 
	 * @internal
	 */
	protected function _render_form_el() {
		
		$html = $this->options['before'];
		
		$html .= '<input ';
			
		foreach( $this->attributes as $att => $val ) {
			if ( is_null( $val ) ) continue;
			
			$html.= $att .'="'. $val .'" ';
		}
			
		$html .= '/>';
		
		$html .= $this->options['after'];
			
		return $html;
	
	}
	
	/**
	 * Renders field HMTL wrapper element
	 * 
	 * @param bool $close If false, render opening tag, otherwise closing tag
	 * 
	 * @return string $html HTML output
	 * 
	 * @internal
	 */
	protected function _render_wrapper( $close = false ) {
		
		if ( false === $close ) {
			
			// Render Open Tag
				
			$html = '<'. $this->wrapper_tag .' ';
				
			foreach( $this->wrapper_attributes as $att => $val ) {
				$html.= $att .'="'. $val .'" ';
			}
			
			$html .= '>';
			
			return $html;
			
		}
		else {
			
			// Render Close Tag
			
			$html = '</'. $this->wrapper_tag .'>';
				
			return $html;
			
		}
		
	}
	
	/**
	 * Renders field HMTL <label> element
	 * 
	 * @return string HTML output
	 * 
	 * @internal
	 */
	protected function _render_label() {
		
		$html = $this->options['label_before'];
	
		$html .= '<label ';
		
		$html .= !is_null( $this->attributes['id'] ) ? 'for="'. $this->attributes['id'] .'"' : '';
		
		$html .= '>';
			
		$html .= $this->options['label_before_inner'];
		
		$html .= $this->label;
		
		$html .= $this->options['label_after_inner'];
		
		$html .= '</label>';
		
		$html .= $this->options['label_after'];
		
		return $html;
		
	}
	
	/**
	 * Populates field value(s)
	 * 
	 * To be overridden in Halp_Form_Field child classes when needed. Field
	 * value(s) population will differ with each type of field
	 * 
	 * @param array $values Associative array of values to be used to populate field. This is the same 
	 * 						$values array as Halp_Form::$values.
	 */
	public function populate( $values = array() ) {
		
		// Silence is golden
	
	}
	
	/**
	 * Sets the default value for the field
	 * 
	 * - Param: $form_is_submitted is used to determine whether or not to populate the field with the 
	 *   default value(s). This is automatically set if $this->form is present but can be overridden if desired.
	 * - This method's ability to function is contingent on whether or not $this->form's 
	 *   $options['render_is_submitted_field'] is set to true (default).
	 * 
	 * @param string|array|null $value Value(s) to be used as default. Should match the same format as the
	 * 								   field's respective array key found in Halp_Form::$values.
	 * @param bool $form_is_submitted Whether or not the form is submitted.
	 */
	public function set_default( $value = null, $form_is_submitted = false ) {
		
		if ( $this->form instanceof Halp_Form && false == $this->form->is_submitted() && false == $form_is_submitted ) {
			$this->populate( array( $this->field_name => $value ) );
		}
		
	}
	
	/**
	 * Sets the errors to be displayed for the field
	 * 
	 * @param array $errors Array of error message strings. 
	 */
	public function set_errors( $errors = array() ) {
		
		$this->errors = $errors;
		
	}
	
	/**
	 * Retrieves the current errors for the field
	 * 
	 * @return array Array of error message strings
	 */
	public function get_errors() {
		
		return $this->errors;
		
	}
	
	/**
	 * Adds an error to $this->errors
	 * 
	 * @param string|null $error_msg The error message
	 */
	public function add_error( $error_msg = null ) {
		
		if ( !is_null( $error_msg ) ) {
			array_push($this->errors, $error_msg);
		}
		
	}
	
	/**
	 * Renders the error messages
	 * 
	 * Errors are rendered in a <div>, each error message wrapped in a <span>
	 * 
	 * @param bool $echo Whether or not to echo the HTML output
	 * 
	 * @return string $html HTML output
	 */
	public function render_errors( $echo = true ) {
		
		$html = '<div class="'. $this->options['error_box_class'] .'">';
		
		foreach( $this->errors as $error ) {
			$html .= '<span>'. $error .'</span>';
		}
		
		$html .= '</div>';
		
		if ( true === $echo ) echo $html;
		
		return $html;
		
	}
	
	/**
	 * Adds a CSS class to the HTML <input> element(s) where applicable
	 * 
	 * @param string|null $class CSS class
	 */
	public function add_class( $class = null ) {
		
		if ( !is_string( $class ) ) return;
		
		$this->classes = Halp::_add_class( $class, $this->classes );
		
	}
	
	/**
	 * Removes a CSS class from the HTML <input> element(s) where applicable
	 *
	 * @param string|null $class CSS class
	 */
	public function remove_class( $class = null ) {
		
		if ( !is_string( $class ) ) return;
		
		$this->classes = Halp::_remove_class( $class, $this->classes );
	
	}
	
	/**
	 * Adds a CSS class to the field's HTML wrapper element
	 *
	 * @param string|null $class CSS class
	 */
	public function add_wrapper_class( $class = null ) {
		
		if ( !is_string( $class ) ) return;
		
		$this->wrapper_classes = Halp::_add_class( $class, $this->wrapper_classes );
	
	}
	
	/**
	 * Removes a CSS class from the field's HTML wrapper element
	 *
	 * @param string|null $class CSS class
	 */
	public function remove_wrapper_class( $class = null ) {
		
		if ( !is_string( $class ) ) return;
		
		$this->wrapper_classes = Halp::_remove_class( $class, $this->wrapper_classes );
	
	}
	
}

