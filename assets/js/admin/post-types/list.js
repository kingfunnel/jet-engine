(function( $, JetEngineCPTListConfig ) {

	'use strict';

	window.JetEngineCPTList = new Vue( {
		el: '#jet_cpt_list',
		template: '#jet-cpt-list',
		data: {
			errorNotices: [],
			editLink: JetEngineCPTListConfig.edit_link,
			showDeleteDialog: false,
			deletedItem: {},
			showTypes: 'jet-engine',
			builtInTypes: JetEngineCPTListConfig.built_in_types
		},
		computed: {
			itemsList: function() {
				var result = [];

				if ( 'jet-engine' === this.showTypes ) {
					result = JetEngineCPTListConfig.engine_types;
				} else {
					result = JetEngineCPTListConfig.built_in_types;
				}

				return result;
			},
		},
		methods: {
			switchType: function() {
				if ( 'jet-engine' === this.showTypes ) {
					this.showTypes = 'built-in';
				} else {
					this.showTypes = 'jet-engine';
				}
			},
			deleteItem: function( item ) {
				this.deletedItem      = item;
				this.showDeleteDialog = true;
			},
			getEditLink: function( id, slug ) {

				var editLink = this.editLink.replace( /%id%/, id );

				if ( 'built-in' === this.showTypes ) {
					editLink += '&edit-type=built-in&post-type=' + slug;
				}

				return editLink;

			}
		}
	} );

})( jQuery, window.JetEngineCPTListConfig );
