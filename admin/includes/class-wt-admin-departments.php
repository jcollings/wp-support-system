<?php
class WT_Admin_Departments{

	public function __construct(){
		// add_action('department_add_form_fields', array($this, 'add_form_fields'));
		// add_action('department_edit_form_fields', array($this, 'edit_form_fields'));
		// add_action('edited_department', array($this, 'save_custom_meta'));
		// add_action('create_department', array($this, 'save_custom_meta'));
		add_action( 'admin_head', array($this, 'hide_slug_box')  );
	}

	/**
	 * Display department custom fields for add view
	 */
	public function add_form_fields(){
		?>
		<div class="form-field">
			<label for="term_meta[notification_emails]"><?php _e( 'Email Addresses' ); ?></label>
			<textarea name="term_meta[notification_emails]" id="term_meta[notification_emails]" rows="5" cols="40"></textarea>
			<p class="description"><?php _e( 'List of emails on seperate lines, who you would like to be notified of activity in this department.'); ?></p>
		</div>
		<?php
	}

	/**
	 * Display department custom fields for edit view
	 * 
	 * @param  object $term 
	 * @return void
	 */
	public function edit_form_fields($term){
		// put the term ID into a variable
		$t_id = $term->term_id;

		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "department_$t_id" ); ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[notification_emails]"><?php _e( 'Email Addresses' ); ?></label></th>
			<td>
				<textarea name="term_meta[notification_emails]" id="term_meta[notification_emails]" rows="5" cols="40"><?php echo is_array($term_meta['notification_emails']) ? esc_attr( implode("\r\n", $term_meta['notification_emails']) ) : ''; ?></textarea>
				<p class="description"><?php _e( 'List of emails on seperate lines, who you would like to be notified of activity in this department.'); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Hide unnecessary Fields
	 * 
	 * @return void
	 */
	function hide_slug_box(){
	    global $pagenow;

	    if(is_admin() && $pagenow == 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'department'){
	        echo "<script type='text/javascript'>
	            jQuery(document).ready(function($) {
	                $('#tag-slug, #parent, #tag-description').parent('div').hide();
	                $('#slug, #parent, #description').parent().parent('tr').hide();
	            });
	            </script>
	        ";
	    }
	}

	/**
	 * Save department custom fields
	 * 
	 * @param  int $term_id 
	 * @return void
	 */
	public function save_custom_meta( $term_id ) {

		if ( isset( $_POST['term_meta'] ) ) {
			$t_id = $term_id;
			$term_meta = get_option( "department_$t_id" );
			$cat_keys = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$value = $_POST['term_meta'][$key];
					if($key == 'notification_emails'){

						$value = explode("\r\n", $value);
						foreach($value as $id => $email){
							if(!is_email( $email)){
								unset($value[$id]);
							}
						}

					}
					$term_meta[$key] = $value;
				}
			}
			// Save the option array.
			update_option( "department_$t_id", $term_meta );
		}
	} 
}

new WT_Admin_Departments();