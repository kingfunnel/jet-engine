const {
		  TextControl,
		  SelectControl,
		  TextareaControl,
		  ToggleControl,
	  } = wp.components;

const {
		  useState,
		  useEffect,
	  } = wp.element;

const {
		  addAction,
		  getFormFieldsBlocks,
		  Tools: { withPlaceholder },
	  } = JetFBActions;
const {
		  ActionFieldsMap,
		  WrapperRequiredControl,
		  MacrosInserter,
	  } = JetFBComponents;

const {
		  applyFilters,
		  addFilter,
	  } = wp.hooks;

addAction(
	'rest_api_request',
	function RestApiAction( props ) {
		const {
				  settings,
				  label,
				  help,
				  source,
				  onChangeSetting,
			  } = props;

		const [ formFields, setFormFields ] = useState( [] );

		useEffect( () => {
			setFormFields( getFormFieldsBlocks() );
		}, [] );

		return <>
			<div className="jet-form-editor__macros-wrap">
				<TextareaControl
					className='jet-border-unset'
					label={ label( 'url' ) }
					value={ settings.url }
					help={ help( 'url' ) }
					onChange={ newValue => onChangeSetting( newValue, 'url' ) }
				/>
				<MacrosInserter
					fields={ formFields }
					onFieldClick={ macros => {
						const content = ( settings.url || '' ) + '%' + macros + '%';
						onChangeSetting( content, 'url' );
					} }
				/>
			</div>
			<div className="jet-form-editor__macros-wrap">
				<TextareaControl
					label={ label( 'body' ) }
					value={ settings.body }
					onChange={ newValue => onChangeSetting( newValue, 'body' ) }
				/>
				<MacrosInserter
					fields={ formFields }
					onFieldClick={ macros => {
						const content = ( settings.body || '' ) + '%' + macros + '%';
						onChangeSetting( content, 'body' );
					} }
				/>
			</div>
			<p
				className={ 'components-base-control__help' }
				style={ { marginTop: '0px', color: 'rgb(117, 117, 117)' } }
				dangerouslySetInnerHTML={ { __html: help( 'body' ) } }
			/>
			<ToggleControl
				label={ label( 'authorization' ) }
				checked={ settings.authorization }
				onChange={ newVal => onChangeSetting( newVal, 'authorization' ) }
			/>
			{ settings.authorization && <>
				<SelectControl
					label={ label( 'auth_type' ) }
					labelPosition='side'
					value={ settings.auth_type }
					onChange={ newValue => {
						onChangeSetting( newValue, 'auth_type' );
					} }
					options={ withPlaceholder( source.auth_types ) }
				/>
				{ 'application-password' === settings.auth_type && <>
					<TextControl
						label={ label( 'application_pass' ) }
						help={ help( 'application_pass' ) }
						value={ settings.application_pass }
						onChange={ newValue => onChangeSetting( newValue, 'application_pass' ) }
					/>
				</> }
				{ applyFilters(
					`jet.engine.restapi.authorization.fields.${ settings.auth_type }`,
					<></>, props,
				) }
			</> }
		</>;
	},
);

addFilter(
	'jet.engine.restapi.authorization.fields.rapidapi',
	'jet-engine',
	function RESTRapidApi( empty, {
		settings,
		label,
		help,
		source,
		onChangeSetting,
	} ) {
		return <>
			<TextControl
				label={ label( 'rapidapi_key' ) }
				help={ help( 'rapidapi_key' ) }
				value={ settings.rapidapi_key }
				onChange={ newValue => onChangeSetting( newValue, 'rapidapi_key' ) }
			/>
			<TextControl
				label={ label( 'rapidapi_host' ) }
				help={ help( 'rapidapi_host' ) }
				value={ settings.rapidapi_host }
				onChange={ newValue => onChangeSetting( newValue, 'rapidapi_host' ) }
			/>
		</>;
	} );

addFilter(
	'jet.engine.restapi.authorization.fields.bearer-token',
	'jet-engine',
	function RESTBearerToken( empty, {
		settings,
		label,
		help,
		source,
		onChangeSetting,
	} ) {
		return <>
			<TextControl
				label={ label( 'bearer_token' ) }
				help={ help( 'bearer_token' ) }
				value={ settings.bearer_token }
				onChange={ newValue => onChangeSetting( newValue, 'bearer_token' ) }
			/>
		</>;
	} );