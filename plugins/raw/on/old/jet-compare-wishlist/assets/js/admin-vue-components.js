'use strict';

let jetCWSettinsMixin = {
	data: function() {
		return {
			pageOptions: window.jetCWSettingsConfig.settingsData,
			preparedOptions: {},
			savingStatus: false,
			ajaxSaveHandler: null,
		};
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
				path: window.jetCWSettingsConfig.settingsApiUrl,
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
	},
	created: function() {
		if ( ! this.pageOptions.wishlist_store_type.value || 'false' === this.pageOptions.wishlist_store_type.value ) {
			this.pageOptions.wishlist_store_type.value = 'session';
		}

		if ( ! this.pageOptions.compare_store_type.value || 'false' === this.pageOptions.compare_store_type.value ) {
			this.pageOptions.compare_store_type.value = 'session';
		}
	}
}

Vue.component( 'jet-cw-compare-settings', {
	template: '#jet-dashboard-jet-cw-compare-settings',
	mixins: [ jetCWSettinsMixin ],
} );

Vue.component( 'jet-cw-wishlist-settings', {
	template: '#jet-dashboard-jet-cw-wishlist-settings',
	mixins: [ jetCWSettinsMixin ],
} );

Vue.component( 'jet-cw-avaliable-addons', {
	template: '#jet-dashboard-jet-cw-avaliable-addons',
	mixins: [ jetCWSettinsMixin ],
} );
