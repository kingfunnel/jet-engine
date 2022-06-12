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
			control,
			value,
			onChange
		} = this.props;

		if ( ! this.isEnbaled() ) {
			return null;
		}

		switch ( control.type ) {

			case 'select':
			case 'select2':

				if ( control.groups ) {
					return <GroupedSelectControl
						label={ control.label }
						help={ control.description ? <div dangerouslySetInnerHTML={{ __html: control.description }}/> : '' }
						options={ control.groups }
						value={ value }
						onChange={ newValue => {
							onChange( newValue );
						} }
					/>;
				} else {
					return <SelectControl
						label={ control.label }
						help={ control.description ? <div dangerouslySetInnerHTML={{ __html: control.description }}/> : '' }
						options={ control.options }
						value={ value }
						onChange={ newValue => {
							onChange( newValue );
						} }
					/>;
				}

			case 'textarea':
				return <TextareaControl
					label={ control.label }
					help={ control.description ? <div dangerouslySetInnerHTML={{ __html: control.description }}/> : '' }
					value={ value }
					onChange={ newValue => {
						onChange( newValue );
					} }
				/>;

			case 'switcher':
				return <ToggleControl
					label={ control.label }
					help={ control.description ? <div dangerouslySetInnerHTML={{ __html: control.description }}/> : '' }
					checked={ value }
					onChange={ () => {
						onChange( !value );
					} }
				/>;

			case 'number':
				return <TextControl
					type="number"
					label={ control.label }
					help={ control.description ? <div dangerouslySetInnerHTML={{ __html: control.description }}/> : '' }
					min={ control.min ? control.min : 1 }
					max={ control.max ? control.max : 100 }
					step={ control.step ? control.step : 1 }
					value={ value }
					onChange={ newValue => {
						onChange( Number( newValue ) );
					} }
				/>;

			default:
				return <TextControl
					type="text"
					label={ control.label }
					help={ control.description ? <div dangerouslySetInnerHTML={{ __html: control.description }}/> : '' }
					value={ value }
					onChange={ newValue => {
						onChange( newValue );
					} }
				/>;
		}
	}
}

export default CustomControl;
