'use strict';

Vue.component( 'jet-engine-relation', {
	template: '#jet-edit-relation',
	props: {
		value: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			args: {},
			relationsTypes: JetEngineRelationConfig.relations_types,
			objectTypes: JetEngineRelationConfig.object_types,
		};
	},
	computed: {
		parentRelations() {

			const result = [
					{
					value: '',
					label: 'Select...',
				}
			];

			const existingRelations = JetEngineRelationConfig.existing_relations || [];

			for ( var relationID in existingRelations ) {
				result.push( {
					value: relationID,
					label: existingRelations[ relationID ],
				} );
			}

			return result;
		}
	},
	created() {
		this.args = _.assign( {}, this.value );
	},
	methods: {
		emitInput() {
			this.$emit( 'input', this.args );
		},
		setLabel( value, key ) {
			this.$set( this.args, 'labels', _.assign( {}, this.args.labels, { [ key ]: value } ) );
			this.emitInput();
		},
		setArg( value, key ) {
			this.$set( this.args, key, value );
			this.emitInput();
		}
	}
} );
