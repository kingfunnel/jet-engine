<?php
namespace Jet_Engine\Modules\Data_Stores;

class Query {

	public function __construct() {
		add_filter( 'jet-engine/listing/grid/posts-query-args', array( $this, 'add_query_args' ), 10, 3 );
	}

	public function add_query_args( $args, $render, $settings ) {

		if ( jet_engine()->listings->is_listing_ajax() && ! empty( $_REQUEST['query'] ) ) {
			$args = $_REQUEST['query'];
			remove_filter( 'jet-engine/listing/grid/posts-query-args', array( $this, 'add_query_args' ), 10 );

			if ( ! empty( $args['is_front_store'] ) ) {
				add_filter( 'jet-engine/listing/grid/add-query-data', array( $this, 'add_query_data_trigger' ) );
				unset( $args['is_front_store'] );
			}

		} elseif ( ! empty( $settings['posts_query'] ) ) {

			$store = false;

			foreach ( $settings['posts_query'] as $query_item ) {
				if ( ! empty( $query_item['posts_from_data_store'] ) ) {
					$store = $query_item['posts_from_data_store'];
				}
			}

			if ( $store ) {

				$store_instance = Module::instance()->stores->get_store( $store );

				if ( $store_instance ) {
					if( $store_instance->get_type()->is_front_store() ) {
						$args['post__in'] = array(
							'is-front',
							$store_instance->get_type()->type_id(),
							$store_instance->get_slug(),
						);
					} else {
						$store_posts = $store_instance->get_store();

						if ( empty( $store_posts ) ) {
							$args['post__in'] = array( 'no-posts' );
						} else  {
							$args['post__in'] = $store_instance->get_store();
						}
					}
				}

				add_filter( 'jet-engine/listing/grid/add-query-data', array( $this, 'add_query_data_trigger' ) );
			}
		}

		return $args;

	}

	public function add_query_data_trigger( $res ) {
		$res = true;
		remove_filter( 'jet-engine/listing/grid/add-query-data', array( $this, 'add_query_data_trigger' ) );
		return $res;
	}

}
