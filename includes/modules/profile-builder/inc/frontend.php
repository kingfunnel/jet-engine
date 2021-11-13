<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Frontend {

	private $template_id = null;
	private $has_access = false;
	public $menu = null;

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

		\Elementor\Plugin::instance()->frontend->enqueue_styles();

		$css_file = new \Elementor\Core\Files\CSS\Post( $this->template_id );
		$css_file->enqueue();

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
			echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $this->template_id );
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

			$profile_page = $settings['dynamic_link_profile_page'];
			$profile_page = explode( '::', $profile_page );

			if ( 1 < count( $profile_page ) ) {
				$url = Module::instance()->settings->get_subpage_url( $profile_page[1], $profile_page[0] );
			}

		}

		return $url;
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
