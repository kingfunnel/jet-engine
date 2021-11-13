<?php
namespace Jet_Engine\Modules\Dynamic_Visibility;

class Module {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Module
	 */
	private static $instance = null;

	public $slug = 'dynamic-visibility';

	/**
	 * @var Conditions\Manager
	 */
	public $conditions = null;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init module components
	 *
	 * @return void
	 */
	public function init() {

		require jet_engine()->modules->modules_path( 'dynamic-visibility/inc/settings.php' );
		require jet_engine()->modules->modules_path( 'dynamic-visibility/inc/conditions/manager.php' );

		$this->conditions = new Conditions\Manager();

		new Settings();

		$el_types = array(
			'section',
			'column',
			'widget',
		);

		foreach ( $el_types as $el ) {
			add_filter( 'elementor/frontend/' . $el . '/should_render', array( $this, 'check_cond' ), 10, 2 );
		}

		add_action( 'elementor/frontend/column/after_render', array( $this, 'add_resize_columns_prop' ) );

	}

	/**
	 * Check render conditions
	 *
	 * @param  [type] $result  [description]
	 * @param  [type] $element [description]
	 * @return [type]          [description]
	 */
	public function check_cond( $result, $element ) {

		$settings   = $element->get_settings();
		$is_enabled = ! empty( $settings['jedv_enabled'] ) ? $settings['jedv_enabled'] : false;
		$is_enabled = filter_var( $is_enabled, FILTER_VALIDATE_BOOLEAN );

		if ( 'section' === $element->get_type() ) {
			$this->maybe_add_resize_columns_class( $element );
		}

		if ( ! $is_enabled ) {
			return $result;
		}

		$dynamic_settings = $element->get_settings_for_display();
		$conditions       = $dynamic_settings['jedv_conditions'];
		$relation         = ! empty( $settings['jedv_relation'] ) ? $settings['jedv_relation'] : 'AND';
		$is_or_relation   = 'OR' === $relation;
		$type             = ! empty( $settings['jedv_type'] ) ? $settings['jedv_type'] : 'show';
		$has_conditions   = false;

		$args = array(
			'type'      => $type,
			'condition' => null,
			'user_role' => null,
			'user_id'   => null,
			'field'     => null,
			'value'     => null,
			'data_type' => null,
			'context'   => null,
		);

		foreach ( $conditions as $index => $settings ) {

			foreach ( $args as $arg => $default ) {
				$key = 'jedv_' . $arg;
				$args[ $arg ] = ! empty( $settings[ $key ] ) ? $settings[ $key ] : $default;
			}

			// Apply macros in value
			if ( null !== $args['value'] ) {
				$args['value'] = jet_engine()->listings->macros->do_macros( $args['value'] );
			}

			$is_dynamic_field = isset( $settings['__dynamic__']['jedv_field'] );
			$is_empty_field   = empty( $settings['jedv_field'] );

			$args['field_raw'] = ( ! $is_dynamic_field && ! $is_empty_field ) ? $settings['jedv_field'] : null;

			if ( empty( $args['condition'] ) ) {
				continue;
			}

			$condition          = $args['condition'];
			$condition_instance = $this->conditions->get_condition( $condition );

			if ( ! $condition_instance ) {
				continue;
			}

			if ( ! $has_conditions ) {
				$has_conditions = true;
			}

			$custom_value_key = 'value_' . $condition_instance->get_id();
			$custom_value = ! empty( $settings[ $custom_value_key ] ) ? $settings[ $custom_value_key ] : false;

			if ( $custom_value ) {
				$args['value'] = $custom_value;
			}

			$args['condition_settings'] = $settings;

			$args = apply_filters( 'jet-engine/modules/dynamic-visibility/condition/args', $args );

			$check = $condition_instance->check( $args );

			if ( 'show' === $type ) {
				if ( $is_or_relation ) {
					if ( $check ) {
						$element->jedv_check_status = true;
						return true;
					}
				} elseif ( ! $check ) {
					$element->jedv_check_status = false;
					return false;
				}
			} else {
				if ( $is_or_relation ) {
					if ( ! $check ) {
						$element->jedv_check_status = false;
						return false;
					}
				} elseif ( $check ) {
					$element->jedv_check_status = true;
					return true;
				}
			}
		}

		if ( ! $has_conditions ) {
			return $result;
		}

		$result = ( 'show' === $type ) ? ! $is_or_relation : $is_or_relation;

		$element->jedv_check_status = $result;

		return $result;
	}

	/**
	 * Add `jedv_resize_columns` property for column.
	 *
	 * @param $column
	 */
	public function add_resize_columns_prop( $column ) {

		if ( ! isset( $column->jedv_check_status ) ) {
			return;
		}

		if ( false !== $column->jedv_check_status ) {
			return;
		}

		$settings = $column->get_settings();

		if ( ! isset( $settings['jedv_resize_columns'] ) ) {
			return;
		}

		if ( ! filter_var( $settings['jedv_resize_columns'], FILTER_VALIDATE_BOOLEAN ) ) {
			return;
		}

		$column->jedv_resize_columns = true;
	}

	/**
	 * Maybe add `jedv-resize-columns` css class for section.
	 *
	 * @param $section
	 */
	public function maybe_add_resize_columns_class( $section ) {
		$has_resize_columns = false;

		foreach ( $section->get_children() as $column ) {
			if ( isset( $column->jedv_resize_columns ) && $column->jedv_resize_columns ) {
				$has_resize_columns = true;
				break;
			}
		}

		if ( $has_resize_columns ) {
			$section->add_render_attribute( '_wrapper', array(
				'class' => 'jedv-resize-columns',
			) );
		}
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Module
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}
