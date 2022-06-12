<?php
/**
 * Custom post types manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_CPT_Tax' ) ) {

	/**
	 * Define Jet_Engine_CPT_Tax class
	 */
	class Jet_Engine_CPT_Tax extends Jet_Engine_Base_WP_Intance {

		public $init_priority = 9;

		/**
		 * Base slug for CPT-related pages
		 * @var string
		 */
		public $page = 'jet-engine-cpt-tax';

		/**
		 * Action request key
		 *
		 * @var string
		 */
		public $action_key = 'cpt_tax_action';

		/**
		 * Metaboxes to register
		 *
		 * @var array
		 */
		public $meta_boxes = array();

		/**
		 * Set object type
		 * @var string
		 */
		public $object_type = 'taxonomy';

		/**
		 * Meta fields for object
		 *
		 * @var null
		 */
		public $meta_fields = array();

		/**
		 * Meta fields with `save custom` option
		 *
		 * @var array
		 */
		public $meta_fields_save_custom = array();

		public $edit_links = array();

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'admin_init', array( $this, 'init_meta_boxes' ) );
			add_action( 'current_screen', array( $this, 'init_edit_links' ) );
		}

		/**
		 * Register post meta
		 *
		 * @return [type] [description]
		 */
		public function init_meta_boxes() {
			if ( jet_engine()->components->is_component_active( 'meta_boxes' ) ) {
				$this->register_meta_boxes();
			}
		}

		/**
		 * Init data instance
		 *
		 * @return [type] [description]
		 */
		public function init_data() {

			if ( ! class_exists( 'Jet_Engine_Base_Data' ) ) {
				require_once jet_engine()->plugin_path( 'includes/base/base-data.php' );
			}

			require $this->component_path( 'data.php' );

			$this->data = new Jet_Engine_CPT_Tax_Data( $this );

		}

		/**
		 * Initiizlize post type specific API endpoints
		 *
		 * @param  Jet_Engine_REST_API $api_manager API manager instance.
		 * @return void
		 */
		public function init_rest( $api_manager ) {

			require_once $this->component_path( 'rest-api/add-taxonomy.php' );
			require_once $this->component_path( 'rest-api/edit-taxonomy.php' );
			require_once $this->component_path( 'rest-api/get-taxonomy.php' );
			require_once $this->component_path( 'rest-api/delete-taxonomy.php' );
			require_once $this->component_path( 'rest-api/get-taxonomies.php' );

			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Add_Taxonomy() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Edit_Taxonomy() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Get_Taxonomy() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Delete_Taxonomy() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Get_Taxonomies() );

		}

		/**
		 * Return path to file inside component
		 *
		 * @param  string $path_inside_component
		 * @return string
		 */
		public function component_path( $path_inside_component ) {
			return jet_engine()->plugin_path( 'includes/components/taxonomies/' . $path_inside_component );
		}

		/**
		 * Return url to file inside component
		 *
		 * @param  string $path_inside_component
		 * @return string
		 */
		public function component_url( $path_inside_component ) {
			return jet_engine()->plugin_url( 'includes/components/taxonomies/' . $path_inside_component );
		}

		/**
		 * Register created post types
		 *
		 * @return void
		 */
		public function register_instances() {

			$capabilities = array();

			foreach ( $this->get_items() as $tax ) {

				if ( empty( $tax['object_type'] ) ) {
					continue;
				}

				if ( ! empty( $tax['meta_fields'] ) ) {

					$this->meta_boxes[ $tax['slug'] ] = $tax['meta_fields'];

					if ( jet_engine()->meta_boxes ) {
						jet_engine()->meta_boxes->store_fields( $tax['slug'], $tax['meta_fields'], 'taxonomy' );
					}

					unset( $tax['meta_fields'] );
				}

				if ( ! empty( $tax['capability_type'] ) ) {
					$capability_type = $tax['capability_type'];
					unset( $tax['capability_type'] );

					$tax['capabilities'] = array(
						'manage_terms' => 'manage_' . $capability_type,
						'edit_terms'   => 'manage_' . $capability_type,
						'delete_terms' => 'manage_' . $capability_type,
						'assign_terms' => 'manage_' . $capability_type,
					);

					$capabilities[] = 'manage_' . $capability_type;

				}

				if ( ! empty( $tax['show_edit_link'] ) ) {

					$this->edit_links[ $tax['slug'] ] = add_query_arg(
						array(
							'page' => 'jet-engine-cpt-tax',
							'cpt_tax_action' => 'edit',
							'id' => $tax['id'],
						),
						admin_url( 'admin.php' )
					);

					unset( $tax['show_edit_link'] );

				}

				register_taxonomy( $tax['slug'], $tax['object_type'], $tax );

			}

		}

		public function init_edit_links( $current_screen ) {

			if ( ! $current_screen->taxonomy ) {
				return;
			}

			$edit_link = isset( $this->edit_links[ $current_screen->taxonomy ] ) ? $this->edit_links[ $current_screen->taxonomy ] : false;

			if ( ! $edit_link ) {
				return;
			}

			$current_screen->add_help_tab( array(
				'title'   => __( 'JetEngine Taxonomy', 'jet-engine' ),
				'id'      => 'jet-engine-tax',
				'content' => sprintf(
					'<br><a href="%1$s" target="_blank">%2$s</a>',
					$edit_link,
					__( 'Edit taxonomy settings', 'jet-engine' )
				),
			) );

		}

		/**
		 * Returns metafields for post type
		 *
		 * @param  [type] $post_type [description]
		 * @return [type]            [description]
		 */
		public function get_meta_fields_for_object( $object ) {

			if ( isset( $this->meta_fields[ $object ] ) ) {
				return $this->meta_fields[ $object ];
			}

			$meta_fields = array();

			if ( ! empty( $this->meta_boxes[ $object ] ) ) {
				$meta_fields = $this->meta_boxes[ $object ];
			}

			foreach ( $meta_fields as $i => $field ) {
				$meta_fields[ $i ]['title'] = isset( $meta_fields[ $i ]['title'] ) ? $meta_fields[ $i ]['title'] : $meta_fields[ $i ]['label'];
			}

			$this->meta_fields[ $object ] = apply_filters(
				'jet-engine/' . $this->object_type . '/' . $object . '/meta-fields',
				$meta_fields
			);

			return $this->meta_fields[ $object ];

		}

		/**
		 * Register metaboxes
		 *
		 * @return void
		 */
		public function register_meta_boxes() {

			if ( empty( $this->meta_boxes ) ) {
				return;
			}

			if ( ! class_exists( 'Jet_Engine_CPT_Tax_Meta' ) ) {
				require jet_engine()->plugin_path( 'includes/components/meta-boxes/tax.php' );
			}

			foreach ( $this->meta_boxes as $post_type => $meta_box ) {
				$this->find_meta_fields_with_save_custom( $post_type, $meta_box );
				$meta_instance = new Jet_Engine_CPT_Tax_Meta( $post_type, $meta_box );

				if ( ! empty( $this->edit_links[ $post_type ] ) ) {
					$meta_instance->add_edit_link( $this->edit_links[ $post_type ] );
				}
			}

			$this->add_hooks_to_save_custom_values();

		}

		/**
		 * Find meta fields with enabling `save custom` option
		 *
		 * @param $tax
		 * @param $fields
		 */
		public function find_meta_fields_with_save_custom( $tax, $fields ) {

			foreach ( $fields as $field ) {

				$object_type = ! empty( $field['object_type'] ) ? $field['object_type'] : 'field';

				if ( 'field' !== $object_type || ! in_array( $field['type'], array( 'checkbox', 'radio' ) ) ) {
					continue;
				}

				$allow_custom = ! empty( $field['allow_custom'] ) && filter_var( $field['allow_custom'], FILTER_VALIDATE_BOOLEAN );
				$save_custom  = ! empty( $field['save_custom'] ) && filter_var( $field['save_custom'], FILTER_VALIDATE_BOOLEAN );

				if ( ! $allow_custom || ! $save_custom ) {
					continue;
				}

				if ( empty( $this->meta_fields_save_custom[ $tax ] ) ) {
					$this->meta_fields_save_custom[ $tax ] = array();
				}

				$this->meta_fields_save_custom[ $tax ][ $field['name'] ] = $field;
			}

		}

		/**
		 * Add hooks to save custom values
		 */
		public function add_hooks_to_save_custom_values() {

			if ( empty( $this->meta_fields_save_custom ) ) {
				return;
			}

			foreach ( $this->meta_fields_save_custom as $tax => $fields ) {
				add_action( "created_{$tax}", array( $this, 'save_custom_values' ) );
				add_action( "edited_{$tax}",  array( $this, 'save_custom_values' ) );
			}
		}

		/**
		 * Save custom values
		 *
		 * @param $id Term ID
		 */
		public function save_custom_values( $id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( empty( $_POST ) ) {
				return;
			}

			if ( false !== strpos( current_action(), 'created_' ) ) {
				if ( ! isset( $_POST['_wpnonce_add-tag'] ) ) {
					return;
				}
			} else {
				if ( ! isset( $_POST['_wpnonce'] ) ) {
					return;
				}
			}

			if ( ! current_user_can( 'edit_term', $id ) ) {
				return;
			}

			$tax = $_POST['taxonomy'];

			$item = $this->data->db->query(
				$this->data->table,
				array(
					'slug' => $tax,
				)
			);

			if ( empty( $item ) ) {
				return;
			}

			$item        = $item[0];
			$meta_fields = maybe_unserialize( $item['meta_fields'] );
			$update_meta = false;

			foreach ( $this->meta_fields_save_custom[ $tax ] as $field => $field_args ) {
				if ( ! isset( $_POST[ $field ] ) || '' === $_POST[ $field ] ) {
					continue;
				}

				do_action( 'jet-engine/meta-boxes/save-custom-value', $field, $field_args );

				$_meta_fields = jet_engine()->meta_boxes->maybe_add_custom_values_to_options( $meta_fields, $field, $field_args );

				if ( $_meta_fields ) {
					$meta_fields = $_meta_fields;
					$update_meta = true;
				}
			}

			if ( $update_meta ) {
				$item['meta_fields'] = maybe_serialize( $meta_fields );
				$this->data->update_item_in_db( $item );
			}
		}

		/**
		 * Return admin pages for current instance
		 *
		 * @return array
		 */
		public function get_instance_pages() {

			$base_path = $this->component_path( 'pages/' );

			return array(
				'Jet_Engine_CPT_Tax_Page_List' => $base_path . 'list.php',
				'Jet_Engine_CPT_Tax_Page_Edit' => $base_path . 'edit.php',
			);
		}

		/**
		 * Returns available labels list
		 *
		 * @return [type] [description]
		 */
		public function get_labels_list() {
			return array(
				array(
					'name'        => 'singular_name',
					'label'       => __( 'Singular name', 'jet-engine' ),
					'description' => __( 'Name for one object of this taxonomy', 'jet-engine' ),
					'is_singular' => true,
					'default'     => '',
				),
				array(
					'name'        => 'menu_name',
					'label'       => __( 'Menu name text', 'jet-engine' ),
					'description' => __( 'This string is the name to give menu items. If not set, defaults to value of name field', 'jet-engine' ),
					'is_singular' => true,
					'default'     => '%s%',
				),
				array(
					'name'        => 'all_items',
					'label'       => __( 'All items text', 'jet-engine' ),
					'description' => __( 'Default is All Tags or All Categories', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'All %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'edit_item',
					'label'       => __( 'Edit item text', 'jet-engine' ),
					'description' => __( 'Default is Edit Tag or Edit Category', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'Edit %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'view_item',
					'label'       => __( 'View Item', 'jet-engine' ),
					'description' => __( 'Default is View Tag or View Category', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'View %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'update_item',
					'label'       => __( 'Update item text', 'jet-engine' ),
					'description' => __( 'Default is Update Tag or Update Category', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'Update %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'add_new_item',
					'label'       => __( 'Add new item text', 'jet-engine' ),
					'description' => __( 'Default is Add New Tag or Add New Category', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'Add New %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'new_item_name',
					'label'       => __( 'New item name text', 'jet-engine' ),
					'description' => __( 'Default is New Tag Name or New Category Name', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'New %s% Name', 'jet-engine' ),
				),
				array(
					'name'        => 'parent_item',
					'label'       => __( 'Parent item text', 'jet-engine' ),
					'description' => __( 'This string is not used on non-hierarchical taxonomies such as post tags', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'Parent %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'parent_item_colon',
					'label'       => __( 'Parent item with colon', 'jet-engine' ),
					'description' => __( 'The same as parent_item, but with colon : in the end null', 'jet-engine' ),
					'is_singular' => true,
					'default'     => __( 'Parent %s%:', 'jet-engine' ),
				),
				array(
					'name'        => 'search_items',
					'label'       => __( 'Search items text', 'jet-engine' ),
					'description' => __( 'Default is Search Tags or Search Categories', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'Search %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'popular_items',
					'label'       => __( 'Popular items text', 'jet-engine' ),
					'description' => __( 'This string is not used on hierarchical taxonomies', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'Popular %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'separate_items_with_commas',
					'label'       => __( 'Separate item with commas text', 'jet-engine' ),
					'description' => __( 'Used in the taxonomy meta box. This string is not used on hierarchical taxonomies', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'Separate %s% with commas', 'jet-engine' ),
				),
				array(
					'name'        => 'add_or_remove_items',
					'label'       => __( 'Add or remove items text', 'jet-engine' ),
					'description' => __( 'Used in the taxonomy meta box when JavaScript is disabled. This string is not used on hierarchical taxonomies', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'Add or remove %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'choose_from_most_used',
					'label'       => __( 'Choose from most used text', 'jet-engine' ),
					'description' => __( 'Used in the taxonomy meta box. This string is not used on hierarchical taxonomies', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'Choose from the most used %s%', 'jet-engine' ),
				),
				array(
					'name'        => 'not_found',
					'label'       => __( 'Items not found text', 'jet-engine' ),
					'description' => __( 'The text displayed via clicking "Choose from the most used tags" in the taxonomy meta box when no tags are available and the text used in the terms list table when there are no items for a taxonomy', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( 'No %s% found', 'Default value for use_featured_image label', 'jet-engine' ),
				),
				array(
					'name'        => 'back_to_items',
					'label'       => __( 'Back to items', 'jet-engine' ),
					'description' => __( 'The text displayed after a term has been updated for a link back to main index', 'jet-engine' ),
					'is_singular' => false,
					'default'     => __( '← Back to %s%', 'jet-engine' ),
				),

			);
		}

		/**
		 * Is meta fields functionality enabled
		 *
		 * @return boolean [description]
		 */
		public function is_meta_fields_enabled() {
			if ( jet_engine()->components->is_component_active( 'meta_boxes' ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Returns current menu page title (for JetEngine submenu)
		 * @return [type] [description]
		 */
		public function get_page_title() {
			return __( 'Taxonomies', 'jet-engine' );
		}

		/**
		 * Returns current instance slug
		 *
		 * @return [type] [description]
		 */
		public function instance_slug() {
			return 'taxonomies';
		}

		/**
		 * Returns default config for add/edit page
		 *
		 * @param  array  $config [description]
		 * @return [type]         [description]
		 */
		public function get_admin_page_config( $config = array() ) {

			$default_settings = array(
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_in_rest'       => true,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_admin_column'  => true,
				'description'        => '',
				'query_var'          => '',
				'rewrite'            => true,
			);

			$default = array(
				'api_path_edit'       => '', // Should be set for apropriate page context
				'api_path_get'        => jet_engine()->api->get_route( 'get-taxonomy' ),
				'edit_button_label'   => '', // Should be set for apropriate page context
				'item_id'             => false,
				'redirect'            => '', // Should be set for apropriate page context
				'general_settings'    => array( 'name' => '' ),
				'labels'              => array( 'singular_name' => '' ),
				'advanced_settings'   => $default_settings,
				'post_types'          => Jet_Engine_Tools::get_post_types_for_js(),
				'meta_fields'         => array(),
				'labels_list'         => $this->get_labels_list(),
				'meta_fields_enabled' => $this->is_meta_fields_enabled(),
				'notices'             => array(
					'name'      => __( 'Please, set taxonomy name', 'jet-engine' ),
					'slug'      => __( 'Please, set taxonomy slug', 'jet-engine' ),
					'post_type' => __( 'Please, select post type for this taxonomy', 'jet-engine' ),
					'success'   => __( 'Taxonomy updated', 'jet-engine' ),
				),
			);

			return array_merge( $default, $config );

		}

	}

}
