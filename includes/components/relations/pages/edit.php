<?php
/**
 * Relation edit page
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Relations_Page_Edit' ) ) {

	/**
	 * Define Jet_Engine_Relations_Page_Edit class
	 */
	class Jet_Engine_Relations_Page_Edit extends Jet_Engine_CPT_Page_Base {

		/**
		 * Page slug
		 *
		 * @return string
		 */
		public function get_slug() {
			if ( $this->item_id() ) {
				return 'edit';
			} else {
				return 'add';
			}
		}

		/**
		 * Page name
		 *
		 * @return string
		 */
		public function get_name() {
			if ( $this->item_id() ) {
				return esc_html__( 'Edit Relation', 'jet-engine' );
			} else {
				return esc_html__( 'Add Relation', 'jet-engine' );
			}
		}

		/**
		 * Returns currently requested items ID.
		 * If this funciton returns an empty result - this is add new item page
		 *
		 * @return [type] [description]
		 */
		public function item_id() {
			return isset( $_GET['id'] ) ? esc_attr( $_GET['id'] ) : false;
		}

		/**
		 * Register add controls
		 * @return [type] [description]
		 */
		public function page_specific_assets() {

			$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );

			$ui = new CX_Vue_UI( $module_data );

			$ui->enqueue_assets();

			do_action( 'jet-engine/relations/edit/before-enqueue-assets' );

			wp_register_script(
				'jet-engine-cpt-delete-dialog',
				jet_engine()->plugin_url( 'assets/js/admin/relations/delete-dialog.js' ),
				array( 'cx-vue-ui', 'wp-api-fetch', ),
				jet_engine()->get_version(),
				true
			);

			wp_localize_script(
				'jet-engine-cpt-delete-dialog',
				'JetEngineCPTDeleteDialog',
				array(
					'api_path' => jet_engine()->api->get_route( 'delete-relation' ),
					'redirect' => $this->manager->get_page_link( 'list' ),
				)
			);

			wp_enqueue_script(
				'jet-engine-cpt-edit',
				jet_engine()->plugin_url( 'assets/js/admin/relations/edit.js' ),
				array( 'cx-vue-ui', 'wp-api-fetch', 'jet-engine-cpt-delete-dialog' ),
				jet_engine()->get_version(),
				true
			);

			$id = $this->item_id();

			if ( $id ) {
				$button_label = __( 'Update Relation', 'jet-engine' );
				$redirect     = false;
			} else {
				$button_label = __( 'Add Relation', 'jet-engine' );
				$redirect     = $this->manager->get_edit_item_link( '%id%' );
			}

			wp_localize_script(
				'jet-engine-cpt-edit',
				'JetEngineCPTConfig',
				$this->manager->get_admin_page_config( array(
					'api_path_edit'      => jet_engine()->api->get_route( $this->get_slug() . '-relation' ),
					'item_id'            => $id,
					'edit_button_label'  => $button_label,
					'redirect'           => $redirect,
					'existing_relations' => $this->get_existing_relations( $id ),
					'help_links'         => array(
						array(
							'url'   => 'https://crocoblock.com/knowledge-base/articles/how-to-choose-the-needed-post-relations-and-set-them-with-jetengine-plugin/?utm_source=jetengine&utm_medium=relations-page&utm_campaign=need-help',
							'label' => __( 'How to choose the needed post relations and set them with JetEngine', 'jet-engine' ),
						),
						array(
							'url'   => 'https://crocoblock.com/knowledge-base/articles/how-to-establish-posts-relations-with-jetengine-creating-one-to-one-posts-relation/?utm_source=jetengine&utm_medium=relations-page&utm_campaign=need-help',
							'label' => __( 'How to establish posts relations with JetEngine. Creating “one-to-one” posts relation', 'jet-engine' ),
						),
						array(
							'url'   => 'https://crocoblock.com/knowledge-base/articles/jetengine-post-relations-how-to-display-related-posts-using-dynamic-field-widget/?utm_source=jetengine&utm_medium=relations-page&utm_campaign=need-help',
							'label' => __( 'How to display related posts using Dynamic Field widget', 'jet-engine' ),
						),
						array(
							'url'   => 'https://crocoblock.com/knowledge-base/articles/jetengine-post-relations-how-to-display-the-related-child-posts-in-the-listing-grid/?utm_source=jetengine&utm_medium=relations-page&utm_campaign=need-help',
							'label' => __( 'How to display the related child posts in the Listing Grid', 'jet-engine' ),
						),
						array(
							'url'   => 'https://crocoblock.com/knowledge-base/articles/jetengine-post-relations-how-to-display-the-related-parent-posts-in-the-listing-grid/?utm_source=jetengine&utm_medium=relations-page&utm_campaign=need-help',
							'label' => __( 'How to display the related parent posts in the Listing Grid', 'jet-engine' ),
						),
					),
				) )
			);

			add_action( 'admin_footer', array( $this, 'add_page_template' ) );

		}

		/**
		 * Print add/edit page template
		 */
		public function add_page_template() {

			ob_start();
			include jet_engine()->get_template( 'admin/pages/relations/edit.php' );
			$content = ob_get_clean();
			printf( '<script type="text/x-template" id="jet-cpt-form">%s</script>', $content );

			ob_start();
			include jet_engine()->get_template( 'admin/pages/relations/delete-dialog.php' );
			$content = ob_get_clean();
			printf( '<script type="text/x-template" id="jet-cpt-delete-dialog">%s</script>', $content );

		}

		/**
		 * Returns existing relations list except requested
		 * @return [type] [description]
		 */
		public function get_existing_relations( $id = false ) {

			$result = array();
			$items  = jet_engine()->relations->data->get_items();

			foreach ( $items as $item_id => $item ) {

				if ( $id && $id === $item_id ) {
					continue;
				}

				$pt1 = ! empty( $item['post_type_1'] ) ? $item['post_type_1'] : false;
				$pt2 = ! empty( $item['post_type_2'] ) ? $item['post_type_2'] : false;

				if ( $pt1 && $pt2 ) {
					$result[ jet_engine()->relations->get_relation_hash( $pt1, $pt2 ) ] = $item;
				}

			}

			return $result;

		}

		/**
		 * Renderer callback
		 *
		 * @return void
		 */
		public function render_page() {
			?>
			<br>
			<div id="jet_cpt_form"></div>
			<?php
		}

	}

}
