<?php
/**
 * Relations data controller class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Relations_Data' ) ) {

	/**
	 * Define Jet_Engine_Relations_Data class
	 */
	class Jet_Engine_Relations_Data extends Jet_Engine_Base_Data {

		/**
		 * Edit slug
		 *
		 * @var string
		 */
		public $edit        = 'edit-relation';
		public $option_name = 'jet_engine_relations';

		/**
		 * Modify create item function
		 *
		 * @return [type] [description]
		 */
		public function create_item( $redirect = true ) {

			if ( empty( $this->request['post_type_1'] ) || empty( $this->request['post_type_2'] ) ) {
				$this->parent->add_notice(
					'error',
					__( 'Please set both post types', 'jet-engine' )
				);
				return;
			}

			if ( $this->request['post_type_1'] === $this->request['post_type_2'] ) {
				$this->parent->add_notice(
					'error',
					__( 'Parent and child post type can\'t be the same', 'jet-engine' )
				);
				return;
			}

			$this->request['slug'] = true;

			if ( empty( $this->request['name'] ) ) {
				$this->request['name'] = $this->request['post_type_1'] . ' to ' . $this->request['post_type_2'];
			}

			return parent::create_item( $redirect );

		}

		/**
		 * Modify create item function
		 *
		 * @return [type] [description]
		 */
		public function edit_item( $redirect = true ) {

			if ( empty( $this->request['post_type_1'] ) || empty( $this->request['post_type_2'] ) ) {
				$this->parent->add_notice(
					'error',
					__( 'Please set both post types', 'jet-engine' )
				);
				return;
			}

			if ( $this->request['post_type_1'] === $this->request['post_type_2'] ) {
				$this->parent->add_notice(
					'error',
					__( 'Parent and child post type can\'t be the same', 'jet-engine' )
				);
				return;
			}

			$this->request['slug'] = true;

			if ( empty( $this->request['name'] ) ) {
				$this->request['name'] = $this->request['post_type_1'] . ' to ' . $this->request['post_type_2'];
			}

			return parent::edit_item( $redirect );

		}

		/**
		 * Update item in DB
		 *
		 * @param  [type] $item [description]
		 * @return [type]       [description]
		 */
		public function update_item_in_db( $item ) {

			$raw        = $this->get_raw();
			$id         = isset( $item['id'] ) ? $item['id'] : 'item-' . $this->get_numeric_id();
			$item['id'] = $id;
			$raw[ $id ] = $item;

			update_option( $this->option_name, $raw );

			return $id;

		}

		/**
		 * Returns actual numeric ID
		 * @return [type] [description]
		 */
		public function get_numeric_id() {

			$raw  = $this->get_raw();
			$keys = array_keys( $raw );
			$last = end( $keys );

			if ( ! $last ) {
				return 1;
			}

			$num = absint( str_replace( 'item-', '', $last ) );

			return $num + 1;

		}

		/**
		 * Prepare post data from request to write into database
		 *
		 * @return array
		 */
		public function sanitize_item_from_request() {

			$request = $this->request;
			$args    = array(
				'name',
				'post_type_1',
				'post_type_2',
				'type',
				'post_type_1_control',
				'post_type_2_control',
				'parent_relation',
			);

			$result = array();

			foreach ( $args as $key ) {
				if ( in_array( $key, array( 'name' ) ) ) {
					$result[ $key ] = sanitize_text_field( $request[ $key ] );
				} else {
					$result[ $key ] = isset( $request[ $key ] ) ? esc_attr( $request[ $key ] ) : '';
				}
			}

			return $result;

		}

		/**
		 * Find related posts for ppassed relation key and current post ID pair
		 *
		 * @param  [type] $meta_key [description]
		 * @param  [type] $post_id  [description]
		 * @return [type]           [description]
		 */
		public function find_related_posts( $meta_key, $post_id ) {

			global $wpdb;

			$related = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` = '%s' AND `meta_value` = %d;",
					$meta_key,
					$post_id
				)
			);

			return $related;

		}

		/**
		 * Update post post type
		 *
		 * @return void
		 */
		public function delete_item( $redirect = true ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				$this->parent->add_notice(
					'error',
					__( 'You don\'t have permissions to do this', 'jet-engine' )
				);
				return;
			}

			$id = isset( $this->request['id'] ) ? esc_attr( $this->request['id'] ) : false;

			if ( ! $id ) {
				$this->parent->add_notice(
					'error',
					__( 'Please provide item ID to delete', 'jet-engine' )
				);
				return;
			}

			$raw = $this->get_raw();

			if ( isset( $raw[ $id ] ) ) {
				unset( $raw[ $id ] );
				update_option( $this->option_name, $raw );
			}

			if ( $redirect ) {
				wp_redirect( $this->parent->get_page_link() );
				die();
			} else {
				return true;
			}

		}

		/**
		 * Delete all related meta contains passed $post_id
		 *
		 * @param  [type] $meta_key [description]
		 * @param  [type] $post_id  [description]
		 * @return [type]           [description]
		 */
		public function delete_all_related_meta( $meta_key, $post_id ) {

			delete_post_meta( $post_id, $meta_key );
			$old_related = $this->find_related_posts( $meta_key, $post_id );

			if ( ! empty( $old_related ) ) {

				foreach ( $old_related as $related_post_id ) {
					delete_post_meta( $related_post_id, $meta_key, $post_id );
				}

			}

		}

		/**
		 * Filter post type for register
		 *
		 * @return array
		 */
		public function filter_item_for_register( $item ) {
			return $item;
		}

		/**
		 * Filter post type for edit
		 *
		 * @return array
		 */
		public function filter_item_for_edit( $item ) {
			return $item;
		}

		/**
		 * Return blacklisted items names
		 *
		 * @return array
		 */
		public function items_blacklist() {
			return array();
		}

		/**
		 * Retrieve post for edit
		 *
		 * @return array
		 */
		public function get_item_for_edit( $id ) {

			$raw  = $this->get_raw();
			$item = isset( $raw[ $id ] ) ? $raw[ $id ] : array();

			if ( empty( $item ) ) {
				return array(
					'general_settings'  => array(),
					'advanced_settings' => array(),
				);
			}

			$result = array(
				'general_settings'  => array(
					'name'            => isset( $item['name'] ) ? $item['name'] : '',
					'post_type_1'     => isset( $item['post_type_1'] ) ? $item['post_type_1'] : '',
					'post_type_2'     => isset( $item['post_type_2'] ) ? $item['post_type_2'] : '',
					'type'            => isset( $item['type'] ) ? $item['type'] : '',
					'parent_relation' => isset( $item['parent_relation'] ) ? $item['parent_relation'] : '',
				),
				'advanced_settings' => array(
					'post_type_1_control' => isset( $item['post_type_1_control'] ) ? filter_var( $item['post_type_1_control'], FILTER_VALIDATE_BOOLEAN ) : true,
					'post_type_2_control' => isset( $item['post_type_2_control'] ) ? filter_var( $item['post_type_2_control'], FILTER_VALIDATE_BOOLEAN ) : true,
				),
			);

			return $result;
		}

		/**
		 * Returns post type in prepared for register format
		 *
		 * @return array
		 */
		public function get_item_for_register() {
			return $this->get_raw();
		}

		/**
		 * Returns items by args without filtering
		 *
		 * @return array
		 */
		public function get_raw( $args = array() ) {

			if ( ! $this->raw ) {
				$this->raw = get_option( $this->option_name, array() );
			}

			return $this->raw;
		}

		/**
		 * Query post types
		 *
		 * @return array
		 */
		public function get_items() {
			return $this->get_raw();
		}

		/**
		 * Stored in wp_options, so always true
		 *
		 * @return [type] [description]
		 */
		public function ensure_db_table() {
			return true;
		}

	}

}
