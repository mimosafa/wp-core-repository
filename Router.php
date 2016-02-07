<?php
namespace mimosafa\WP\CoreRepository;

class Router {

	private $queried;
	private $hooks = [];

	private static $def = [
		'post_type' => \FILTER_DEFAULT,
		'taxonomy'  => \FILTER_DEFAULT,
		'post'      => \FILTER_VALIDATE_INT,
		// & more
	];

	public static function instance() {
		static $instance;
		return $instance ?: $instance = new self();
	}

	private function __construct() {
		$this->request();
		$this->hooks();
	}

	private function request() {
		is_admin() ? $this->admin_request() : add_action( 'parse_request', [ $this, 'parse_request' ] );
	}

	private function hooks() {
		//
	}

	private function admin_request() {
		global $pagenow;
		$q = filter_input_array( \INPUT_GET, self::$def );
		switch ( $pagenow ) {
			case 'edit.php' :
			case 'post-new.php' :
				$post_type = isset( $q['post_type'] ) ? $q['post_type'] : 'post';
				break;
			case 'post.php' :
				/**
				 * @see https://ja.forums.wordpress.org/topic/150122
				 */
				$post_type = $q['post'] ? get_post_type( $q['post'] ) : filter_input( \INPUT_POST, 'post_type' );
				break;
			case 'edit-tags.php' :
				$taxonomy = $q['taxonomy'] ?: null;
				break;
			default :
				// dashboard
				break;
		}
		if ( $post_type && $repository = PostType::instance( $post_type ) ) {
			$this->queried = $post_type;
		}
		else if ( $taxonomy && $repository = Taxonomy::instance( $taxonomy ) ) {
			$this->queried = $taxonomy;
		}
	}

	public function parse_request( \WP $wp ) {
		//
	}

}
