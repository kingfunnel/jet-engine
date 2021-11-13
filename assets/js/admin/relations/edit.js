(function( $, JetEngineCPTConfig ) {

	'use strict';

	window.JetEngineCPT = new Vue( {
		el: '#jet_cpt_form',
		template: '#jet-cpt-form',
		data: {
			generalSettings: JetEngineCPTConfig.general_settings,
			advancedSettings: JetEngineCPTConfig.advanced_settings,
			postTypes: JetEngineCPTConfig.post_types,
			relationsTypes: JetEngineCPTConfig.relations_types,
			buttonLabel: JetEngineCPTConfig.edit_button_label,
			isEdit: JetEngineCPTConfig.item_id,
			helpLinks: JetEngineCPTConfig.help_links,
			existingRelations: JetEngineCPTConfig.existing_relations,
			showDeleteDialog: false,
			saving: false,
			errors: {
				name: false,
				slug: false,
			},
			errorNotices: [],
		},
		mounted: function() {

			var self = this;

			if ( JetEngineCPTConfig.item_id ) {

				wp.apiFetch( {
					method: 'get',
					path: JetEngineCPTConfig.api_path_get + JetEngineCPTConfig.item_id,
				} ).then( function( response ) {

					if ( response.success && response.data ) {

						self.generalSettings   = response.data.general_settings;
						self.advancedSettings  = response.data.advanced_settings;

					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 15000,
								} );

								//self.errorNotices.push( notice.message );
							} );
						}
					}
				} );

			}
		},
		computed: {
			availableParentRelations: function() {
				var result = [
					{
						value: '',
						label: 'Select...',
					}
				];

				for ( var relationKey in this.existingRelations ) {
					if ( this.generalSettings.post_type_1 === this.existingRelations[ relationKey ].post_type_2 ) {
						result.push( {
							value: relationKey,
							label: this.existingRelations[ relationKey ].name,
						} );
					}
				}

				return result;
			},
		},
		methods: {
			handleDeletionError: function( errors ) {

				var self = this;

				errors.forEach( function( error ) {
					self.errorNotices.push( error.message );
				} );

			},
			handleFocus: function( where ) {

				if ( this.errors[ where ] ) {
					this.$set( this.errors, where, false );
					this.$CXNotice.close( where );
					//this.errorNotices.splice( 0, this.errorNotices.length );
				}

			},
			save: function() {

				var self      = this,
					hasErrors = false,
					path      = JetEngineCPTConfig.api_path_edit;

				if ( JetEngineCPTConfig.item_id ) {
					path += JetEngineCPTConfig.item_id;
				}

				if ( hasErrors ) {
					return;
				}

				self.saving = true;

				wp.apiFetch( {
					method: 'post',
					path: path,
					data: {
						general_settings: self.generalSettings,
						labels: self.labels,
						advanced_settings: self.advancedSettings,
						meta_fields: self.metaFields,
					}
				} ).then( function( response ) {

					if ( response.success ) {
						if ( JetEngineCPTConfig.redirect ) {
							window.location = JetEngineCPTConfig.redirect.replace( /%id%/, response.item_id );
						} else {

							self.$CXNotice.add( {
								message: JetEngineCPTConfig.notices.success,
								type: 'success',
							} );

							self.saving = false;
						}
					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 7000,
								} );

								//self.errorNotices.push( notice.message );
							} );
						}
						self.saving = false;
					}
				} ).catch( function( response ) {
					//self.errorNotices.push( response.message );

					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

					self.saving = false;
				} );

			},
		}
	} );

})( jQuery, window.JetEngineCPTConfig );
