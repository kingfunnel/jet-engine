<?php
namespace Jet_Engine\Modules\Dynamic_Visibility;

use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as DynamicTags;
use Elementor\Repeater;

class Settings {

	public function __construct() {

		$callback = array( $this, 'add_visibility_settings' );

		add_action( 'elementor/element/column/section_advanced/after_section_end', $callback, 10, 2 );
		add_action( 'elementor/element/section/section_advanced/after_section_end', $callback, 10, 2 );
		add_action( 'elementor/element/common/_section_style/after_section_end', $callback, 10, 2 );
		add_action( 'elementor/element/container/section_layout/after_section_end', $callback, 10, 2 );

		add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_styles' ) );

	}

	/**
	 * Add preview styles for elements with dynamic visibility is enabled
	 * @return void
	 */
	public function preview_styles() {
		wp_add_inline_style( 'editor-preview', '.jedv-enabled--yes{opacity: .6;}' );
	}

	/**
	 * Add visibility settings
	 */
	public function add_visibility_settings( $element, $section_id ) {

		global $wp_roles;
		$user_roles = array();

		foreach ( $wp_roles->roles as $role_id => $role ) {
			$user_roles[ $role_id ] = $role['name'];
		}

		$type = $element->get_type();

		/**
		 * Filter data types for condition comparison
		 *
		 * @var array
		 */
		$data_types = apply_filters( 'jet-engine/modules/dynamic-visibility/data-types', array(
			'chars'   => __( 'Chars (alphabetical comparison)', 'jet-engine' ),
			'numeric' => __( 'Numeric', 'jet-engine' ),
			'date'    => __( 'Datetime', 'jet-engine' )
		) );

		$element->start_controls_section(
			'jedv_section',
			array(
				'tab' => Controls_Manager::TAB_ADVANCED,
				'label' => __( 'Dynamic Visibility', 'jet-engine' ),
			)
		);

		$element->add_control(
			'jedv_enabled',
			array(
				'type'           => Controls_Manager::SWITCHER,
				'label'          => __( 'Enable', 'jet-engine' ),
				'render_type'    => 'template',
				'prefix_class'   => 'jedv-enabled--',
				'style_transfer' => false,
			)
		);

		$element->add_control(
			'jedv_type',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => __( 'Visibility condition type', 'jet-engine' ),
				'label_block' => true,
				'default'     => 'show',
				'options'     => array(
					'show' => __( 'Show element if condition met', 'jet-engine' ),
					'hide' => __( 'Hide element if condition met', 'jet-engine' ),
				),
				'condition'  => array(
					'jedv_enabled' => 'yes',
				),
				'style_transfer' => false,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'jedv_condition',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => __( 'Condition', 'jet-engine' ),
				'label_block' => true,
				'groups'      => Module::instance()->conditions->get_grouped_conditions_for_options(),
			)
		);

		$repeater->add_control(
			'jedv_user_role',
			array(
				'label'       => __( 'User role', 'jet-engine' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $user_roles,
				'label_block' => true,
				'condition'   => array(
					'jedv_condition' => array( 'user-role', 'user-role-not' ),
				),
			)
		);

		$repeater->add_control(
			'jedv_user_id',
			array(
				'label'       => __( 'User IDs', 'jet-engine' ),
				'description' => __( 'Set comma separated IDs list (10, 22, 19 etc.). Note: ID Guest user is 0', 'jet-engine' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'condition'   => array(
					'jedv_condition' => array( 'user-id', 'user-id-not' ),
				),
			)
		);

		$repeater->add_control(
			'jedv_field',
			array(
				'label'       => __( 'Field', 'jet-engine' ),
				'description' => __( 'Enter meta field name or select dynamic tag to compare value against. <br><b>Note!</b> If your meta field contains array, for example JetEngine Checkbox field etc, you need to set meta field name manually (not with dynamic capability)', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic' => array(
					'active' => true,
					'categories' => array(
						DynamicTags::BASE_GROUP,
						DynamicTags::TEXT_CATEGORY,
						DynamicTags::URL_CATEGORY,
						DynamicTags::GALLERY_CATEGORY,
						DynamicTags::IMAGE_CATEGORY,
						DynamicTags::MEDIA_CATEGORY,
						DynamicTags::POST_META_CATEGORY,
						DynamicTags::NUMBER_CATEGORY,
						DynamicTags::COLOR_CATEGORY,
						\Jet_Engine_Dynamic_Tags_Module::JET_MACROS_CATEGORY,
					),
				),
				'condition'   => array(
					'jedv_condition' => Module::instance()->conditions->get_conditions_for_fields(),
				),
			)
		);

		$repeater->add_control(
			'jedv_value',
			array(
				'label'       => __( 'Value', 'jet-engine' ),
				'description' => __( 'Set value to compare. Separate values with commas to set values list.', 'jet-engine' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'dynamic' => array(
					'active' => true,
					'categories' => array(
						\Jet_Engine_Dynamic_Tags_Module::JET_MACROS_CATEGORY,
					),

				),
				'condition'   => array(
					'jedv_condition' => Module::instance()->conditions->get_conditions_with_value_detect(),
				),
			)
		);

		$repeater->add_control(
			'jedv_context',
			array(
				'label'       => __( 'Context', 'jet-engine' ),
				'description' => __( 'Context of object to get value from - current post by default or current listing item object', 'jet-engine' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'default',
				'options'     => array(
					'default'         => __( 'Default', 'jet-engine' ),
					'current_listing' => __( 'Current listing item object', 'jet-engine' ),
				),
				'condition'   => array(
					'jedv_condition' => Module::instance()->conditions->get_conditions_for_fields(),
				),
			)
		);

		Module::instance()->conditions->add_condition_specific_controls( $repeater );

		$repeater->add_control(
			'jedv_data_type',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => __( 'Data type', 'jet-engine' ),
				'label_block' => true,
				'default'     => 'chars',
				'options'     => $data_types,
				'condition'   => array(
					'jedv_condition' => Module::instance()->conditions->get_conditions_with_type_detect(),
				),
			)
		);

		$element->add_control(
			'jedv_conditions',
			array(
				'label'   => __( 'Conditions', 'jet-engine' ),
				'type'    => 'jet-repeater',
				'fields'  => $repeater->get_controls(),
				'default' => array(
					array(
						'jedv_condition' => '',
					)
				),
				'title_field' => '<# var jedv_labels=' . json_encode( Module::instance()->conditions->get_conditions_for_options() ) . ';#> {{{ jedv_labels[jedv_condition] }}}',
				'condition'   => array(
					'jedv_enabled' => 'yes',
				),
				'style_transfer' => false,
			)
		);

		$element->add_control(
			'jedv_relation',
			array(
				'label'   => __( 'Relation', 'jet-engine' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'AND',
				'options' => array(
					'AND' => __( 'AND', 'jet-engine' ),
					'OR'  => __( 'OR', 'jet-engine' ),
				),
				'condition' => array(
					'jedv_enabled' => 'yes',
				),
				'style_transfer' => false,
			)
		);

		if ( 'column' === $type ) {
			$element->add_control(
				'jedv_resize_columns',
				array(
					'label'     => __( 'Resize other columns', 'jet-engine' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => array(
						'jedv_enabled' => 'yes',
					),
					'style_transfer' => false,
				)
			);
		}

		$element->end_controls_section();

	}

}
