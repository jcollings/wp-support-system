<?php
class WP_Engine_Form
{
	var $_settings = array();
	var $hasPosted = false;
	private $errors = array();

	public function __construct($name)
	{
		$this->_settings['name'] = $name;
	}

	private function setValue($field){
		return isset($_POST[$field]) ? $_POST[$field] : false;	
	}

	private function setLabel($name){
		$output = '<label>'.$name.'</label>';
		return $output;
	}
	private function setError($name){
		$error = $this->get_error_msg($name);
		if(!empty($error))
			return '<span class="error_msg">' . $error . '</span>';
		
		return '';
	}

	public function create($args = array())
	{	
		$file = null;
		extract($args);

		$form_name = $this->_settings['name'];

		if(!is_null($file))
			$file = 'enctype="multipart/form-data"';
		// if form has been submitted
		if(isset($_POST['SupportFormType']) && $_POST['SupportFormType'] == $form_name)
			$this->hasPosted = true;

		$output = '<form name="'.$form_name.'" id="'.$form_name.'" method="POST" action="" '.$file.' >';
		$output .= '<input type="hidden" name="SupportFormType" id="SupportFormType" value="'.$form_name.'" />';
		$output .=   wp_nonce_field('support_form_nonce_'.strtolower($form_name),'SupportFormNonce_'.$form_name, true, false);
		return $output;
	}

	public function hidden($name, $args = array())
	{
		$output = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$this->setValue().'" />';
		return $output;
	}

	public function text($name, $args = array())
	{
		// set default arg values
		$required = false;
		$label = $name;
		extract($args);

		$value = $this->setValue($name);
		$classes = array('input', 'text');

		if(!empty($class)){
			if(!is_array($class)){
				$classes[] = $class;
			}
		}

		if($required == true)
		{
			if(empty($value) && $this->hasPosted == true)
				$classes[] = 'error';

			$classes[] = 'required';
		}
			
		$output = '<div class="'.implode(' ', $classes).'" />';

		if($label !== false)
			$output .= $this->setLabel($label);

		$output .= '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" />';

		// $output .= $this->setError($name);

		$output .= '</div>';
		return $output;
	}

	public function file($name, $args = array())
	{
		// set default arg values
		$required = false;
		$label = $name;
		extract($args);

		$value = $this->setValue($name);
		$classes = array('input', 'file');

		if($required == true)
		{
			if(empty($value) && $this->hasPosted == true)
				$classes[] = 'error';

			$classes[] = 'required';
		}
			
		$output = '<div class="'.implode(' ', $classes).'" />';

		if($label !== false)
			$output .= $this->setLabel($label);

		$output .= '<input type="file" name="'.$name.'" id="'.$name.'" value="'.$value.'" />';

		$output .= '</div>';
		return $output;
	}

	public function password($name, $args = array())
	{
		// set default arg values
		$required = false;
		$label = $name;
		extract($args);

		$value = $this->setValue($name);
		$classes = array('input', 'password');

		if(!empty($class)){
			if(!is_array($class)){
				$classes[] = $class;
			}
		}

		if($required == true)
		{
			if(empty($value) && $this->hasPosted == true)
				$classes[] = 'error';

			$classes[] = 'required';
		}
			
		$output = '<div class="'.implode(' ', $classes).'" />';

		if($label !== false)
			$output .= $this->setLabel($label);

		$output .= '<input type="password" name="'.$name.'" id="'.$name.'" value="'.$value.'" />';
		
		$output .= '</div>';
		return $output;
	}

	public function textarea($name, $args = array())
	{
		// set default arg values
		$required = false;
		$label = $name;
		extract($args);

		$value = $this->setValue($name);
		$classes = array('input', 'textarea');

		if($required == true)
		{
			if(empty($value) && $this->hasPosted == true)
				$classes[] = 'error';

			$classes[] = 'required';
		}
			
		$output = '<div class="'.implode(' ', $classes).'" />';

		if($label !== false)
			$output .= $this->setLabel($label);

		$output .= '<textarea name="'.$name.'" id="'.$name.'">'.$value.'</textarea>';
		
		$output .= '</div>';
		return $output;
	}

	public function wysiwyg($name, $args = array()){
		// set default arg values
		$required = false;
		$label = $name;
		extract($args);

		$value = $this->setValue($name);
		$classes = array('input', 'textarea', 'wysiwyg');

		if($required == true)
		{
			if(empty($value) && $this->hasPosted == true)
				$classes[] = 'error';

			$classes[] = 'required';
		}
			
		$output = '<div class="'.implode(' ', $classes).'" />';

		if($label !== false)
			$output .= $this->setLabel($label);

		$output .= '<textarea name="'.$name.'" id="'.$name.'">'.$value.'</textarea>';
		
		$output .= '</div>';
		return $output;
	}

	public function select($name, $args = array())
	{
		// set default arg values
		$required = false;
		$label = $name;
		extract($args);

		$value = $this->setValue($name);
		$classes = array('input', 'select');

		if(!empty($class)){
			if(!is_array($class)){
				$classes[] = $class;
			}
		}

		if($required == true)
		{
			if(empty($value) && $this->hasPosted == true)
				$classes[] = 'error';

			$classes[] = 'required';
		}
			
		$output = '<div class="'.implode(' ', $classes).'" />';

		if($label !== false)
			$output .= $this->setLabel($label);

		$output .= '<select name="'.$name.'" id="'.$name.'">';

		foreach($options as $id => $option)
		{
			$output .= '<option value="'.$id.'">'.$option.'</option>';
		}

		$output .= '</select>';
		
		$output .= '</div>';
		return $output;
	}

	public function submit($name, $args = array())
	{
		$output = '<div class="input submit">';
		$output .= '<input type="submit" name="'.$name.'" value="'.$name.'" />';
		$output .= '</div>';
		return $output;
	}

	public function end($submit = '')
	{
		$output = '';
		if($submit != '')
			$output .= $this->submit($submit);

		$output .= '</form>';
		return $output;
	}

	public function get_error_msg($name = ''){

		if(!isset($this->errors['fields']) || !isset($this->errors['fields'][$name]))
			return false;
		// $fields = $this->errors['fields'];
		return $this->errors['fields'][$name];
	}

	public function errors()
	{
		global $current_user;
		$current_user = wp_get_current_user();
		$this->errors = array(
			'message' => get_transient($this->_settings['name'].'Error_'.$current_user->ID),
			'fields' => get_transient($this->_settings['name'].'Field_'.$current_user->ID)
		);
		delete_transient($this->_settings['name'].'Error_'.$current_user->ID);
		delete_transient($this->_settings['name'].'Field_'.$current_user->ID);
		return $this->errors;
	}

	/**
	 * Check to see if form has been submitted
	 * @param  string $form id of current form
	 * @return bool/string returns false if not complete
	 */
	public function complete()
	{
		global $current_user;
		$current_user = wp_get_current_user();
		$success = get_transient($this->_settings['name'].'Success_'.$current_user->ID);
		delete_transient($this->_settings['name'].'Success_'.$current_user->ID);
		return $success;	
	}
}