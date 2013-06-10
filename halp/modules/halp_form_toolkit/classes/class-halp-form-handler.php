<?php

/*
 * The Halp_Form_Handler class simplifies the task of keeping
 * track of errors in user submitted forms and the form
 * field values that were entered correctly.
 * 
 */
/**
 * Halp Form Handler Class
 * 
 * Simplifies the task of intercepting $_POST & $_GET requests and aids in
 * keeping track of values and errors submitted in a web form.
 * Supports storing values and errors in $_SESSION for transfer between scripts
 */
class Halp_Form_Handler {
	
	/** @type array Associative array of user-submitted values ($_POST|$_GET) */
	protected $values = array();
	
	/** @type array Associative array of errors for each field in $this->values */
	protected $errors = array();
	
	/**
	 * Checks to see if there are any values in the $_POST or $_GET arrays
	 * 
	 * Does not necessarily mean a form was not submitted if it returns
	 * false--but it's a commonly used shorthand way of determining whether or not
	 * to act on a form submission
	 * 
	 * @return bool Whether or not there are values present in $_POST or $_GET
	 */
	public function is_request() {
		
		$method = strtolower( $method );
		
		if ( $method == 'post' && !empty($_POST) ) {
			return true;
		}
		else if ( $method == 'get' && !empty($_GET) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Retrieves the submitted form values from either $_POST or $_GET
	 * 
	 * Also stores them in $this->values
	 * 
	 * @param string $method Form method to check for (post|get)
	 * 
	 * @return array Associative array of values ($_POST|$_GET)
	 */
	public function get_request( $method = 'post' ) {
		
		$src = array();
		
		if ( 'post' == $method ) {
			$src = $_POST;
		}
		else if ( 'get' == $method ) {
			$src = $_GET;
		}
		
		$this->set_values( $src );
		
		return $src;
		
	}
	
	/**
	 * Retrieves previously stored values & errors stored in $_SESSION
	 * 
	 * Values & errors are stored using $this->set_session_data() and stored
	 * in $this->values and $this->errors repsectively
	 */
	public function get_session_data() {
		
		if ( !isset($_SESSION) ) session_start();
		
		/**
		 * Get form value and error arrays, used when there
		 * is an error with a user-submitted form.
		 */
		
		if (isset($_SESSION['form_value_array']) && isset($_SESSION['form_error_array'])) {
			$this->set_values( $_SESSION['form_value_array'] );
			$this->set_errors( $_SESSION['form_error_array'] );
		
			unset($_SESSION['form_value_array']);
			unset($_SESSION['form_error_array']);
		}
		
	}
	
	/**
	 * Stores values & errors in $_SESSION for transfer across scripts
	 */
	public function set_session_data() {
		
		if ( !isset($_SESSION) ) session_start();
		
		$_SESSION['form_value_array'] = $this->get_values();
		$_SESSION['form_error_array'] = $this->get_errors();
		
	}
	
	/**
	 * Sets errors
	 * 
	 * Param: $errors should be an array with keys matching the field names--each
	 * array element containing an array of string error messages
	 * 
	 * @param array $errors Associative array of associative error arrays
	 */
	public function set_errors( $errors = array() ) {
		
		if ( !is_array( $errors ) ) return;
		
		$this->errors = $errors;
	
	}
	
	/**
	 * Adds an error to $this->errors
	 * 
	 * @param string $field_name The Field name to add the error to
	 * @param string $error_msg The error message
	 */
	public function add_error( $field_name = '', $error_msg = '' ) {
	
		if ( !array_key_exists($field_name, $this->errors) ) {
			$this->errors[$field_name] = array();
		}
	
		array_push( $this->errors[$field_name], $error_msg );
	
	}
	
	/**
	 * Checks to see if there are any errors
	 * 
	 * @return bool Whether or not there are any errors
	 */
	public function has_errors() {
	
		return sizeof($this->errors) > 0;
	
	}
	
	/**
	 * Retrieves $this->errors
	 * 
	 * @return array Associative array of error messages--keys matching field names
	 */
	public function get_errors() {
		
		return $this->errors;
		
	}
	
	/**
	 * Gets all errors for a given field name
	 * 
	 * @param string $field_name
	 * 
	 * @return array Array of error messages
	 */
	public function get_field_errors( $field_name = '' ) {
	
		if ( array_key_exists($field_name, $this->errors) ) {
			return $this->errors[$field_name];
		}
		else {
			return array();
		}
	
	}
	
	/**
	 * Set values for $this->values
	 * 
	 * @param array $values Associative array of values--keys matching field names (format ex. $_POST|$_GET)
	 */
	public function set_values( $values = array() ) {
		
		if ( !is_array( $values ) ) return;
		
		$this->values = $values;
	
	}
	
	/**
	 * Sets the field values for a given field in $this->values
	 * 
	 * @param string $field_name
	 * @param array|string $value Array of string values (for checkboxes/radios, etc) or a string value
	 */
	public function set_value( $field_name = '', $value = '' ) {
	
		$this->values[$field_name] = $value;
	
	}
	
	/**
	 * Retrieves $this->values
	 * 
	 * @return array Associative array of values--keys matching field names (format ex. $_POST|$_GET)
	 */
	public function get_values() {
		
		return $this->values;
		
	}
	
	/**
	 * Retrieves the value(s) for a given field name
	 * 
	 * @param string $field_name
	 * 
	 * @return array|string|null Array of string values (for checkboxes/radios, etc), a string value or null if not found
	 */
	public function get_field_value( $field_name = '' ) {
	
		if ( array_key_exists($field_name, $this->values) ) {
			return $this->values[$field_name];
		}
		else {
			return null;
		}
	
	}
	
	/**
	 * Retrieves the value(s) for a given field name, but cleaned
	 * 
	 * @uses htmlspecialchars & stripslashes on string values
	 * 
	 * @param string $field_name
	 * 
	 * @return array|string|null Array of string values (for checkboxes/radios, etc), a string value or null if not found
	 */
	public function get_field_value_clean( $field_name = '' ) {
	
		$value = $this->get_field_value( $field_name ) ;
	
		if ( is_string( $value ) ) {
			return htmlspecialchars( stripslashes( $value ) );
		}
		else {
			return $value;
		}
	
	}
	
}