<?php
namespace mimosafa\WP\CoreRepository;

abstract class CoreRepositoryRewritable {

	protected $name;
	protected $real_name;
	protected $args;

	protected static $instances  = [];
	protected static $real_names = [];

	protected static $post_types = [];
	protected static $taxonomies = [];

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
		if ( isset( self::$instances[$name] ) ) {
			return self::$instances[$name];
		}
		if ( isset( self::$real_names[$name] ) ) {
			return self::$instances[self::$real_names[$name]];
		}
	}

	/**
	 * @uses wp_parse_args()
	 */
	protected function __construct( $name, $args = [] ) {
		if ( ! static::validateName( $name ) ) {
			throw new \Exception( 'Invalid name parameter.' );
		}
		if ( isset( self::$real_names[$name] ) ) {
			throw new \Exception( "The name \"{$name}\" is already used as existing repository's alias." );
		}
		$args = wp_parse_args( $args, static::$defaults );
		if ( isset( $args['alias'] ) ) {
			$real_name = $args['alias'];
			unset( $args['alias'] );
		} else {
			$real_name = $name;
		}
		if ( ! static::validateRealName( $real_name ) ) {
			throw new \Exception( 'Invalid alias parameter.' );
		}
		if ( in_array( $real_name, self::$real_names, true ) ) {
			throw new \Exception( "The alias \"{$real_name}\" is already used as existing repository's name." );
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
		if ( substr( $name, 0, 8 ) === 'rewrite_' ) {
			/**
			 * Set rewrite arguments.
			 */
			$name = substr( $name, 8 );
			if ( array_key_exists( $name, static::$rewrite_defaults ) ) {
				if ( ! is_array( $this->args['rewrite'] ) ) {
					$this->args['rewrite'] = [];
				}
				$this->args['rewrite'][$name] = $var;
			}
		}
		else if ( substr( $name, 0, 6 ) === 'label_' ) {
			/**
			 * Set label arguments.
			 */
			$name = substr( $name, 6 );
			if ( array_key_exists( $name, static::$label_formats ) ) {
				if ( ! is_array( $this->args['labels'] ) ) {
					$this->args['labels'] = [];
				}
				$this->args['labels'][$name] = $var;
			}
		}
		else {
			$this->args[$name] = $var;
		}
	}

	public function __get( $name ) {
		if ( in_array( $name, [ 'name', 'real_name' ] ) ) {
			return $this->{$name};
		}
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

	public static function validateName( $str ) {
		return is_string( $str ) && @preg_match( '/[a-z]+[a-z0-9_\-]+/', $str );
	}

	public static function validateRealName( $str ) {
		return self::validateName( $str );
	}

	protected static function labelize( $string ) {
		return trim( ucwords( str_replace( [ '-', '_' ], ' ', $string ) ) );
	}

}
