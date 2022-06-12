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
		computed: {
			slugsList: function() {
				var result = [];

				for ( var i = 0; i < this.itemsList.length; i++ ) {
					result.push( this.itemsList[i].slug );
				}

				return result;
			}
		},
		mounted: function() {

			var self = this;

			wp.apiFetch( {
				method: 'get',
				path: JetEngineCPTListConfig.api_path,
			} ).then( function( response ) {

				if ( response.success && response.data ) {
					self.itemsList = response.data;
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
			copyItem: function( item ) {

				if ( !item ) {
					return;
				}

				var self = this,
					itemData = JSON.parse( JSON.stringify( item ) ),
					newSlug = itemData.slug + '_copy';

				itemData.slug = -1 === this.slugsList.indexOf( newSlug ) ? newSlug : newSlug + '_' + Math.floor( ( Math.random() * 99 )  + 1 );
				itemData.labels.name = itemData.labels.name + ' (Copy)';

				wp.apiFetch( {
					method: 'post',
					path: JetEngineCPTListConfig.api_path_add,
					data: {
						general_settings: {
							name: itemData.labels.name,
							slug: itemData.slug,
							object_type: itemData.object_type,
							show_edit_link: itemData.show_edit_link,
						},
						labels: itemData.labels,
						advanced_settings: itemData.args,
						meta_fields: itemData.meta_fields,
					}
				} ).then( function( response ) {

					if ( response.success && response.item_id ) {

						itemData.id = response.item_id

						self.itemsList.unshift( itemData );

						self.$CXNotice.add( {
							message: JetEngineCPTListConfig.notices.copied,
							type: 'success',
						} );

					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 7000,
								} );


							} );
						}
					}
				} ).catch( function( response ) {

					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

				} );
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
