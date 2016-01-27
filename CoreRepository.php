<?php
namespace mimosafa\WP\CoreRepository;

interface CoreRepository {
	public static function init( $name, $args );
	public static function instance( $name );
}
