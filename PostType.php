<?php
namespace mimosafa\WP\CoreRepository;

class PostType extends RewritableRepository implements CoreRepository {

	protected static $defaults = [
		'labels'               => [],
		'description'          => '',
		'public'               => false,
		'hierarchical'         => false,
		'exclude_from_search'  => null,
		'publicly_queryable'   => null,
		'show_ui'              => null,
		'show_in_menu'         => null,
		'show_in_nav_menus'    => null,
		'show_in_admin_bar'    => null,
		'menu_position'        => null,
		'menu_icon'            => null,
		'capability_type'      => null,
		'capabilities'         => [],
		'map_meta_cap'         => null,
		'supports'             => [ 'title', 'editor' ],
		'register_meta_box_cb' => null,
		'taxonomies'           => [],
		'has_archive'          => false,
		'rewrite'              => true,
		'query_var'            => true,
		'can_export'           => true,
		'delete_with_user'     => null,
	];

	protected static $rewrite_defaults = [
		'slug'       => '',
		'with_front' => true,
		'pages'      => true,
		'feeds'      => null,
		'ep_mask'    => null
	];

	protected static $label_formats = [
		'name'                  => null,
		'singular_name'         => null,
		'add_new'               => null,
		'add_new_item'          => [ 'singular', 'Add New %s' ],
		'edit_item'             => [ 'singular', 'Edit %s' ],
		'new_item'              => [ 'singular', 'New %s' ],
		'view_item'             => [ 'singular', 'View %s' ],
		'search_items'          => [ 'plural', 'Search %s' ],
		'not_found'             => [ 'plural', 'No %s found.' ],
		'not_found_in_trash'    => [ 'plural', 'No %s found in Trash.' ],
		'all_items'             => [ 'plural', 'All %s' ],
		'parent_item_colon'     => [ 'singular', 'Parent %s:' ],
		'uploaded_to_this_item' => [ 'singular', 'Uploaded to this %s' ],
		'featured_image'        => null,
		'set_featured_image'    => [ 'featured_image', 'Set %s' ],
		'remove_featured_image' => [ 'featured_image', 'Remove %s' ],
		'use_featured_image'    => [ 'featured_image', 'Use as %s' ],
		'archives'              => [ 'singular', '%s Archives' ],
		'insert_into_item'      => [ 'singular', 'Insert into %s' ],
		'filter_items_list'     => [ 'plural', 'Filter %s list' ],
		'items_list_navigation' => [ 'plural', '%s list navigation' ],
		'items_list'            => [ 'plural', '%s list' ]
	];

	public function __set( $name, $var ) {
		if ( substr( $name, 0, 8 ) === 'support_' ) {
			/**
			 * Set|Unset post type support.
			 */
			static $supports = [
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'page-attributes',
				'post-formats'
			];
			$name = str_replace( '_', '-', substr( $name, 8 ) );
			if ( in_array( $name, $supports, true ) ) {
				if ( ! is_array( $this->args['supports'] ) ) {
					if ( filter_var( $this->args['supports'], \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE ) === false ) {
						$this->args['supports'] = [];
					} else {
						$this->args['supports'] = preg_split( '/[\s,]+/', $this->args['supports'] );
					}
				}
				if ( $var === true && ! in_array( $name, $this->args['supports'], true ) ) {
					$this->args['supports'][] = $name;
				}
				else if ( $var === false && in_array( $name, $this->args['supports'], true ) ) {
					foreach ( $this->args['supports'] as $i => $support ) {
						if ( $support === $name ) {
							unset( $this->args['supports'][$i] );
						}
					}
				}
			}
		}
		else {
			parent::__set( $name, $var );
		}
	}

