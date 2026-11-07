(function( $, settingsPageConfig ) {

	'use strict';

	Vue.config.devtools = true;

	window.JetCWSettingsPage = new Vue( {
		el: '#jet-cw-settings-page',

		data: {
			pageOptions: settingsPageConfig.settingsData,
			preparedOptions: {},
			savingStatus: false,
			ajaxSaveHandler: null
		},

		mounted: function() {

			this.$el.className = 'is-mounted';
		},

		watch: {
			pageOptions: {
				handler( options ) {
					let prepared = {};

					for ( let option in options ) {

						if ( options.hasOwnProperty( option ) ) {
							prepared[ option ] = options[option]['value'];
						}
					}

					this.preparedOptions = prepared;

					this.saveOptions();
				},
				deep: true
			}
		},

		methods: {
			saveOptions: function() {

				var self = this;

				self.savingStatus = true;

				wp.apiFetch( {
					method: 'post',
					path: settingsPageConfig.settingsApiUrl,
					data: self.preparedOptions
				} ).then( function( response ) {

					self.savingStatus = false;

					if ( 'success' === response.status ) {
						self.$CXNotice.add( {
							message: response.message,
							type: 'success',
							duration: 3000,
						} );
					}

					if ( 'error' === response.status ) {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 3000,
						} );
					}
					
				} ).catch( function( response ) {
					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 3000,
					} );
				} );

			},
		}
	} );

})( jQuery, window.JetCWSettingsPageConfig );
