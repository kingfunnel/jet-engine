<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Frontend {

	private $template_id = null;
	private $has_access = false;
	public $menu = null;
	public $current_user_obj = false;

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		require jet_engine()->modules->modules_path( 'profile-builder/inc/menu.php' );
		require jet_engine()->modules->modules_path( 'profile-builder/inc/access.php' );

		$this->access = new Access();
		$this->menu   = new Menu();

		add_action(
			'jet-engine/profile-builder/query/setup-props',
			array( $this, 'add_template_filter' )
		);

		add_filter(
			'jet-engine/listings/dynamic-link/custom-url',
			array( $this, 'dynamic_link_url' ), 10, 2
		);

		add_filter(
			'jet-engine/listings/dynamic-image/custom-url',
			array( $this, 'dynamic_link_url' ), 10, 2
		);

	}

	/**
	 * Enqueue page template CSS
	 *
	 * @return [type] [description]
	 */
	public function enqueue_template_css() {

		if ( ! $this->template_id ) {
			return;
		}

		do_action( 'jet-engine/profile-builder/template/assets', $this->template_id, $this );

	}

	/**
	 * Render profile page content
	 *
	 * @return [type] [description]
	 */
	public function render_page_content() {

		if ( ! $this->template_id ) {
			return;
		}

		jet_engine()->admin_bar->register_post_item( $this->template_id);

		$settings = Module::instance()->settings->get();
		$template_mode = Module::instance()->settings->get( 'template_mode' );

		if ( 'rewrite' === $template_mode && ! empty( $settings['force_template_rewrite'] ) ) {

			global $post;

			if ( $this->template_id !== get_the_ID() ) {
				$template = get_post( $this->template_id );
				$tmp      = $post;
				$post     = $template;
			} else {
				$template = $post;
			}

			echo apply_filters( 'the_content', $template->post_content );

			if ( $this->template_id !== get_the_ID() ) {
				$post = $tmp;
			}

		} else {
			$template = get_post( $this->template_id );
			echo apply_filters( 'jet-engine/profile-builder/template/content', $template->post_content, $this->template_id, $this );
		}

	}

	/**
	 * Replace default content
	 * @return [type] [description]
	 */
	public function add_template_filter() {

		$settings   = Module::instance()->settings->get();
		$add        = false;
		$structure  = false;
		$has_access = $this->access->check_user_access();
		$subapge    = Module::instance()->query->get_subpage_data();

		if ( ! $has_access['access'] && ! empty( $has_access['template'] ) ) {
			$this->template_id = $has_access['template'];
		} else {

			$this->template_id = ! empty( $subapge['template'] ) ? $subapge['template'][0] : false;

			if ( ! $this->template_id && ! empty( $settings['force_template_rewrite'] ) ) {
				$this->template_id = get_the_ID();
			}
		}

		if ( $has_access['access'] ) {
			$this->has_access = true;
		}

		if ( $this->template_id ) {
			add_filter( 'template_include', array( $this, 'set_page_template' ), 99999 );
			add_action( 'jet-engine/profile-builder/template/main-content', array( $this, 'render_page_content' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_template_css' ) );
		}

	}

	/**
	 * Rewrite template
	 *
	 * @param [type] $template [description]
	 */
	public function set_page_template( $template ) {

		$template_mode = Module::instance()->settings->get( 'template_mode' );

		if ( 'rewrite' === $template_mode || ! $this->has_access ) {
			$current_template = get_page_template_slug();

			if ( $current_template && 'elementor_canvas' === $current_template ) {
				$template = jet_engine()->get_template( 'profile-builder/page-canvas.php' );
			} else {
				$template = jet_engine()->get_template( 'profile-builder/page.php' );
			}
		}

		return $template;
	}

	/**
	 * Dynamic link URL
	 *
	 * @param  boolean $url      [description]
	 * @param  array   $settings [description]
	 * @return [type]            [description]
	 */
	public function dynamic_link_url( $url = false, $settings = array() ) {

		$link_source = isset( $settings['dynamic_link_source'] ) ? $settings['dynamic_link_source'] : false;

		if ( ! $link_source ) {
			$link_source = isset( $settings['image_link_source'] ) ? $settings['image_link_source'] : false;
		}

		if ( $link_source && 'profile_page' === $link_source && ! empty( $settings['dynamic_link_profile_page'] ) ) {

			$context = ! empty( $settings['object_context'] ) ? $settings['object_context'] : 'default_object';

			if ( ! in_array( $context, array( 'default_object', 'wp_user' ) ) ) {
				$this->current_user_obj = jet_engine()->listings->data->get_object_by_context( $context );
			}

			$profile_page = $settings['dynamic_link_profile_page'];
			$profile_page = explode( '::', $profile_page );

			if ( 1 < count( $profile_page ) ) {
				$this->maybe_set_user_obj_by_context();

				$url = Module::instance()->settings->get_subpage_url( $profile_page[1], $profile_page[0] );

				$this->maybe_reset_user_obj_by_context();
			}

		}

		return $url;
	}

	public function maybe_set_user_obj_by_context() {

		if ( ! $this->current_user_obj ) {
			return;
		}

		add_filter( 'jet-engine/profile-builder/query/pre-get-queried-user', array( $this, 'set_user_obj_by_context' ) );

	}

	public function maybe_reset_user_obj_by_context() {

		if ( ! $this->current_user_obj ) {
			return;
		}

		$this->current_user_obj = null;

		remove_filter( 'jet-engine/profile-builder/query/pre-get-queried-user', array( $this, 'set_user_obj_by_context' ) );

	}

	/**
	 * Set user object by context.
	 *
	 * @param  $user
	 * @return bool|mixed
	 */
	public function set_user_obj_by_context( $user ) {

		if ( $this->current_user_obj ) {
			$user = $this->current_user_obj;
		}

		return $user;
	}


	/**
	 * Render profile menu
	 *
	 * @param  array  $settings [description]
	 * @return [type]           [description]
	 */
	public function profile_menu( $args = array(), $echo = true ) {

		$menu = $this->menu->get_profile_menu( $args );

		if ( $echo ) {
			echo $menu;
		} else {
			return $menu;
		}

	}

}
