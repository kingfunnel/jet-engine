import GroupedSelectControl from "components/grouped-select-control.js";

const {
	SelectControl,
	ToggleControl,
	TextControl,
	TextareaControl
} = wp.components;

class CustomControl extends wp.element.Component {

	isEnbaled() {

		if ( ! this.props.condition ) {
			return true;
		}

		for ( var field in this.props.condition ) {

			var compare        = this.props.condition[ field ];
			var checked        = true;
			var isNotEqualCond = field.includes( '!' );

			if ( isNotEqualCond ) {
				field = field.replace( '!', '' );
			}

			if ( this.props.prefix ) {
				field = this.props.prefix + field;
			}

			var fieldVal = this.props.getValue( field, this.props.attr, this.props.attributes );

			if ( isNotEqualCond ) {
				if ( Array.isArray( compare ) ) {
					checked = ! compare.includes( fieldVal );
				} else {
					checked = fieldVal != compare;
				}
			} else {
				if ( Array.isArray( compare ) ) {
					checked = compare.includes( fieldVal );
				} else {
					checked = fieldVal == compare;
				}
			}

			if ( ! checked ) {
				return false;
			}

		}

		return true;

	}

	render() {

		const {
			getValue,
			attr,
			attributes,
			setAttributes,
			setValue,
			supports,
			control,
			prefix
		} = this.props;

		if ( ! this.isEnbaled() ) {
			return null;
		}

		var controlName = control.name;

		if ( prefix ) {
			controlName = prefix + controlName;
		}

		switch ( control.type ) {

			case 'select':
			case 'select2':

				if ( control.groups ) {
					return <GroupedSelectControl
						label={ control.label }
						options={ control.groups }
						value={ getValue( controlName, attr, attributes ) }
						onChange={ newValue => {
							setValue( newValue, controlName, attr, attributes, setAttributes, supports );
						} }
					/>;
				} else {
					return <SelectControl
						label={ control.label }
						options={ control.options }
						value={ getValue( controlName, attr, attributes ) }
						onChange={ newValue => {
							setValue( newValue, controlName, attr, attributes, setAttributes, supports );
						} }
					/>;
				}

			case 'textarea':
				return <TextareaControl
					label={ control.label }
					value={ getValue(
						controlName,
						attr,
						attributes
					) }
					onChange={ ( newValue ) => {
						setValue(
							newValue,
							controlName,
							attr,
							attributes,
							setAttributes,
							supports
						);
					} }
				/>;

			case 'switcher':
				return <ToggleControl
					label={ control.label }
					checked={ getValue( controlName, attr, attributes ) }
					onChange={ () => {
						setValue(
							! getValue( controlName, attr, attributes ),
							controlName,
							attr,
							attributes,
							setAttributes,
							supports
						);
					} }
				/>;

			default:
				return <TextControl
					type="text"
					label={ control.label }
					value={ getValue(
						controlName,
						attr,
						attributes
					) }
					onChange={ ( newValue ) => {
						setValue(
							newValue,
							controlName,
							attr,
							attributes,
							setAttributes,
							supports
						);
					} }
				/>;

		}
	}
}

export default CustomControl;
