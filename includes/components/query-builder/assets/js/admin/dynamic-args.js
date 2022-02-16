Vue.component( 'jet-query-dynamic-args', {
	name: 'jet-query-dynamic-args',
	template: '#jet-query-dynamic-args',
	directives: { clickOutside: window.JetVueUIClickOutside },
	props: [ 'value' ],
	data: function() {
		return {
			isActive: false,
			macrosList: window.JetEngineQueryDynamicArgs.macros_list,
			argumentsList: window.JetEngineQueryDynamicArgs.dynamic_args_list,
			currentMacros: {},
			editMacros: false,
			result: {},
		};
	},
	created: function() {

		if ( 'string' !== typeof this.value || ! this.value.includes( '%' ) ) {
			return;
		}

		let data = this.value.substring( 1, this.value.length - 1 );

		data = data.split( '|' );

		let macros = data[0];

		for ( var i = 0; i < this.macrosList.length; i++ ) {

			if ( macros === this.macrosList[ i ].id ) {

				this.result = {
					macros: macros,
					macrosName: this.macrosList[ i ].name,
					macrosControls: this.macrosList[ i ].controls,
				};

				if ( 1 < data.length && this.macrosList[ i ].controls ) {
					let index = 1;
					for ( const prop in this.macrosList[ i ].controls ) {

						if ( data[ index ] ) {
							this.$set( this.result, prop, data[ index ] );
						}

						index++;
					}
				}

				return;
			}
		}

	},
	methods: {
		applyMacros: function( macros, force ) {

			force = force || false;

			if ( macros ) {
				this.$set( this.result, 'macros', macros.id );
				this.$set( this.result, 'macrosName', macros.name );

				if ( macros.controls ) {
					this.$set( this.result, 'macrosControls', macros.controls );
				}
			}

			if ( macros && ! force && macros.controls ) {
				this.editMacros = true;
				this.currentMacros = macros;
				return;
			}

			this.$emit( 'input', this.formatResult() );
			this.isActive = false;

		},
		switchIsActive: function() {

			this.isActive = ! this.isActive;

			if ( this.isActive ) {
				if ( this.result.macros ) {
					for (var i = 0; i < this.macrosList.length; i++) {
						if ( this.result.macros === this.macrosList[ i ].id && this.macrosList[ i ].controls ) {
							this.currentMacros = this.macrosList[ i ];
							this.editMacros = true;
						}
					}
				}
			} else {
				this.resetEdit();
			}

		},
		clearResult: function() {
			this.result = {};
			this.$emit( 'input', '' );
		},
		formatResult: function() {

			let res = '%';
			res += this.result.macros;

			if ( this.result.macrosControls ) {
				for ( const prop in this.currentMacros.controls ) {
					res += '|';

					if ( undefined !== this.result[ prop ] ) {
						res += this.result[ prop ];
					}

				}
			}

			res += '%';
			return res;

		},
		onClickOutside: function() {
			this.isActive = false;
			this.editMacros = false;
			this.currentMacros = {};
		},
		resetEdit: function() {
			this.editMacros = false;
			this.currentMacros = {};
		},
		getPreparedControls: function() {

			controls = [];

			for ( const controlID in this.currentMacros.controls ) {

				let control     = this.currentMacros.controls[ controlID ];
				let optionsList = [];
				let type        = control.type;
				let label       = control.label;
				let defaultVal  = control.default;
				let groupsList  = [];
				let condition   = control.condition || {};

				switch ( control.type ) {

					case 'text':
						type = 'cx-vui-input';
						break;

					case 'select':

						type = 'cx-vui-select';

						if ( control.groups ) {

							for ( var i = 0; i < control.groups.length; i++) {

								let group = control.groups[ i ];
								let groupOptions = [];

								for ( const optionValue in group.options ) {
									groupOptions.push( {
										value: optionValue,
										label: group.options[ optionValue ],
									} );
								}

								groupsList.push( {
									label: group.label,
									options: groupOptions,
								} );

							}
						} else {
							for ( const optionValue in control.options ) {
								optionsList.push( {
									value: optionValue,
									label: control.options[ optionValue ],
								} );
							}
						}

						break;
				}

				controls.push( {
					type: type,
					name: controlID,
					label: label,
					default: defaultVal,
					optionsList: optionsList,
					groupsList: groupsList,
					condition: condition,
				} );

			}

			return controls;
		},
		checkCondition: function( condition ) {

			let checkResult = true;

			condition = condition || {};

			for ( const [ fieldName, check ] of Object.entries( condition ) ) {
				if ( check && check.length ) {
					if ( ! check.includes( this.result[ fieldName ] ) ) {
						checkResult = false;
					}
				} else {
					if ( check != this.result[ fieldName ] ) {
						checkResult = false;
					}
				}
			}

			return checkResult;

		}
	},
} );
