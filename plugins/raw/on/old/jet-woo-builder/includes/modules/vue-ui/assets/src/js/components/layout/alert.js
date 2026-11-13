import { oneOf } from '../../utils/assist';
import { checkConditions } from '../../mixins/check-conditions';

const Alert = {
	name: 'cx-vui-alert',
	template: '#cx-vui-alert',
	mixins: [ checkConditions ],
	props: {
		value: {
			type: Boolean,
			default: true
		},
		type: {
			validator ( value ) {
				return oneOf( value, [ 'success', 'danger', 'warning', 'info', 'custom' ] );
			},
			default: 'info'
		},
		dismissible: {
			type: Boolean,
			default: false
		},
		elementId: {
			type: String
		},
		conditions: {
			type: Array,
			default() {
				return [];
			}
		},
	},
	data() {
		return {
			baseClass: 'cx-vui-alert',
			show: this.value,
		};
	},
	watch: {
		value( val ) {
			this.show = val;
		}
	},
	computed: {
		classesList() {
			let classesList = [
				this.baseClass,
				this.baseClass + '--' + this.type,
			];

			if ( this.dismissible ) {
				classesList.push( this.baseClass + '--dismissible' );
			}

			return classesList;
		},
	},
	methods: {
		handleDismiss() {
			this.show = false;
			this.$emit( 'input', false );
			this.$emit( 'dismissed', event );
		}
	},
};

export default Alert;