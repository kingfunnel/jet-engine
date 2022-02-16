(function( $, JetEngineCPTListConfig ) {

	'use strict';

	window.JetEngineCPTList = new Vue( {
		el: '#jet_cpt_list',
		template: '#jet-cpt-list',
		data: {
			itemsList: [],
			errorNotices: [],
			editLink: JetEngineCPTListConfig.edit_link,
			showDeleteDialog: false,
			deletedItem: {},
		},
		mounted: function() {

			var self = this;

			wp.apiFetch( {
				method: 'get',
				path: JetEngineCPTListConfig.api_path,
			} ).then( function( response ) {

				if ( response.success && response.data ) {
					for ( var itemID in response.data ) {
						var item = response.data[ itemID ];
						self.itemsList.push( item );
					}
				} else {
					if ( response.notices.length ) {
						response.notices.forEach( function( notice ) {
							self.errorNotices.push( notice.message );
						} );
					}
				}
			} ).catch( function( e ) {
				self.errorNotices.push( e.message );
			} );
		},
		methods: {
			verboseItemInfo: function( item ) {

				var result = '';

				if ( 'post' === item.args.object_type ) {
					if ( item.args.allowed_post_type && item.args.allowed_post_type.length ) {
						result = item.args.allowed_post_type.join( ', ' );
					}
				} else {
					if ( item.args.allowed_tax && item.args.allowed_tax.length ) {
						result = item.args.allowed_tax.join( ', ' );
					}
				}

				return result;
			},
			deleteItem: function( item ) {
				this.deletedItem      = item;
				this.showDeleteDialog = true;
			},
			getEditLink: function( id ) {
				return this.editLink.replace( /%id%/, id );
			},
		}
	} );

})( jQuery, window.JetEngineCPTListConfig );
