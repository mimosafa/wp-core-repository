<?php
namespace mimosafa\WP\CoreRepository;

class Taxonomy extends RewritableRepository implements CoreRepository {

	protected $object_type = [];

	protected static $defaults = [
		'labels'                => [],
		'description'           => '',
		'public'                => true,
		'hierarchical'          => false,
		'show_ui'               => null,
		'show_in_menu'          => null,
		'show_in_nav_menus'     => null,
		'show_tagcloud'         => null,
		'show_in_quick_edit'    => null,
		'show_admin_column'     => false,
		'meta_box_cb'           => null,
		'capabilities'          => [],
		'rewrite'               => true,
		'query_var'             => true,
		'update_count_callback' => '',
	];

	private static $rewrite_defaults = [
		'slug'         => '',
		'with_front'   => true,
		'hierarchical' => false,
		'ep_mask'      => EP_NONE
	];

	protected static $label_formats = [
		// Common
		'name'          => null,
		'singular_name' => null,
		'search_items'  => [ 'plural',   'Search %s' ],
		'all_items'     => [ 'plural',   'All %s' ],
		'edit_item'     => [ 'singular', 'Edit %s' ],
		'view_item'     => [ 'singular', 'View %s' ],
		'update_item'   => [ 'singular', 'Update %s' ],
		'add_new_item'  => [ 'singular', 'Add New %s' ],
		'new_item_name' => [ 'singular', 'New %s Name' ],
		'not_found'     => [ 'plural',   'No %s found.' ],
		'no_terms'      => [ 'plural',   'No %s' ],
		// No-hierarchical
		'popular_items'              => [ 'singular', 'Popular %s' ],
		'separate_items_with_commas' => [ 'plural',   'Separate %s with commas' ],
		'add_or_remove_items'        => [ 'plural',   'Add or remove %s' ],
		'choose_from_most_used'      => [ 'plural',   'Choose from the most used %s' ],
		// Hierarchical
		'parent_item'       => [ 'singular', 'Parent %s' ],
		'parent_item_colon' => [ 'singular', 'Parent %s:' ],
	];

	protected function __construct( $name, $args = [] ) {
		parent::__construct( $name, $args );
		if ( isset( $this->args['object_type'] ) ) {
			if ( is_string( $this->args['object_type'] ) ) {
				$this->args['object_type'] = preg_split( '/[\s,]+/', $this->args['object_type'] );
			}
			if ( $this->args['object_type'] && is_array( $this->args['object_type'] ) ) {
				$this->object_type = array_values( $this->args['object_type'] );
			}
			unset( $this->args['object_type'] );
			$this->object_type = array_unique( $this->object_type );
		}
	}

