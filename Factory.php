<?php
namespace mimosafa\WP\CoreRepository;
/**
 * WordPress core repository class factory.
 *
 * @access public
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Factory {

	/**
	 * Repository real name prefix.
	 *
	 * @var string
	 */
	private $prefix = '';

	/**
	 * Default arguments.
	 *
	 * @var array
	 */
	private $defaults = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param  string       $context
	 * @param  array|string $defaults
	 */
	public function __construct( $context = null, $defaults = [] ) {
		if ( $context ) {
			if ( ! is_string( $context ) || ! @preg_match( '/\A[a-z][a-z_]*\z/', $context ) ) {
				throw new \Exception( 'Invalid argument.' );
			}
			$this->prefix = rtrim( $context, '_' ) . '_';
		}
		if ( $defaults ) {
			$this->defaults = wp_parse_args( $defaults );
		}
	}

	public function set_defaults( $defaults ) {
		$this->defaults = wp_parse_args( $defaults, $this->defaults );
	}

	public function reset_defaults( $defaults = [] ) {
		$this->defaults = wp_parse_args( $defaults );
	}

	/**
	 * Create post type repository class.
	 *
	 * @access public
	 *
	 * @param  string       $name
	 * @param  array|string $args
	 */
	public function create_post_type( $name, $args = [] ) {
		$this->arguments( $name, $args );
		return PostType::init( $name, $args );
	}

	/**
	 * Create taxonomy repository class.
	 *
	 * @access public
	 *
	 * @param  string       $name
	 * @param  array|string $args
	 */
	public function create_taxonomy( $name, $args = [] ) {
		$this->arguments( $name, $args );
		return Taxonomy::init( $name, $args );
	}

	/**
	 * Merge default arguments & create repository real name.
	 *
	 * @access private
	 *
	 * @param  string $name
	 * @param  array  &$args
	 */
	private function arguments( $name, &$args ) {
		$args = wp_parse_args( $args, $this->defaults );
		if ( ! isset( $args['alias'] ) || ! filter_var( $args['alias'] ) ) {
			$args['alias'] = $name;
		}
		$args['alias'] = $this->prefix . $args['alias'];
	}

}
