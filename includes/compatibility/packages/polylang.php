<?php
/**
 * Polylang compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Polylang_Package' ) ) {

	class Jet_Engine_Polylang_Package {

		public function __construct() {
			add_filter( 'jet-engine/listings/frontend/rendered-listing-id', array( $this, 'set_translated_listing' ) );

			// Translate Admin Labels
			add_filter( 'jet-engine/compatibility/translate-string', array( $this, 'translate_admin_labels' ) );
		}

		/**
		 * Set translated listing ID to show
		 *
		 * @param int|string $listing_id Listing ID
		 *
		 * @return false|int|null
		 */
		public function set_translated_listing( $listing_id ) {

			if ( function_exists( 'pll_get_post' ) ) {

				$translation_listing_id = pll_get_post( $listing_id );

				if ( null === $translation_listing_id ) {
					// the current language is not defined yet
					return $listing_id;
				} elseif ( false === $translation_listing_id ) {
					//no translation yet
					return $listing_id;
				} elseif ( $translation_listing_id > 0 ) {
					// return translated post id
					return $translation_listing_id;
				}
			}

			return $listing_id;
		}

		/**
		 * Translate Admin Labels
		 *
		 * @param  string $label
		 * @return string
		 */
		public function translate_admin_labels( $label ) {

			pll_register_string( 'jet-engine', $label, 'JetEngine', true );

			return pll__( $label );
		}

	}

}

new Jet_Engine_Polylang_Package();
