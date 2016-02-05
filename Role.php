<?php
namespace mimosafa\WP\CoreRepository;

class Role implements CoreRepository {

	private $name;
	private $args;

	private static $defaults = [];

	private static $instances = [];

	public static function init( $name, $args = [] ) {
		if ( isset( self::$instances[$name] ) ) {
			throw new \Exception( 'Same name ' . __CLASS__ . ' instance exists.' );
		}
		return self::$instances[$name] = new self( $name, $args );
	}

	public static function instance( $name ) {
		return isset( self::$instances[$name] ) ? self::$instances[$name] : null;
	}

	private function __construct( $name, $args = [] ) {
		//
	}

}