	public function regulation() {
		if ( taxonomy_exists( $this->real_name ) ) {
			return;
		}
		/**
		 * @var array          &$labels
		 * @var string         &$description
		 * @var boolean        &$public
		 * @var boolean        &$hierarchical
		 * @var boolean        &$show_ui
		 * @var boolean        &$show_in_menu
		 * @var boolean        &$show_in_nav_menus
		 * @var boolean        &$show_tagcloud
		 * @var boolean        &$show_in_quick_edit
		 * @var boolean        &$show_admin_column
		 * @var callable       &$meta_box_cb
		  @var array          &$capabilities
		 * @var boolean|array  &$rewrite
		 * @var boolean|string &$query_var
		 * @var callable       &$update_count_callback
		 * @var array|string   &$object_type
		 */
		extract( $this->args, \EXTR_REFS );

		$public            = filter_var( $public,            \FILTER_VALIDATE_BOOLEAN );
		$hierarchical      = filter_var( $hierarchical,      \FILTER_VALIDATE_BOOLEAN );
		$show_admin_column = filter_var( $show_admin_column, \FILTER_VALIDATE_BOOLEAN );
		if ( isset( $show_ui ) ) {
			$show_ui = filter_var( $show_ui, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_in_menu ) ) {
			$show_in_menu = filter_var( $show_in_menu, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_in_nav_menus ) ) {
			$show_in_nav_menus = filter_var( $show_in_nav_menus, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_tagcloud ) ) {
			$show_tagcloud = filter_var( $show_tagcloud, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_in_quick_edit ) ) {
			$show_in_quick_edit = filter_var( $show_in_quick_edit, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( is_array( $description ) || is_object( $description ) ) {
			$description = '';
		}
		if ( isset( $meta_box_cb ) ) {
			if ( ! is_string( $meta_box_cb ) || ! preg_match( '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $meta_box_cb ) ) {
				$meta_box_cb = null;
			}
		}
		if ( $update_count_callback ) {
			if ( $update_count_callback !== '_update_post_term_count' || $update_count_callback !== '_update_generic_term_count' ) {
				$update_count_callback = '';
			}
		}
		if ( $public ) {
			if ( filter_var( $rewrite, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE ) !== false ) {
				$rewrite = wp_parse_args( is_array( $rewrite ) ? $rewrite : [], self::$rewrite_defaults );
				if ( ! $rewrite['slug'] || ! is_string( $rewrite['slug'] ) ) {
					$rewrite['slug'] = $this->name;
				}
				$rewrite['with_front']   = filter_var( $rewrite['with_front'],   \FILTER_VALIDATE_BOOLEAN );
				$rewrite['hierarchical'] = filter_var( $rewrite['hierarchical'], \FILTER_VALIDATE_BOOLEAN );
				$rewrite['ep_mask'] = filter_var( $rewrite['ep_mask'], \FILTER_VALIDATE_INT, [ 'options' => [ 'default' => EP_NONE ] ] );
			}
			if ( filter_var( $query_var, \FILTER_VALIDATE_BOOLEAN ) !== false ) {
				$query_var = $this->real_name;
			} else {
				$query_var = false;
			}
		} else {
			$rewrite = $query_var = false;
		}
		if ( ! is_array( $labels ) ) {
			$labels = [];
		}
		if ( ! isset( $labels['name'] ) || ! filter_var( $labels['name'] ) ) {
			$labels['name'] = isset( $label ) && filter_var( $label ) ? $label : self::labelize( $this->name );
		}
		if ( ! isset( $labels['singular_name'] ) || ! filter_var( $labels['singular_name'] ) ) {
			$labels['singular_name'] = $labels['name'];
		}
		self::generateLabels( $labels, $hierarchical );

		if ( $this->object_type = array_filter( $this->object_type ) ) {
			$this->object_type = array_unique( $this->object_type, \SORT_REGULAR );
			$this->object_type_regulation();
		}

		self::$taxonomies[$this->real_name] = [ 'taxonomy' => $this->real_name, 'object_type' => $this->object_type, 'args' => $this->args ];
	}

	private static function generateLabels( &$labels ) {
		$singular = $labels['singular_name'];
		$plural   = $labels['name'];
		foreach ( self::$label_formats as $key => $format ) {
			if ( ! isset( $labels[$key] ) || ! filter_var( $labels[$key] ) ) {
				if ( is_array( $format ) && ( $string = ${$format[0]} ) ) {
					$labels[$key] = esc_html( sprintf( __( $format[1], 'wp-mimosafa-libs' ), $string ) );
				}
			}
		}
	}

	private function object_type_regulation() {
		foreach ( $this->object_type as $i => $type ) {
			if ( post_type_exists( $type ) || isset( self::$post_types[$type] ) ) {
				continue;
			}
			else if ( in_array( $type, self::$real_names, true ) ) {
				$real_name = array_search( $type, self::$real_names, true );
				if ( isset( self::$post_types[$real_name] ) ) {
					$this->object_type[$i] = $real_name;
					continue;
				}
			}
			unset( $this->object_type[$i] );
		}
	}

	protected static function validateRealName( $str ) {
		if ( parent::validateRealName( $str ) ) {
			/**
			 * Taxonomy name regulation.
			 *
			 * @see http://codex.wordpress.org/Function_Reference/register_taxonomy#Parameters
			 */
			if ( strlen( $str ) < 33 && ! @preg_match( '/[0-9\-]/', $str ) ) {
				return true;
			}
		}
		return false;
	}

}
