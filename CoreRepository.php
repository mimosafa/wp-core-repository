<?php
namespace mimosafa\WP\CoreRepository;

interface CoreRepository {
	public static function init( $name, $args );
	public static function instance( $name );
}

/**
 * Load textdomain.
 */
if ( defined( 'ABSPATH' ) ) {
	load_textdomain(
		'mimosafa-wp-core-repository',
		__DIR__ . '/languages/wp-core-repository-' . get_locale() . '.mo'
	);
}
