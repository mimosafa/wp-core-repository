<?php
namespace mimosafa\WP\CoreRepository;

class Factory {

	private $prefix = '';

	public function __construct( $context = '' ) {
		if ( $context ) {
			if ( ! is_string( $context ) || @preg_match( '/[^a-z0-9_]/', $context ) ) {
				throw new \Exception( 'Invalid argument.' );
			}
			$this->prefix = rtrim( $context, '_' ) . '_';
		}
	}

	public function create_post_type( $name, $args = [] ) {
		$this->real_name( $name, $args );
		return PostType::init( $name, $args );
	}

	public function create_taxonomy( $name, $args = [] ) {
		$this->real_name( $name, $args );
		return Taxonomy::init( $name, $args );
	}

	private function real_name( $name, &$args ) {
		$args = wp_parse_args( $args );
		if ( ! isset( $args['alias'] ) || ! filter_var( $args['alias'] ) ) {
			$args['alias'] = $name;
		}
		$args['alias'] = $this->prefix . $args['alias'];
	}

}
