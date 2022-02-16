<cx-vui-collapse
	:collapsed="false"
>
	<h3 class="cx-vui-subtitle" slot="title" v-html="blockTitle + ' (' + fieldsList.length + ')'"></h3>
	<cx-vui-repeater
		slot="content"
		:button-label="buttonLabel"
		button-style="accent"
		button-size="default"
		v-model="fieldsList"
		@input="onInput"
		@add-new-item="addNewField"
	>
		<cx-vui-repeater-item
			v-for="( field, index ) in fieldsList"
			:title="fieldsList[ index ].title"
			:subtitle="getFieldSubtitle( fieldsList[ index ] )"
			:collapsed="isCollapsed( field )"
			:index="index"
			:customCss="isNestedField( field ) ? 'jet-engine-nested-item' : ''"
			@clone-item="cloneField( $event )"
			@delete-item="deleteField( $event )"
			:key="field.id ? field.id : field.id = getRandomID()"
		>
			<cx-vui-input
				label="<?php _e( 'Label', 'jet-engine' ); ?>"
				description="<?php _e( 'Meta field label. It will be displayed on Post edit page', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].title"
				@input="setFieldProp( index, 'title', $event )"
				@on-input-change="preSetFieldName( index )"
			></cx-vui-input>
			<cx-vui-input
				label="<?php _e( 'Name/ID', 'jet-engine' ); ?>"
				description="<?php _e( 'Meta field name/key/ID. Under this name field will be stored in the database. Should contain only Latin letters, numbers, `-` or `_` chars', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].name"
				@input="setFieldProp( index, 'name', $event )"
				@on-input-change="sanitizeFieldName( index )"
			></cx-vui-input>
			<cx-vui-select
				label="<?php _e( 'Object type', 'jet-engine' ); ?>"
				description="<?php _e( 'Current meta box object type: field or layout element. To close the action of previously selected Tab or Accordion group, use the `Endpoint` option', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="[
					{
						value: 'field',
						label: '<?php _e( 'Field', 'jet-engine' ); ?>'
					},
					{
						value: 'tab',
						label: '<?php _e( 'Tab', 'jet-engine' ); ?>'
					},
					{
						value: 'accordion',
						label: '<?php _e( 'Accordion', 'jet-engine' ); ?>'
					},
					{
						value: 'endpoint',
						label: '<?php _e( 'Endpoint', 'jet-engine' ); ?>'
					},
				]"
				:value="fieldsList[ index ].object_type"
				@input="setFieldProp( index, 'object_type', $event )"
				:conditions="[
					{
						'input':   'object_type',
						'compare': 'not_in',
						'value':   hideOptions,
					},
					{
						'input':    'object_type',
						'compare': 'not_in',
						'value':    disabledFields,
					},
				]"
			></cx-vui-select>
			<cx-vui-select
				:label="'<?php _e( 'Field type', 'jet-engine' ); ?>'"
				:description="'<?php _e( 'Meta field type. Defines the way field to be displayed on Post edit page', 'jet-engine' ); ?>'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="fieldTypes"
				:value="fieldsList[ index ].type"
				:conditions="[
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
				@input="setFieldProp( index, 'type', $event )"
			></cx-vui-select>
			<cx-vui-select
				:label="'<?php _e( 'Layout', 'jet-engine' ); ?>'"
				:description="'<?php _e( 'Select tab layout. Note, layout selected on first tab in set will be automatically applied to all other tabs', 'jet-engine' ); ?>'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="[
					{
						value: 'horizontal',
						label: '<?php _e( 'Horizontal', 'jet-engine' ); ?>'
					},
					{
						value: 'vertical',
						label: '<?php _e( 'Vertical', 'jet-engine' ); ?>'
					},
				]"
				:value="fieldsList[ index ].tab_layout"
				:conditions="[
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'tab',
					}
				]"
				@input="setFieldProp( index, 'tab_layout', $event )"
			></cx-vui-select>
			<cx-vui-switcher
				label="<?php _e( 'Allow Custom', 'jet-engine' ); ?>"
				description="<?php _e( 'Allow \'custom\' values to be added', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].allow_custom"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'in',
						'value':   [ 'checkbox', 'radio' ],
					},
					{
						'input':    'allow_custom',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':   'allow_custom',
						'compare': 'not_in',
						'value':   hideOptions,
					}
				]"
				@input="setFieldProp( index, 'allow_custom', $event )"
			></cx-vui-switcher>
			<cx-vui-switcher
				label="<?php _e( 'Save Custom', 'jet-engine' ); ?>"
				description="<?php _e( 'Save \'custom\' values to the field\'s options', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].save_custom"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'in',
						'value':   [ 'checkbox', 'radio' ],
					},
					{
						'input':    'save_custom',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':   fieldsList[ index ].allow_custom,
						'compare': 'equal',
						'value':   true,
					},
					{
						'input':   'save_custom',
						'compare': 'not_in',
						'value':   hideOptions,
					}
				]"
				@input="setFieldProp( index, 'save_custom', $event )"
			></cx-vui-switcher>
			<cx-vui-switcher
				label="<?php _e( 'Get options from the glossary', 'jet-engine' ); ?>"
				description="<?php _e( 'Automatically get options for this field from the existing glossary', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].options_from_glossary"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'in',
						'value':   [ 'checkbox', 'radio', 'select' ],
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
				]"
				@input="setFieldProp( index, 'options_from_glossary', $event )"
			></cx-vui-switcher>
			<cx-vui-select
				:label="'<?php _e( 'Glossary', 'jet-engine' ); ?>'"
				:description="'<?php _e( 'Select exact glossary to get options from', 'jet-engine' ); ?>'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="glossariesList"
				:value="fieldsList[ index ].glossary_id"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'in',
						'value':   [ 'checkbox', 'radio', 'select' ],
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':   fieldsList[ index ].options_from_glossary,
						'compare': 'equal',
						'value':   true,
					},
				]"
				@input="setFieldProp( index, 'glossary_id', $event )"
			></cx-vui-select>
			<cx-vui-component-wrapper
				:wrapper-css="[ 'fullwidth-control' ]"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'in',
						'value':    [ 'checkbox', 'select', 'radio' ],
					},
					{
						'input':   fieldsList[ index ].options_from_glossary,
						'compare': 'not_equal',
						'value':   true,
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			>
				<div class="cx-vui-inner-panel">
					<cx-vui-repeater
						:button-label="'<?php _e( 'New Field Option', 'jet-engine' ); ?>'"
						:button-style="'accent'"
						:button-size="'mini'"
						v-model="fieldsList[ index ].options"
						@add-new-item="addNewFieldOption( $event, index )"
					>
						<cx-vui-repeater-item
							v-for="( option, optionIndex ) in fieldsList[ index ].options"
							:title="fieldsList[ index ].options[ optionIndex ].value"
							:subtitle="getOptionSubtitle( fieldsList[ index ].options[ optionIndex ] )"
							:collapsed="isCollapsed( option )"
							:index="optionIndex"
							@clone-item="cloneOption( $event, index )"
							@delete-item="deleteOption( $event, index )"
							:key="option.id ? option.id : option.id = getRandomID()"
						>
							<cx-vui-input
								:label="'<?php _e( 'Option Value', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'This value will be saved into Database', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ].options[ optionIndex ].key"
								@input="setOptionProp( index, optionIndex, 'key', $event )"
							></cx-vui-input>
							<cx-vui-input
								:label="'<?php _e( 'Option label', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'This will be shown for user on Post edit page', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ].options[ optionIndex ].value"
								@input="setOptionProp( index, optionIndex, 'value', $event )"
							></cx-vui-input>
							<cx-vui-switcher
								label="<?php _e( 'Is checked (selected)', 'jet-engine' ); ?>"
								description="<?php _e( 'Check this to make this field checked or selected by default.', 'jet-engine' ); ?>"
								:wrapper-css="[ 'equalwidth' ]"
								:value="fieldsList[ index ].options[ optionIndex ].is_checked"
								@input="setOptionProp( index, optionIndex, 'is_checked', $event )"
							></cx-vui-switcher>
						</cx-vui-repeater-item>
					</cx-vui-repeater>
				</div>
			</cx-vui-component-wrapper>
			<cx-vui-switcher
				:label="'<?php _e( 'Save as timestamp', 'jet-engine' ); ?>'"
				:description="'<?php _e( 'If this option is enabled date will be saved in database Unix timestamp. Toggle it if you need to sort or query posts by date', 'jet-engine' ); ?>'"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].is_timestamp"
				@input="setFieldProp( index, 'is_timestamp', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'in',
						'value':    [ 'date', 'datetime-local' ],
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-switcher>
			<cx-vui-input
				label="<?php _e( 'Placeholder', 'jet-engine' ); ?>"
				description="<?php _e( 'Placeholder text', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].placeholder"
				@input="setFieldProp( index, 'placeholder', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'equal',
						'value':    'select',
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-input>
			<cx-vui-switcher
				:label="'<?php _e( 'Save as array', 'jet-engine' ); ?>'"
				:description="'<?php _e( 'If this option is enabled checked values will be stored as plain PHP array. Use this option if this meta value will be edited from front-end form', 'jet-engine' ); ?>'"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].is_array"
				@input="setFieldProp( index, 'is_array', $event )"
				:conditions="[
					{
						'input':    'is_array',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':    fieldsList[ index ].type,
						'compare': 'equal',
						'value':    'checkbox',
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-switcher>
			<cx-vui-f-select
				label="<?php _e( 'Search in post types', 'jet-engine' ); ?>"
				description="<?php _e( 'Select post types available to search in', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:options-list="postTypes"
				:size="'fullwidth'"
				:multiple="true"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'equal',
						'value':   'posts',
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
				:value="fieldsList[ index ].search_post_type"
				@input="setFieldProp( index, 'search_post_type', $event )"
			></cx-vui-f-select>
			<cx-vui-switcher
				label="<?php _e( 'Multiple', 'jet-engine' ); ?>"
				description="<?php _e( 'Allow to select multiple values', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].is_multiple"
				@input="setFieldProp( index, 'is_multiple', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'in',
						'value':    [ 'select', 'posts' ],
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-switcher>
			<cx-vui-input
				label="<?php _e( 'Min value', 'jet-engine' ); ?>"
				description="<?php _e( 'Set a minimum value for a number field', 'jet-engine' ); ?>"
				:type="'number'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].min_value"
				@input="setFieldProp( index, 'min_value', $event )"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'equal',
						'value':   'number',
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-input>
			<cx-vui-input
				label="<?php _e( 'Max value', 'jet-engine' ); ?>"
				description="<?php _e( 'Set a maximum value for a number field', 'jet-engine' ); ?>"
				:type="'number'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].max_value"
				@input="setFieldProp( index, 'max_value', $event )"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'equal',
						'value':   'number',
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-input>
			<cx-vui-input
				label="<?php _e( 'Step value', 'jet-engine' ); ?>"
				description="<?php _e( 'Set a stepping interval for a number field', 'jet-engine' ); ?>"
				:type="'number'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].step_value"
				@input="setFieldProp( index, 'step_value', $event )"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'equal',
						'value':   'number',
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-input>
			<cx-vui-select
				:label="'<?php _e( 'Value format', 'jet-engine' ); ?>'"
				:description="'<?php _e( 'Set the format of the value will be stored in the database', 'jet-engine' ); ?>'"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="[
					{
						value: 'id',
						label: '<?php _e( 'Media ID', 'jet-engine' ); ?>'
					},
					{
						value: 'url',
						label: '<?php _e( 'Media URL', 'jet-engine' ); ?>'
					},
					{
						value: 'both',
						label: '<?php _e( 'Array with media ID and URL', 'jet-engine' ); ?>'
					},
				]"
				:value="fieldsList[ index ].value_format"
				@input="setFieldProp( index, 'value_format', $event )"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'in',
						'value':   [ 'media', 'gallery' ],
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-select>
			<cx-vui-component-wrapper
				:wrapper-css="[ 'fullwidth-control' ]"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'equal',
						'value':    'repeater',
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			>
				<div class="cx-vui-inner-panel">
					<cx-vui-repeater
						:button-label="'<?php _e( 'New Repeater Field', 'jet-engine' ); ?>'"
						:button-style="'accent'"
						:button-size="'mini'"
						v-model="fieldsList[ index ]['repeater-fields']"
						@add-new-item="addNewRepeaterField( $event, index )"
					>
						<cx-vui-repeater-item
							v-for="( rField, rFieldIndex ) in fieldsList[ index ]['repeater-fields']"
							:title="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].title"
							:subtitle="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].name + ' (' + fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type + ')'"
							:collapsed="isCollapsed( rField )"
							:index="rFieldIndex"
							@clone-item="cloneRepeaterField( $event, index )"
							@delete-item="deleteRepeaterField( $event, index )"
							:key="rField.id ? rField.id : rField.id = getRandomID()"
						>
							<cx-vui-input
								:label="'<?php _e( 'Label', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'Repeater field label. Will be displayed on Post edit page', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].title"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'title', $event )"
								@on-input-change="preSetRepeaterFieldName( index, rFieldIndex )"
							></cx-vui-input>
							<cx-vui-input
								:label="'<?php _e( 'Name', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'Repeater field name/ID. Should contain only latin letters, numbers, `-` or `_` chars', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].name"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'name', $event )"
								@on-input-change="sanitizeRepeaterFieldName( index, rFieldIndex )"
							></cx-vui-input>
							<cx-vui-select
								:label="'<?php _e( 'Type', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'Repeater field type. Defines the way field be displayed on Post edit page', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:options-list="repeaterFieldTypes"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'type', $event )"
							></cx-vui-select>
							<cx-vui-switcher
								label="<?php _e( 'Get options from the glossary', 'jet-engine' ); ?>"
								description="<?php _e( 'Automatically get options for this field from the existing glossary', 'jet-engine' ); ?>"
								:wrapper-css="[ 'equalwidth' ]"
								v-model="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options_from_glossary"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'in',
										'value':    [ 'checkbox', 'select', 'radio' ],
									}
								]"
							></cx-vui-switcher>
							<cx-vui-select
								label="<?php _e( 'Glossary', 'jet-engine' ); ?>"
								description="<?php _e( 'Select exact glossary to get options from', 'jet-engine' ); ?>"
								:wrapper-css="[ 'equalwidth' ]"
								size="fullwidth"
								:options-list="glossariesList"
								v-model="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].glossary_id"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'in',
										'value':    [ 'checkbox', 'select', 'radio' ],
									},
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options_from_glossary,
										'compare': 'equal',
										'value':   true,
									}
								]"
							></cx-vui-select>
							<cx-vui-component-wrapper
								:wrapper-css="[ 'fullwidth-control' ]"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'in',
										'value':    [ 'checkbox', 'select', 'radio' ],
									},
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options_from_glossary,
										'compare': 'not_equal',
										'value':   true,
									},
								]"
							>
								<div class="cx-vui-inner-panel">
									<cx-vui-repeater
										:button-label="'<?php _e( 'New Field Option', 'jet-engine' ); ?>'"
										:button-style="'accent'"
										:button-size="'mini'"
										v-model="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options"
										@add-new-item="addNewRepeaterFieldOption( $event, rFieldIndex, index )"
									>
										<cx-vui-repeater-item
											v-for="( rFieldOption, rFieldOptionIndex ) in fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options"
											:title="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options[ rFieldOptionIndex ].value"
											:subtitle="getOptionSubtitle( fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options[ rFieldOptionIndex ] )"
											:collapsed="isCollapsed( rFieldOption )"
											:index="rFieldOptionIndex"
											@clone-item="cloneRepeaterFieldOption( $event, rFieldIndex, index )"
											@delete-item="deleteRepeaterFieldOption( $event, rFieldIndex, index )"
											:key="rFieldOption.id ? rFieldOption.id : rFieldOption.id = getRandomID()"
										>
											<cx-vui-input
												:label="'<?php _e( 'Option Value', 'jet-engine' ); ?>'"
												:description="'<?php _e( 'This value will be saved into Database', 'jet-engine' ); ?>'"
												:wrapper-css="[ 'equalwidth' ]"
												:size="'fullwidth'"
												:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options[ rFieldOptionIndex ].key"
												@input="setRepeaterFieldOptionProp( index, rFieldIndex, rFieldOptionIndex, 'key', $event )"
											></cx-vui-input>
											<cx-vui-input
												:label="'<?php _e( 'Option label', 'jet-engine' ); ?>'"
												:description="'<?php _e( 'This will be shown for user on Post edit page', 'jet-engine' ); ?>'"
												:wrapper-css="[ 'equalwidth' ]"
												:size="'fullwidth'"
												:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options[ rFieldOptionIndex ].value"
												@input="setRepeaterFieldOptionProp( index, rFieldIndex, rFieldOptionIndex, 'value', $event )"
											></cx-vui-input>
											<cx-vui-switcher
												label="<?php _e( 'Is checked (selected)', 'jet-engine' ); ?>"
												description="<?php _e( 'Check this to make this field checked or selected by default.', 'jet-engine' ); ?>"
												:wrapper-css="[ 'equalwidth' ]"
												:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].options[ rFieldOptionIndex ].is_checked"
												@input="setRepeaterFieldOptionProp( index, rFieldIndex, rFieldOptionIndex, 'is_checked', $event )"
											></cx-vui-switcher>
										</cx-vui-repeater-item>
									</cx-vui-repeater>
								</div>
							</cx-vui-component-wrapper>
							<cx-vui-input
								label="<?php _e( 'Min value', 'jet-engine' ); ?>"
								description="<?php _e( 'Set a minimum value for a number field', 'jet-engine' ); ?>"
								:type="'number'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].min_value"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'min_value', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'equal',
										'value':   'number',
									}
								]"
							></cx-vui-input>
							<cx-vui-input
								label="<?php _e( 'Max value', 'jet-engine' ); ?>"
								description="<?php _e( 'Set a maximum value for a number field', 'jet-engine' ); ?>"
								:type="'number'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].max_value"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'max_value', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'equal',
										'value':   'number',
									}
								]"
							></cx-vui-input>
							<cx-vui-input
								label="<?php _e( 'Step value', 'jet-engine' ); ?>"
								description="<?php _e( 'Set a stepping interval for a number field', 'jet-engine' ); ?>"
								:type="'number'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].step_value"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'step_value', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'equal',
										'value':   'number',
									}
								]"
							></cx-vui-input>
							<cx-vui-f-select
								label="<?php _e( 'Search in post types', 'jet-engine' ); ?>"
								description="<?php _e( 'Select post types available to search in', 'jet-engine' ); ?>"
								:wrapper-css="[ 'equalwidth' ]"
								:options-list="postTypes"
								size="fullwidth"
								:multiple="true"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].search_post_type"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'search_post_type', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'equal',
										'value':   'posts',
									},
								]"
								v-model="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].search_post_type"
							></cx-vui-f-select>
							<cx-vui-input
								label="<?php _e( 'Placeholder', 'jet-engine' ); ?>"
								description="<?php _e( 'Placeholder text', 'jet-engine' ); ?>"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								v-model="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].placeholder"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'equal',
										'value':   'select',
									}
								]"
							></cx-vui-input>
							<cx-vui-switcher
								:label="'<?php _e( 'Multiple', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'Allow to select multiple values', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].is_multiple"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'is_multiple', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'in',
										'value':    [ 'select', 'posts' ],
									},
								]"
							></cx-vui-switcher>
							<cx-vui-switcher
								:label="'<?php _e( 'Save as array', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'If this option is enabled checked values will be stored as plain PHP array. Use this option if this meta value will be edited from front-end form', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].is_array"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'is_array', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'equal',
										'value':   'checkbox',
									},
								]"
							></cx-vui-switcher>
							<cx-vui-select
								:label="'<?php _e( 'Value format', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'Set the format of the value will be stored in the database', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:options-list="[
									{
										value: 'id',
										label: '<?php _e( 'Media ID', 'jet-engine' ); ?>'
									},
									{
										value: 'url',
										label: '<?php _e( 'Media URL', 'jet-engine' ); ?>'
									},
									{
										value: 'both',
										label: '<?php _e( 'Array with media ID and URL', 'jet-engine' ); ?>'
									},
								]"
								:value="fieldsList[ index ]['repeater-fields'][ rFieldIndex ].value_format"
								@input="setRepeaterFieldProp( index, rFieldIndex, 'value_format', $event )"
								:conditions="[
									{
										'input':   fieldsList[ index ]['repeater-fields'][ rFieldIndex ].type,
										'compare': 'in',
										'value':   [ 'media', 'gallery' ],
									}
								]"
							></cx-vui-select>
						</cx-vui-repeater-item>
					</cx-vui-repeater>
				</div>
			</cx-vui-component-wrapper>
			<cx-vui-switcher
				label="<?php _e( 'Collapsed', 'jet-engine' ); ?>"
				description="<?php _e( 'Toggle this option to collapse repeater items on page load', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].repeater_collapsed"
				@input="setFieldProp( index, 'repeater_collapsed', $event )"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'equal',
						'value':   'repeater',
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-switcher>
			<cx-vui-select
				label="<?php _e( 'Title Field', 'jet-engine' ); ?>"
				description="<?php _e( 'Select a repeater field to show as a repeater item title', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="getRepeaterTitleFields( index )"
				:value="fieldsList[ index ].repeater_title_field"
				@input="setFieldProp( index, 'repeater_title_field', $event )"
				:conditions="[
					{
						'input':   fieldsList[ index ].type,
						'compare': 'equal',
						'value':   'repeater',
					},
					{
						'input':   fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-select>
			<cx-vui-textarea
				label="<?php _e( 'Description', 'jet-engine' ); ?>"
				description="<?php _e( 'Meta field description to be shown on Post edit page', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].description"
				@input="setFieldProp( index, 'description', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':    fieldsList[ index ].type,
						'compare': 'not_equal',
						'value':    'html',
					},
				]"
			></cx-vui-textarea>
			<cx-vui-textarea
				label="<?php _e( 'HTML Code', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].html"
				@input="setFieldProp( index, 'html', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':    fieldsList[ index ].type,
						'compare': 'equal',
						'value':    'html',
					},
				]"
			></cx-vui-textarea>
			<cx-vui-input
				label="<?php _e( 'CSS Classes', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].html_css_class"
				@input="setFieldProp( index, 'html_css_class', $event )"
				@on-input-change="preSetFieldName( index )"
				:conditions="[
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':    fieldsList[ index ].type,
						'compare': 'equal',
						'value':    'html',
					},
				]"
			></cx-vui-input>
			<cx-vui-select
				label="<?php _e( 'Field width', 'jet-engine' ); ?>"
				description="<?php _e( 'Select meta field width from the dropdown list for Post edit page', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:options-list="[
					{
						value: '100%',
						label: '100%',
					},
					{
						value: '75%',
						label: '75%',
					},
					{
						value: '66.66666%',
						label: '66.6%',
					},
					{
						value: '50%',
						label: '50%',
					},
					{
						value: '33.33333%',
						label: '33.3%',
					},
					{
						value: '25%',
						label: '25%',
					},
				]"
				:value="fieldsList[ index ].width"
				:conditions="[
					{
						'input':    'width',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
				@input="setFieldProp( index, 'width', $event )"
			></cx-vui-select>
			<cx-vui-input
				label="<?php _e( 'Character limit', 'jet-engine' ); ?>"
				description="<?php _e( 'Max field value length. Leave empty for no limit', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].max_length"
				@input="setFieldProp( index, 'max_length', $event )"
				@on-input-change="preSetFieldName( index )"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'in',
						'value':    [ 'text', 'textarea' ],
					},
					{
						'input':    'max_length',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-input>
			<cx-vui-input
				label="<?php _e( 'Default value', 'jet-engine' ); ?>"
				description="<?php _e( 'Set default value for current field. <b>Note</b> For date field you can set any value could be processed by strtotime() function.', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:size="'fullwidth'"
				:value="fieldsList[ index ].default_val"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'in',
						'value':    [ 'text', 'date', 'textarea', 'iconpicker', 'wysiwyg', 'number' ],
					},
					{
						'input':    'default_val',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
				@input="setFieldProp( index, 'default_val', $event )"
			></cx-vui-input>
			<cx-vui-switcher
				label="<?php _e( 'Is required', 'jet-engine' ); ?>"
				description="<?php _e( 'Toggle this option to make this field as required one. Note: so far, required fields don’t work properly with Blocks editor', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].is_required"
				@input="setFieldProp( index, 'is_required', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].type,
						'compare': 'in',
						'value':    [ 'text', 'date', 'time', 'datetime-local', 'textarea', 'iconpicker', 'select' ],
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					}
				]"
			></cx-vui-switcher>
			<template
				v-if="( 'checkbox' !== fieldsList[ index ].type ) || ( 'checkbox' === fieldsList[ index ].type && fieldsList[ index ].is_array )"
			>
				<cx-vui-switcher
					label="<?php _e( 'Quick edit support', 'jet-engine' ); ?>"
					description="<?php _e( 'Toggle this option to make this field available in the Quick Edit section', 'jet-engine' ); ?>"
					:wrapper-css="[ 'equalwidth' ]"
					:value="fieldsList[ index ].quick_editable"
					@input="setFieldProp( index, 'quick_editable', $event )"
					:conditions="[
						{
							'input':    fieldsList[ index ].type,
							'compare': 'in',
							'value':    quickEditSupports,
						},
						{
							'input':    'quick_editable',
							'compare': 'not_in',
							'value':    disabledFields,
						},
						{
							'input':    fieldsList[ index ].object_type,
							'compare': 'equal',
							'value':   'field',
						},
						{
							'input':   'quick_editable',
							'compare': 'not_in',
							'value':   hideOptions,
						}
					]"
				></cx-vui-switcher>
			</template>
			<cx-vui-switcher
				label="<?php _e( 'Conditional Logic', 'jet-engine' ); ?>"
				description="<?php _e( 'Toggle this option to set meta field display rules. Note: relation type is AND.', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="fieldsList[ index ].conditional_logic"
				@input="setFieldProp( index, 'conditional_logic', $event )"
				:conditions="[
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':    'conditional_logic',
						'compare': 'not_in',
						'value':    disabledFields,
					},
					{
						'input':    fieldsList[ index ].type,
						'compare': 'not_in',
						'value':    [ 'html' ],
					},
				]"
			></cx-vui-switcher>
			<cx-vui-component-wrapper
				:wrapper-css="[ 'fullwidth-control' ]"
				:conditions="[
					{
						'input':    fieldsList[ index ].conditional_logic,
						'compare': 'equal',
						'value':    true,
					},
					{
						'input':    fieldsList[ index ].object_type,
						'compare': 'equal',
						'value':   'field',
					},
					{
						'input':    fieldsList[ index ].type,
						'compare': 'not_in',
						'value':    [ 'html' ],
					},
				]"
			>
				<div class="cx-vui-inner-panel">
					<cx-vui-repeater
						:button-label="'<?php _e( 'New Rule', 'jet-engine' ); ?>'"
						:button-style="'accent'"
						:button-size="'mini'"
						v-model="fieldsList[ index ].conditions"
						@add-new-item="addNewCondition( $event, index )"
					>
						<cx-vui-repeater-item
							v-for="( condition, conditionIndex ) in fieldsList[ index ].conditions"
							:title="getConditionFieldTitle( index, conditionIndex )"
							:subtitle="fieldsList[ index ].conditions[ conditionIndex ].operator"
							:collapsed="isCollapsed( condition )"
							:index="conditionIndex"
							@clone-item="cloneCondition( $event, index )"
							@delete-item="deleteCondition( $event, index )"
							:key="condition.id ? condition.id : condition.id = getRandomID()"
						>
							<cx-vui-select
								:label="'<?php _e( 'Field', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:options-list="getConditionFieldsList( index )"
								:value="fieldsList[ index ].conditions[ conditionIndex ].field"
								@input="setConditionProp( index, conditionIndex, 'field', $event )"
							></cx-vui-select>
							<cx-vui-select
								:label="'<?php _e( 'Operator', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:options-list="[
									{
										value: '',
										label: '<?php _e( 'Select operator...', 'jet-engine' ); ?>',
									},
									{
										value: 'equal',
										label: '<?php _e( 'Equal', 'jet-engine' ); ?>',
									},
									{
										value: 'not_equal',
										label: '<?php _e( 'Not Equal', 'jet-engine' ); ?>',
									},
									{
										value: 'in',
										label: '<?php _e( 'In the list', 'jet-engine' ); ?>',
									},
									{
										value: 'not_in',
										label: '<?php _e( 'Not In the list', 'jet-engine' ); ?>',
									},
								]"
								:value="fieldsList[ index ].conditions[ conditionIndex ].operator"
								@input="setConditionProp( index, conditionIndex, 'operator', $event )"
							></cx-vui-select>
							<cx-vui-input
								:label="'<?php _e( 'Value', 'jet-engine' ); ?>'"
								:description="'<?php _e( 'For <b>In the list</b> and <b>Not In the list</b> operator separate multiple values with comma', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:value="fieldsList[ index ].conditions[ conditionIndex ].value"
								@input="setConditionProp( index, conditionIndex, 'value', $event )"
								:conditions="[
									{
										'input':    getConditionFieldType( index, conditionIndex ),
										'compare':  'not_in',
										'value':    [ 'checkbox', 'radio', 'select', 'switcher', '' ],
									},
									{
										'input':   fieldsList[ index ].conditions[ conditionIndex ].operator,
										'compare': 'not_equal',
										'value':   '',
									},
								]"
							></cx-vui-input>
							<cx-vui-select
								:label="'<?php _e( 'Value', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:options-list="[
									{
										value: 'true',
										label: '<?php _e( 'On', 'jet-engine' ); ?>',
									},
									{
										value: 'false',
										label: '<?php _e( 'Off', 'jet-engine' ); ?>',
									}
								]"
								:value="fieldsList[ index ].conditions[ conditionIndex ].value"
								@input="setConditionProp( index, conditionIndex, 'value', $event )"
								:conditions="[
									{
										'input':   getConditionFieldType( index, conditionIndex ),
										'compare': 'equal',
										'value':   'switcher',
									},
									{
										'input':   fieldsList[ index ].conditions[ conditionIndex ].operator,
										'compare': 'not_equal',
										'value':   '',
									},
								]"
							></cx-vui-select>
							<cx-vui-f-select
								:label="'<?php _e( 'Value', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:options-list="getConditionValuesList( index, conditionIndex )"
								:value="fieldsList[ index ].conditions[ conditionIndex ].value"
								@input="setConditionProp( index, conditionIndex, 'value', $event )"
								:remote="isGlossaryField( index, conditionIndex )"
								:remote-callback="getGlossaryFields.bind( this, index, conditionIndex )"
								:conditions="[
									{
										'input':   getConditionFieldType( index, conditionIndex ),
										'compare': 'in',
										'value':   [ 'checkbox', 'radio', 'select' ],
									},
									{
										'input':   fieldsList[ index ].conditions[ conditionIndex ].operator,
										'compare': 'not_in',
										'value':   [ 'in', 'not_in', '' ],
									},
								]"
							></cx-vui-f-select>
							<cx-vui-f-select
								:label="'<?php _e( 'Value', 'jet-engine' ); ?>'"
								:wrapper-css="[ 'equalwidth' ]"
								:size="'fullwidth'"
								:multiple="true"
								:options-list="getConditionValuesList( index, conditionIndex )"
								:value="fieldsList[ index ].conditions[ conditionIndex ].values"
								@input="setConditionProp( index, conditionIndex, 'values', $event )"
								:remote="isGlossaryField( index, conditionIndex )"
								:remote-callback="getGlossaryFields.bind( this, index, conditionIndex )"
								:conditions="[
									{
										'input':   getConditionFieldType( index, conditionIndex ),
										'compare': 'in',
										'value':   [ 'checkbox', 'radio', 'select' ],
									},
									{
										'input':   fieldsList[ index ].conditions[ conditionIndex ].operator,
										'compare': 'in',
										'value':   [ 'in', 'not_in' ],
									},
								]"
							></cx-vui-f-select>
						</cx-vui-repeater-item>
					</cx-vui-repeater>
				</div>
			</cx-vui-component-wrapper>
		</cx-vui-repeater-item>
	</cx-vui-repeater>
</cx-vui-collapse>
