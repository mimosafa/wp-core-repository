<?php
namespace mimosafa\WP\CoreRepository;

abstract class RewritableRepository {

	protected $name;
	protected $real_name;
	protected $args;

	protected static $instances  = [];
	protected static $real_names = [];

	protected static $post_types = [];
	protected static $taxonomies = [];

	protected static $builtinPostTypes = [
		'post' => 'post',
		'page' => 'page',
	];

	protected static $builtinTaxonomies = [
		'category' => 'category',
		'post_tag' => 'tag',
	];

	abstract public function regulation();

	public static function init( $name, $args = [] ) {
		if ( get_called_class() === __CLASS__ ) {
			throw new \Exception( 'Invalid operation.' );
		}
		if ( isset( self::$instances[$name] ) ) {
			throw new \Exception( 'Same name ' . __CLASS__ . ' instance exists.' );
		}
		return self::$instances[$name] = new static( $name, $args );
	}

	public static function instance( $name ) {
		return isset( self::$instances[$name] ) ? self::$instances[$name] : null;
	}

	/**
	 * @uses wp_parse_args()
	 */
	protected function __construct( $name, $args = [] ) {
		if ( ! static::validateName( $name ) ) {
			throw new \Exception( 'Invalid.' );
		}
		$args = wp_parse_args( $args, static::$defaults );
		if ( isset( $args['alias'] ) ) {
			$real_name = $args['alias'];
			unset( $args['alias'] );
		} else {
			$real_name = $name;
		}
		if ( ! static::validateRealName( $real_name ) ) {
			throw new \Exception( 'Invalid.' );
		}
		$this->name = $name;
		$this->real_name = $real_name;
		$this->args = $args;

		self::$real_names[$real_name] = $name;

		add_action( 'init', [ $this, 'regulation' ], 0 );

		static $done = false;
		if ( ! $done ) {
			add_action( 'init', [ $this, 'register_post_types' ], 1 );
			add_action( 'init', [ $this, 'register_taxonomies' ], 1 );
			$done = true;
		}
	}

	public function __set( $name, $var ) {
		$this->args[$name] = $var;
	}

	public function register_taxonomies() {
		if ( self::$taxonomies ) {
			foreach ( self::$taxonomies as $tx ) {
				/**
				 * @var string $taxonomy
				 * @var array  $object_type
				 * @var array  $args
				 */
				extract( $tx, EXTR_OVERWRITE );

				register_taxonomy( $taxonomy, $object_type, $args );
				/**
				 * Built-in object types
				 */
				if ( $object_type ) {
					foreach ( (array) $object_type as $object ) {
						if ( post_type_exists( $object ) ) {
							register_taxonomy_for_object_type( $taxonomy, $object );
						}
					}
				}
			}
		}
	}

	public function register_post_types() {
		if ( self::$post_types ) {

			/**
			 * Theme support: post-thumbnails
			 *
			 * @var boolean
			 */
			static $thumbnail_supported;
			if ( ! isset( $thumbnail_supported ) ) {
				$thumbnail_supported = current_theme_supports( 'post-thumbnails' );
			}

			/**
			 * Theme support: post-formats
			 *
			 * @var boolean
			 */
			static $post_formats_supported;
			if ( ! isset( $post_formats_supported ) ) {
				$post_formats_supported = current_theme_supports( 'post-formats' );
			}

			foreach ( self::$post_types as $pt ) {
				/**
				 * @var string $post_type
				 * @var array  $args
				 */
				extract( $pt, EXTR_OVERWRITE );
				/**
				 * Taxonomies
				 */
				if ( self::$taxonomies ) {
					$taxonomies = [];
					foreach ( self::$taxonomies as $tx ) {
						if ( in_array( $post_type, $tx['object_type'], true ) ) {
							$taxonomies[] = $tx['taxonomy'];
						}
					}
					if ( $taxonomies ) {
						if ( ! isset( $args['taxonomies'] ) || ! is_array( $args['taxonomies'] ) ) {
							$args['taxonomies'] = array_unique( array_merge( $args['taxonomies'], $taxonomies ) );
						}
					}
				}

				if ( ! $thumbnail_supported && isset( $args['supports'] ) && in_array( 'thumbnail', (array) $args['supports'], true ) ) {
					add_theme_support( 'post-thumbnails' );
					$thumbnail_supported = true;
				}
				if ( ! $post_formats_supported && isset( $args['supports'] ) && in_array( 'post-formats', (array) $args['supports'], true ) ) {
					add_theme_support( 'post-formats' );
					$post_formats_supported = true;
				}

				register_post_type( $post_type, $args );
			}
		}
	}

	protected static function validateName( $str ) {
		if ( isset( self::$real_names[$str] ) ) {
			throw new \Exception( "The repository name \"{$str}\" is already used as existing repository's real name." );
		}
		return is_string( $str ) && @preg_match( '/[a-z]+[a-z0-9_\-]+/', $str );
	}

	protected static function validateRealName( $str ) {
		if ( in_array( $str, self::$real_names, true ) ) {
			throw new \Exception( "The repository real name \"{$str}\" is already used as existing repository's name." );
		}
		return self::validateName( $str );
	}

	protected static function labelize( $string ) {
		return trim( ucwords( str_replace( [ '-', '_' ], ' ', $string ) ) );
	}

}
