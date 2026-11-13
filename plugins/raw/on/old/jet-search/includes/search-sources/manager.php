<?php
/**
 * Search sources manager
 */
namespace Jet_Search\Search_Sources;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	private $_sources = array();

	public function __construct() {
		add_action( 'init', array( $this, 'register_search_sources' ), 99 );
	}

	public function register_search_sources() {

		require $this->component_path( 'base.php' );
		require $this->component_path( 'source-terms.php' );
		require $this->component_path( 'source-users.php' );

		$this->register_source( new Terms() );
		$this->register_source( new Users() );

		do_action( 'jet-search/sources/register', $this );
	}

    public function component_path( $relative_path = '' ) {
		return Jet_Search()->plugin_path( 'includes/search-sources/' . $relative_path );
	}

    /**
	 * Register search sources
	 *
	 * @param  [type] $instance [description]
	 * @return [type]           [description]
	 */
	public function register_source( $instance ) {
		$this->_sources[ $instance->get_name() ] = $instance;
	}

    public function get_sources() {
		return $this->_sources;
	}

	public function get_source( $source_name ) {
		return isset( $this->_sources[ $source_name ] ) ? $this->_sources[ $source_name ] : false;
	}
}