	public function regulation() {
		if ( post_type_exists( $this->real_name ) ) {
			return;
		}
		/**
		 * @var array          &$labels
		 * @var string         &$description
		 * @var boolean        &$public
		 * @var boolean        &$hierarchical
		 * @var boolean        &$exclude_from_search
		 * @var boolean        &$publicly_queryable
		 * @var boolean        &$show_ui
		 * @var boolean|string &$show_in_menu
		 * @var boolean        &$show_in_nav_menus
		 * @var boolean        &$show_in_nav_menus
		 * @var boolean        &$show_in_admin_bar
		 * @var int            &$menu_position
		 * @var string         &$menu_icon
		 * @var string|array   &$capability_type
		 * @var array          &$capabilities
		 * @var boolean        &$map_meta_cap
		 * @var array          &$supports
		 * @var callable       &$register_meta_box_cb
		 * @var array          &$taxonomies
		 * @var boolean        &$has_archive
		 * @var boolean|array  &$rewrite
		 * @var boolean|string &$query_var
		 * @var boolean        &$can_export
		 * @var boolean        &$delete_with_user
		 */
		extract( $this->args, \EXTR_REFS );

		/**
		 * Regulate arguments.
		 */
		$public        = filter_var( $public,        \FILTER_VALIDATE_BOOLEAN );
		$hierarchical  = filter_var( $hierarchical,  \FILTER_VALIDATE_BOOLEAN );
		$has_archive   = filter_var( $has_archive,   \FILTER_VALIDATE_BOOLEAN );
		$can_export    = filter_var( $can_export,    \FILTER_VALIDATE_BOOLEAN );
		$menu_position = filter_var( $menu_position, \FILTER_VALIDATE_INT,     [ 'options' => [ 'default' => null ] ] );
		$can_export    = filter_var( $can_export,    \FILTER_VALIDATE_BOOLEAN, [ 'options' => [ 'default' => true ] ] );
		if ( isset( $exclude_from_search ) ) {
			$exclude_from_search = filter_var( $exclude_from_search, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $publicly_queryable ) ) {
			$publicly_queryable = filter_var( $publicly_queryable, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_in_nav_menus ) ) {
			$show_in_nav_menus = filter_var( $show_in_nav_menus, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_ui ) ) {
			$show_ui = filter_var( $show_ui, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_in_menu ) && ! ( is_string( $show_in_menu ) && preg_match( '/\w+(\.php){1}\w*/', $show_in_menu ) ) ) {
			$show_in_menu = filter_var( $show_in_menu, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $show_in_admin_bar ) ) {
			$show_in_admin_bar = filter_var( $show_in_admin_bar, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $map_meta_cap ) ) {
			$map_meta_cap = filter_var( $map_meta_cap, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( isset( $delete_with_user ) ) {
			$delete_with_user = filter_var( $delete_with_user, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
		}
		if ( is_array( $description ) || is_object( $description ) ) {
			$description = '';
		}
		if ( ! is_array( $taxonomies ) ) {
			$taxonomies = [];
		}
		if ( isset( $register_meta_box_cb ) ) {
			if ( ! is_string( $register_meta_box_cb ) || ! preg_match( '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $register_meta_box_cb ) ) {
				$register_meta_box_cb = null;
			}
		}
		if ( $publicly_queryable || ( ! isset( $publicly_queryable ) && $public ) ) {
			if ( isset( $permalink_epmask ) ) {
				$permalink_epmask = filter_var( $permalink_epmask, \FILTER_VALIDATE_INT, [ 'options' => [ 'default' => null ] ] );
			}
			if ( filter_var( $rewrite, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE ) !== false ) {
				$rewrite = wp_parse_args( is_array( $rewrite ) ? $rewrite : [], self::$rewrite_defaults );
				if ( ! $rewrite['slug'] || ! is_string( $rewrite['slug'] ) ) {
					$rewrite['slug'] = $this->name !== $this->real_name ? $this->name : $this->real_name;
				}
				$rewrite['with_front'] = filter_var( $rewrite['with_front'], \FILTER_VALIDATE_BOOLEAN );
				$rewrite['pages']   = filter_var( $rewrite['pages'],   \FILTER_VALIDATE_BOOLEAN );
				$rewrite['feeds']   = filter_var( $rewrite['feeds'],   \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
				$rewrite['ep_mask'] = filter_var( $rewrite['ep_mask'], \FILTER_VALIDATE_INT, [ 'options' => [ 'default' => null ] ] );
			} else {
				$rewrite = false;
			}
			$query_var = filter_var( $query_var, \FILTER_VALIDATE_BOOLEAN );
		} else {
			$rewrite = $query_var = false;
		}
		if ( filter_var( $supports, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE ) !== false && $supports !== [] ) {
			if ( ! is_array( $supports ) ) {
				$supports = is_string( $supports ) ? preg_split( '/[\s,]+/', $supports ) : [];
			}
			$supports = array_unique( $supports );
		} else {
			$supports = false;
		}
		if ( ! $capability_type || is_object( $capability_type ) || ( is_array( $capability_type ) && count( $capability_type ) !== 2 ) ) {
			unset( $this->args['capability_type'] );
		}
		else if ( is_array( $capability_type ) ) {
			$capability_type = array_values( $capability_type );
			if ( ! is_string( $capability_type[0] ) || ! is_string( $capability_type[1] ) ) {
				unset( $this->args['capability_type'] );
			}
			else if ( $capability_type[0] === $capability_type[1] ) {
				unset( $this->args['capability_type'] );
			}
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
		self::generateLabels( $labels );

		self::$post_types[$this->real_name] = [ 'post_type' => $this->real_name, 'args' => $this->args ];
	}

	/**
	 * Create post type labels.
	 *
	 * @access private
	 *
	 * @param  array &$labels
	 */
	private static function generateLabels( &$labels ) {
		$singular = $labels['singular_name'];
		$plural   = $labels['name'];
		$featured_image = isset( $labels['featured_image'] ) && filter_var( $labels['featured_image'] ) ? $labels['featured_image'] : null;
		foreach ( self::$label_formats as $key => $format ) {
			if ( ! isset( $labels[$key] ) || ! filter_var( $labels[$key] ) ) {
				if ( is_array( $format ) && ( $string = ${$format[0]} ) ) {
					$labels[$key] = esc_html( sprintf( __( $format[1], 'wp-mimosafa-libs' ), $string ) );
				}
			}
		}
	}

	protected static function validateRealName( $str ) {
		return parent::validateRealName( $str ) && strlen( $str ) < 21;
	}

}
