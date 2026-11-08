<?php
namespace PowerpackElements\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class PP_Faq_Schema.
 */
class PP_Faq_Schema {

	/**
	 * FAQ Data
	 *
	 * @var faq_data
	 */
	private $faq_data = [];

	private $widget_data = [];

	private $widget_ids = [];

	public static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		//add_action('wp_head', array($this, 'render_faq_schema'));
		add_filter( 'elementor/frontend/builder_content_data', [ $this, 'grab_faq_data' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'render_faq_schema' ] );
	}

	public function grab_faq_data( $data, $post_id ) {
		$widgets = [];

		pp_get_elementor()->db->iterate_data( $data, function ( $element ) use ( &$widgets ) {
			$type = $this->get_widget_type( $element );
			if ( 'pp-faq' === $type ) {
				array_push( $widgets, $element );
			}
			return $element;
		} );

		if ( ! empty( $widgets ) ) {
			$this->widget_data[ $post_id ] = $widgets;

			foreach ( $widgets as $widget_data ) {
				if ( in_array( $widget_data['id'], $this->widget_ids ) ) {
					continue;
				} else {
					$this->widget_ids[] = $widget_data['id'];
				}
				$widget = pp_get_elementor()->elements_manager->create_element_instance( $widget_data );
				if ( isset( $widget_data['templateID'] ) ) {
					$type = $this->get_global_widget_type( $widget_data['templateID'], 1 );
					$element_class = $type->get_class_name();
					try {
						$widget = new $element_class( $widget_data, [] );
					} catch ( \Exception $e ) {
						return null;
					}
				}
				$settings = $widget->get_settings();
				$enable_schema = $settings['enable_schema'];
				$faq_items = $widget->get_faq_items();

				if ( ! empty( $faq_items ) && 'yes' === $enable_schema ) {
					foreach ( $faq_items as $faqs ) {
						$faq_data = array(
							'@type'          => 'Question',
							'name'           => $faqs['question'],
							'acceptedAnswer' =>
							array(
								'@type' => 'Answer',
								'text'  => $faqs['answer'],
							),
						);
						array_push( $this->faq_data, $faq_data );
					}
				}
			}
		}

		return $data;
	}

	public function render_faq_schema() {
		//$faqs_data = $this->get_faqs_data();
		$faqs_data = $this->faq_data;

		if ( $faqs_data ) {
			$schema_data = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'FAQPage',
				'mainEntity'    => $faqs_data,
			);

			$encoded_data = wp_json_encode( $schema_data );
			?>
			<script type="application/ld+json">
				<?php echo( $encoded_data ); ?>
			</script>
			<?php
		}

		$this->faq_data = [];
	}

	private function get_widget_type( $element ) {
		if ( empty( $element['widgetType'] ) ) {
			$type = $element['elType'];
		} else {
			$type = $element['widgetType'];
		}

		if ( 'global' === $type && ! empty( $element['templateID'] ) ) {
			$type = $this->get_global_widget_type( $element['templateID'] );
		}

		return $type;
	}

	private function get_global_widget_type( $template_id, $return_type = false ) {
		$template_data = pp_get_elementor()->templates_manager->get_template_data( [
			'source'        => 'local',
			'template_id'   => $template_id,
		] );

		if ( is_wp_error( $template_data ) ) {
			return '';
		}

		if ( empty( $template_data['content'] ) ) {
			return '';
		}

		$original_widget_type = pp_get_elementor()->widgets_manager->get_widget_types( $template_data['content'][0]['widgetType'] );

		if ( $return_type ) {
			return $original_widget_type;
		}

		return $original_widget_type ? $template_data['content'][0]['widgetType'] : '';
	}
}

PP_Faq_Schema::instance();
