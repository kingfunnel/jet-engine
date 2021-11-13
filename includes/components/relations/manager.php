<?php
/**
 * Relations manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Relations' ) ) {

	/**
	 * Define Jet_Engine_Relations class
	 */
	class Jet_Engine_Relations extends Jet_Engine_Base_WP_Intance {

		/**
		 * Base slug for CPT-related pages
		 * @var string
		 */
		public $page = 'jet-engine-relations';

		/**
		 * Action request key
		 *
		 * @var string
		 */
		public $action_key = 'cpt_relation_action';

		/**
		 * Set object type
		 * @var string
		 */
		public $object_type = '';

		public $hierarchy = null;

		private $_active_relations         = array();
		private $_relations_for_post_types = array();

		/**
		 * Constructor for the class
		 */
		function __construct() {

			add_action( 'init', array( $this, 'register_instances' ), 11 );

			$this->init_data();

			add_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'init_rest' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'relations_box_assets' ), 10 );

			require_once $this->component_path( 'hierarchy.php' );
			$this->hierarchy = new Jet_Engine_Relations_Hierarchy();

			$this->init_admin_pages();

			// Clear relations meta on delete a relation post.
			add_action( 'delete_post', array( $this, 'clear_relations_meta_on_delete_post' ) );

		}

		/**
		 * Enqueue relations assets to posts edit screen
		 *
		 * @param  [type] $hook [description]
		 * @return [type]       [description]
		 */
		public function relations_box_assets( $hook ) {

			if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
				return;
			}

			wp_enqueue_style(
				'jet-engine-relations',
				jet_engine()->plugin_url( 'assets/css/admin/relations.css' ),
				array(),
				jet_engine()->get_version()
			);

		}

		/**
		 * Initiizlize post type specific API endpoints
		 *
		 * @param  Jet_Engine_REST_API $api_manager API manager instance.
		 * @return void
		 */
		public function init_rest( $api_manager ) {

			require_once $this->component_path( 'rest-api/add-relation.php' );
			require_once $this->component_path( 'rest-api/edit-relation.php' );
			require_once $this->component_path( 'rest-api/get-relation.php' );
			require_once $this->component_path( 'rest-api/delete-relation.php' );
			require_once $this->component_path( 'rest-api/get-relations.php' );

			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Add_Relation() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Edit_Relation() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Get_Relation() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Delete_Relation() );
			$api_manager->register_endpoint( new Jet_Engine_CPT_Rest_Get_Relations() );

		}

		/**
		 * Return allowed relations types
		 *
		 * @return array
		 */
		public function get_relations_types() {

			return array(
				'one_to_one'   => __( 'One to one', 'jet-engine' ),
				'one_to_many'  => __( 'One to many', 'jet-engine' ),
				'many_to_many' => __( 'Many to many', 'jet-engine' ),
			);

		}

		/**
		 * Returns allowed relations types prepared to use in JS formst
		 *
		 * @return [type] [description]
		 */
		public function get_relations_types_for_js() {

			$result = array();

			foreach ( $this->get_relations_types() as $value => $label ) {
				$result[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $result;
		}

		/**
		 * Get relations for JS
		 * @return [type] [description]
		 */
		public function get_relations_for_js() {

			$relations = $this->get_active_relations();
			$result    = array();

			if ( ! empty( $relations ) ) {
				foreach ( $relations as $key => $relation ) {
					$result[] = array(
						'value' => $key,
						'label' => $relation['name'],
					);
				}
			}

			return $result;

		}

		/**
		 * Return path to file inside component
		 *
		 * @param  [type] $path_inside_component [description]
		 * @return [type]                        [description]
		 */
		public function component_path( $path_inside_component ) {
			return jet_engine()->plugin_path( 'includes/components/relations/' . $path_inside_component );
		}

		/**
		 * Init data instance
		 *
		 * @return [type] [description]
		 */
		public function init_data() {
			require $this->component_path( 'data.php' );
			$this->data = new Jet_Engine_Relations_Data( $this );
		}

		/**
		 * Returns unique relation name for post types pair
		 *
		 * @param  [type] $post_type_1 [description]
		 * @param  [type] $post_type_2 [description]
		 * @return [type]              [description]
		 */
		public function get_relation_hash( $post_type_1, $post_type_2 ) {
			$hash = md5( $post_type_1 . $post_type_2 );
			return 'relation_' . $hash;
		}

		/**
		 * Register relations meta boxes
		 *
		 * @return void
		 */
		public function register_instances() {

			$relations     = $this->data->get_raw();
			$has_hierarchy = false;

			$relations = apply_filters( 'jet-engine/relations/registered-relation', $relations );

			if ( empty( $relations ) ) {
				return;
			}

			foreach ( $relations as $relation ) {

				$post_type_1 = $relation['post_type_1'];
				$post_type_2 = $relation['post_type_2'];
				$type        = explode( '_to_', $relation['type'] );
				$type_1      = $type[0];
				$type_2      = $type[1];
				$meta_key    = $this->get_relation_hash( $post_type_1, $post_type_2 );
				$control_1   = isset( $relation['post_type_1_control'] ) ? $relation['post_type_1_control'] : 'true';
				$control_2   = isset( $relation['post_type_2_control'] ) ? $relation['post_type_2_control'] : 'true';
				$control_1   = filter_var( $control_1, FILTER_VALIDATE_BOOLEAN );
				$control_2   = filter_var( $control_2, FILTER_VALIDATE_BOOLEAN );

				// Allow single relation for post type 1 - post type 2 pair
				if ( ! empty( $this->_active_relations[ $meta_key ] ) ) {
					continue;
				}

				if ( ! class_exists( 'Jet_Engine_CPT_Meta' ) ) {
					require_once jet_engine()->plugin_path( 'includes/components/meta-boxes/post.php' );
				}

				$obj_1 = get_post_type_object( $post_type_1 );
				$obj_2 = get_post_type_object( $post_type_2 );

				if ( ! $obj_1 || ! $obj_2 ) {
					continue;
				}

				if ( is_admin() ) {

					$title_1 = sprintf( __( 'Related %s', 'jet-engine' ), $obj_2->labels->name );
					$title_2 = sprintf( __( 'Related %s', 'jet-engine' ), $obj_1->labels->name );

					if ( $control_1 ) {

						if ( 'one' === $type_1 && 'many' === $type_2 ) {
							$multiple_1 = 'true';
						} elseif ( 'many' === $type_1 ) {
							$multiple_1 = 'true';
						} else {
							$multiple_1 = 'false';
						}

						$meta_field_1 = array(
							'name'             => $meta_key,
							'type'             => 'posts',
							'element'          => 'control',
							'title'            => sprintf( __( 'Select %s', 'jet-engine' ), $obj_2->labels->name ),
							'is_multiple'      => $multiple_1,
							'search_post_type' => $post_type_2,
							'description'      => sprintf( __( 'Set Child %s', 'jet-engine' ), $obj_2->labels->name ),
						);

						new Jet_Engine_CPT_Meta( $post_type_1, array( $meta_field_1 ), $title_1, 'side', 'default' );

						if ( jet_engine()->meta_boxes ) {
							$meta_field_1['title'] = $title_1;
							jet_engine()->meta_boxes->store_fields( $post_type_1, array( $meta_field_1 ), 'post_type' );
						}

					}

					if ( $control_2 ) {

						if ( 'one' === $type_1 && 'many' === $type_2 ) {
							$multiple_2 = 'false';
						} elseif ( 'many' === $type_2 ) {
							$multiple_2 = 'true';
						} else {
							$multiple_2 = 'false';
						}

						$meta_field_2 = array(
							'name'             => $meta_key,
							'type'             => 'posts',
							'element'          => 'control',
							'title'            => sprintf( __( 'Select %s', 'jet-engine' ), $obj_1->labels->name ),
							'is_multiple'      => $multiple_2,
							'search_post_type' => $post_type_1,
							'description'      => sprintf( __( 'Set Parent %s', 'jet-engine' ), $obj_1->labels->name ),
						);

						new Jet_Engine_CPT_Meta( $post_type_2, array( $meta_field_2 ), $title_2, 'side', 'default' );

						if ( jet_engine()->meta_boxes ) {
							$meta_field_2['title'] = $title_2;
							jet_engine()->meta_boxes->store_fields( $post_type_2, array( $meta_field_2 ), 'post_type' );
						}

					}

				}

				$relation['label_1'] = $obj_1->labels->name;
				$relation['label_2'] = $obj_2->labels->name;

				$this->add_relation_to_post_types( $meta_key, $relation );

				$this->_active_relations[ $meta_key ] = $relation;

				if ( ! empty( $relation['parent_relation'] ) ) {
					$has_hierarchy = true;
				}

				add_filter( 'cx_post_meta/pre_process_key/' . $meta_key, array( $this, 'process_meta' ), 10, 3 );
				add_filter( 'cx_post_meta/pre_get_meta/' . $meta_key, array( $this, 'get_meta' ), 10, 5 );

			}

			if ( $has_hierarchy ) {
				$this->hierarchy->create_hierarchy( $this->_active_relations );
			}

		}

		/**
		 * Store relation meta keys for post type
		 *
		 * @param string $meta_key [description]
		 * @param array  $relation [description]
		 */
		public function add_relation_to_post_types( $meta_key = null, $relation = array() ) {

			$post_type_1 = $relation['post_type_1'];
			$label_1     = $relation['label_1'];
			$post_type_2 = $relation['post_type_2'];
			$label_2     = $relation['label_2'];

			if ( ! isset( $this->_relations_for_post_types[ $post_type_1 ] ) ) {
				$this->_relations_for_post_types[ $post_type_1 ] = array();
			}

			if ( ! isset( $this->_relations_for_post_types[ $post_type_2 ] ) ) {
				$this->_relations_for_post_types[ $post_type_2 ] = array();
			}

			$this->_relations_for_post_types[ $post_type_1 ][ $meta_key ] = array(
				'title' => sprintf( __( 'Child %s', 'jet-engine' ), $label_2 ),
				'type'  => 'select',
				'name'  => $meta_key,
			);

			$this->_relations_for_post_types[ $post_type_2 ][ $meta_key ] = array(
				'title' => sprintf( __( 'Parent %s', 'jet-engine' ), $label_1 ),
				'type'  => 'select',
				'name'  => $meta_key,
			);

		}

		/**
		 * Get values for relations meta fields early
		 *
		 * @param  [type] $result  [description]
		 * @param  [type] $post    [description]
		 * @param  [type] $key     [description]
		 * @param  [type] $default [description]
		 * @param  [type] $field   [description]
		 * @return [type]          [description]
		 */
		public function get_meta( $result, $post, $key, $default, $field ) {

			$relation = isset( $this->_active_relations[ $key ] ) ? $this->_active_relations[ $key ] : false;

			if ( ! $relation ) {
				return $result;
			}

			$meta = get_post_meta( $post->ID, $key, false );
			$meta = array_filter( $meta );
			$meta = array_values( $meta );

			if ( ! isset( $field['multiple'] ) || ! $field['multiple'] ) {
				$meta = isset( $meta[0] ) ? $meta[0] : null;
			}

			if ( ! empty( $meta ) ) {
				return $meta;
			} else {
				return null;
			}

		}

		/**
		 * Check if is relation meta key
		 *
		 * @param  [type]  $meta_key [description]
		 * @return boolean           [description]
		 */
		public function is_relation_key( $meta_key = null ) {
			return isset( $this->_active_relations[ $meta_key ] );
		}

		/**
		 * Returns active relations list
		 *
		 * @return [type] [description]
		 */
		public function get_active_relations() {
			return $this->_active_relations;
		}

		/**
		 * Synchronize realted meta on post save
		 *
		 * @return void
		 */
		public function process_meta( $result = null, $post_id = null, $meta_key = '', $related_posts = array() ) {

			$relation = isset( $this->_active_relations[ $meta_key ] ) ? $this->_active_relations[ $meta_key ] : false;

			if ( ! $relation ) {
				return $result;
			}

			if ( empty( $related_posts ) ) {
				$related_posts = ! empty( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : false;
			}

			if ( empty( $related_posts ) ) {
				$this->data->delete_all_related_meta( $meta_key, $post_id );
			}

			if ( ! is_array( $related_posts ) ) {
				$related_posts = array( $related_posts );
			}

			$prev_related    = get_post_meta( $post_id, $meta_key );
			$saved_post_type = get_post_type( $post_id );
			$type            = explode( '_to_', $relation['type'] );

			if ( $relation['post_type_1'] === $saved_post_type ) {
				$current_type = $type[0];
				$related_type = $type[1];
			} elseif ( $relation['post_type_2'] === $saved_post_type ) {
				$current_type = $type[1];
				$related_type = $type[0];
			} else {
				return $result;
			}

			$result    = true;
			$to_delete = array_diff( $prev_related, $related_posts );
			$to_delete = array_filter( $to_delete );

			if ( ! empty( $to_delete ) ) {
				foreach ( $to_delete as $delete_post_id ) {
					delete_post_meta( $delete_post_id, $meta_key, $post_id );
					delete_post_meta( $post_id, $meta_key, $delete_post_id );
				}
			}

			foreach ( $related_posts as $related_post_id ) {

				if ( $related_post_id && ! in_array( $related_post_id, $prev_related ) ) {
					add_post_meta( $post_id, $meta_key, $related_post_id, false );
				}

				$stored = get_post_meta( $related_post_id, $meta_key, false );

				if ( ! is_array( $stored ) ) {
					$stored = array( $stored );
					$stored = array_filter( $stored );
				}

				if ( 'one' === $current_type ) {
					foreach ( $stored as $stored_id ) {
						if ( absint( $stored_id ) !== $post_id ) {
							delete_post_meta( $related_post_id, $meta_key );
							delete_post_meta( $stored_id, $meta_key, $related_post_id );
						}
					}
				}

				if ( $post_id && ! in_array( $post_id, $stored ) ) {
					add_post_meta( $related_post_id, $meta_key, $post_id, false );
				}

			}

			return $result;

		}

		/**
		 * Returns relation meta keys for passed post type
		 *
		 * @param  string $post_type Post type name
		 * @return array
		 */
		public function get_relation_fields_for_post_type( $post_type = null ) {

			$default = array();

			if ( ! $post_type ) {
				return $default;
			}

			if ( empty( $this->_relations_for_post_types ) ) {
				return $default;
			}

			if ( empty( $this->_relations_for_post_types[ $post_type ] ) ) {
				return $default;
			}

			return $this->_relations_for_post_types[ $post_type ];

		}

		/**
		 * Returns info about relationby hash
		 */
		public function get_relation_info( $key ) {
			return isset( $this->_active_relations[ $key ] ) ? $this->_active_relations[ $key ] : false;
		}

		/**
		 * Returns related posts
		 *
		 * @return void
		 */
		public function get_related_posts( $args = array() ) {

			$post_type_1 = isset( $args['post_type_1'] ) ? $args['post_type_1'] : false;
			$post_type_2 = isset( $args['post_type_2'] ) ? $args['post_type_2'] : false;
			$hash        = isset( $args['hash'] ) ? $args['hash'] : false;

			if ( $hash ) {
				$meta_key = $hash;
				$relation = isset( $this->_active_relations[ $meta_key ] ) ? $this->_active_relations[ $meta_key ] : false;

				if ( ! $relation ) {
					return false;
				}

				$post_type_1 = $relation['post_type_1'];
				$post_type_2 = $relation['post_type_2'];

				$current = ! empty( $args['current'] ) ? $args['current'] : false;

				if ( $current ) {
					$args['from'] = ( $post_type_1 === $current ) ? $post_type_2 : $post_type_1;
				}

			} else {

				if ( ! $post_type_1 || ! $post_type_2 ) {
					return false;
				}

				$meta_key = $this->get_relation_hash( $post_type_1, $post_type_2 );
				$relation = isset( $this->_active_relations[ $meta_key ] ) ? $this->_active_relations[ $meta_key ] : false;
			}

			if ( ! $relation ) {
				return false;
			}

			$post_id = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();
			$type    = explode( '_to_', $relation['type'] );
			$from    = isset( $args['from'] ) ? $args['from'] : $post_type_1;

			if ( $post_type_1 === $from ) {
				$type = $type[0];
			} else {
				$type = $type[1];
			}

			if ( 'one' === $type ) {
				$single = true;
			} else {
				$single = false;
			}

			$meta = get_post_meta( $post_id, $meta_key, $single );
			$meta = apply_filters( 'jet-engine/relations/get_related_posts', $meta );

			return $meta;

		}

		/**
		 * Return admin pages for current instance
		 *
		 * @return array
		 */
		public function get_instance_pages() {

			$base_path = $this->component_path( 'pages/' );

			return array(
				'Jet_Engine_Relations_Page_List' => $base_path . 'list.php',
				'Jet_Engine_Relations_Page_Edit' => $base_path . 'edit.php',
			);
		}

		/**
		 * Returns current menu page title (for JetEngine submenu)
		 * @return [type] [description]
		 */
		public function get_page_title() {
			return __( 'Relations', 'jet-engine' );
		}

		/**
		 * Returns current instance slug
		 *
		 * @return [type] [description]
		 */
		public function instance_slug() {
			return 'relations';
		}

		/**
		 * Returns default config for add/edit page
		 *
		 * @param  array  $config [description]
		 * @return [type]         [description]
		 */
		public function get_admin_page_config( $config = array() ) {

			$default = array(
				'api_path_edit'       => '', // Should be set for apropriate page context
				'api_path_get'        => jet_engine()->api->get_route( 'get-relation' ),
				'edit_button_label'   => '', // Should be set for apropriate page context
				'item_id'             => false,
				'redirect'            => '', // Should be set for apropriate page context
				'general_settings'    => array(
					'name'        => '',
					'post_type_1' => 'post',
					'post_type_2' => 'page',
					'type'        => 'one_to_one',
				),
				'advanced_settings'   => array(
					'post_type_1_control' => true,
					'post_type_2_control' => true,
				),
				'post_types'          => Jet_Engine_Tools::get_post_types_for_js(),
				'relations_types'     => $this->get_relations_types_for_js(),
				'notices'             => array(
					'success' => __( 'Relationship updated', 'jet-engine' ),
				),
			);

			return array_merge( $default, $config );

		}

		/**
		 * Clear relations meta on delete a relation post.
		 *
		 * @param $post_id
		 */
		public function clear_relations_meta_on_delete_post( $post_id ) {

			$post_type       = get_post_type( $post_id );
			$relation_fields = jet_engine()->relations->get_relation_fields_for_post_type( $post_type );

			if ( ! empty( $relation_fields ) ) {
				foreach ( $relation_fields as $field => $args ) {
					jet_engine()->relations->data->delete_all_related_meta( $field, $post_id );
				}
			}
		}

	}

}
