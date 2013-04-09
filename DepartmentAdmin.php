<?php 
/**
 * Ticket Admin
 * 
 * Handles all Department administration functions
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @package Support System
 * @since 0.0.2
 */
class DepartmentAdmin{


	/**
	 * Setup Hooks
	 * 
	 * @param [type] &$config [description]
	 */
	public function __construct(&$config){
		$this->config = $config;

		add_action('init', array($this, 'register_tax'));

		if(is_admin()){
			add_action( 'support_groups_add_form_fields', array($this, 'support_group_add_new_meta_field'), 10, 2 );
			add_action( 'support_groups_edit_form_fields', array($this, 'support_group_edit_meta_field'), 10, 2 );
			add_action( 'edited_support_groups', array($this, 'support_group_save_custom_meta'), 10, 2 );  
			add_action( 'create_support_groups', array($this, 'support_group_save_custom_meta'), 10, 2 );
			add_action( 'admin_head', array($this, 'hide_slug_box')  );
			add_action( 'parent_file', array($this, 'menu_highlight'));
		}
	}
	/**
	 * Register Taxonomoy
	 * 
	 * @return void
	 */
	function register_tax(){
		$labels = array(
		    'name'                => _x( 'Departments', 'taxonomy general name' ),
		    'singular_name'       => _x( 'Department', 'taxonomy singular name' ),
		    'search_items'        => __( 'Search Departments' ),
		    'all_items'           => __( 'All Departments' ),
		    'parent_item'         => __( 'Parent Department' ),
		    'parent_item_colon'   => __( 'Parent Department:' ),
		    'edit_item'           => __( 'Edit Department' ), 
		    'update_item'         => __( 'Update Department' ),
		    'add_new_item'        => __( 'Add New Department' ),
		    'new_item_name'       => __( 'New Department Name' ),
		    'menu_name'           => __( 'Department' )
		); 	
		register_taxonomy(  
			'support_groups',  
			'supportmessage',  
			array(  
				'labels' => $labels,  
				'public' => true,
		        'show_in_nav_menus' => true,
		        'show_ui' => true,
		        'show_tagcloud' => false,
		        'show_admin_column' => true,
		        'hierarchical' => true,
		        'rewrite' => true,
		        'query_var' => 'suppor'
			)  
		);
	}

	/**
	 * Add Fields to Add Support Group Taxonomy
	 * 
	 * @return void
	 */
	function support_group_add_new_meta_field(){
		?>
		<div class="form-field">
			<label for="term_meta[notification_emails]"><?php _e( 'Email Addresses' ); ?></label>
			<textarea name="term_meta[notification_emails]" id="term_meta[notification_emails]" rows="5" cols="40"></textarea>
			<p class="description"><?php _e( 'List of emails on seperate lines, who you would like to be notified of activity in this department.'); ?></p>
		</div>
		<?php
	}

	/**
	 * Add Fields to Edit Support Group Taxonomy
	 * 
	 * @param  WP_Term $term 
	 * @return void
	 */
	function support_group_edit_meta_field($term) {
 
		// put the term ID into a variable
		$t_id = $term->term_id;
	 
		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "support_groups_$t_id" ); ?>
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
	 * Save Support Group Fields
	 * 
	 * @param  int $term_id 
	 * @return void
	 */
	function support_group_save_custom_meta( $term_id ) {

		if ( isset( $_POST['term_meta'] ) ) {
			$t_id = $term_id;
			$term_meta = get_option( "support_groups_$t_id" );
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
			update_option( "support_groups_$t_id", $term_meta );
		}
	} 

	/**
	 * Hide unnecessary Fields
	 * 
	 * @return void
	 */
	function hide_slug_box(){
	    global $pagenow;

	    if(is_admin() && $pagenow == 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'support_groups'){
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
	 * Set Active Menu Item
	 * 
	 * Fix to dsiplay the correct menu item for support group taxonomy
	 * 
	 * @param  string $parent_file 
	 * @return string
	 */
	public function menu_highlight($parent_file) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if ($taxonomy == 'support_groups')
			$parent_file = 'support-tickets';
		return $parent_file;
	}
}
?>