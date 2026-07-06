<?php
/**
 * Elementor Element Helpers.
 *
 * Shared utilities for manipulating Elementor `_elementor_data` post meta:
 * parse, find, insert, remove, save, and cache clearing.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Element_Helpers
 *
 * Static helper methods for Elementor element tree manipulation.
 *
 * @since 2.9.0
 */
class HFE_Element_Helpers {

	/**
	 * Parse `_elementor_data` JSON from a template post.
	 *
	 * @param int $post_id Template post ID.
	 * @return array|WP_Error Parsed element tree or error.
	 */
	public static function parse_elementor_data( $post_id ) {
		$raw = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $raw ) ) {
			return [];
		}

		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			return new \WP_Error(
				'hfe_invalid_elementor_data',
				__( 'Template has invalid Elementor data.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		return $data;
	}

	/**
	 * Save `_elementor_data` back to post meta and clear Elementor CSS cache.
	 *
	 * @param int   $post_id Template post ID.
	 * @param array $data    Element tree array.
	 * @return true|WP_Error True on success, error on failure.
	 */
	public static function save_elementor_data( $post_id, $data ) {
		// Undo safety net: snapshot the current (pre-edit) Elementor data before we
		// overwrite it, so an accidental AI change can be reverted via builder/undo.
		// Captured on every save path (including the headless fallback below, which
		// does not create an Elementor revision).
		self::snapshot_for_undo( $post_id );

		// Security: AI clients can supply arbitrary widget content. The fallback
		// meta-save path below bypasses Elementor's own `unfiltered_html` gate, so
		// sanitize raw-HTML-bearing widgets here for users who lack that capability.
		// Applied to BOTH the Document API and the direct-meta paths as defense in depth.
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$data = self::sanitize_raw_html_widgets( $data );
		}

		// Try Elementor's Document save API first — triggers hooks, CSS regen, and cache clearing.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( $document ) {
				// Document API expects arrays, not stdClass (which we use for JSON {} encoding).
				$clean_data = self::convert_stdclass_to_array( $data );

				try {
					$result = $document->save( [ 'elements' => $clean_data ] );
				} catch ( \TypeError $e ) {
					$result = new \WP_Error( 'hfe_document_save_error', $e->getMessage() );
				}

				if ( ! is_wp_error( $result ) ) {
					return true;
				}

				// Document save failed — fall through to direct meta save.
			}
		}

		// Fallback: direct meta save (for CLI/non-browser contexts).
		$json = wp_json_encode( $data );

		if ( false === $json ) {
			return new \WP_Error(
				'hfe_encode_failed',
				__( 'Failed to encode Elementor data.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );

		// Update Elementor meta so the post is recognized as an Elementor document.
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
		}

		self::clear_elementor_cache( $post_id );

		// Also delete the cached CSS file from disk.
		$upload_dir = wp_upload_dir();
		$css_file   = $upload_dir['basedir'] . '/elementor/css/post-' . absint( $post_id ) . '.css';
		if ( file_exists( $css_file ) ) {
			wp_delete_file( $css_file );
		}

		return true;
	}

	/**
	 * Meta key holding the single-level undo snapshot of `_elementor_data`.
	 *
	 * @since 2.9.0
	 * @var string
	 */
	const UNDO_SNAPSHOT_META = '_hfe_ai_undo_snapshot';

	/**
	 * Snapshot the current `_elementor_data` so the last AI edit can be reverted.
	 *
	 * Stores one level of undo (overwritten on each save, never accumulated) along
	 * with a timestamp and the acting user. No-op when the post has no data yet.
	 *
	 * @since 2.9.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function snapshot_for_undo( $post_id ) {
		$previous = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $previous ) ) {
			return;
		}

		update_post_meta(
			$post_id,
			self::UNDO_SNAPSHOT_META,
			array(
				'data'      => $previous,
				'timestamp' => time(),
				'user'      => get_current_user_id(),
			)
		);
	}

	/**
	 * Sanitize raw-HTML-bearing widget settings in an element tree.
	 *
	 * The `html`, `shortcode`, and `text-editor` widgets accept markup that is
	 * rendered verbatim on the frontend. When a user without the `unfiltered_html`
	 * capability supplies these (e.g. via an AI/MCP client), run their content
	 * through `wp_kses_post()` to strip scripts and other dangerous markup,
	 * mirroring Elementor's own capability gate.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed $elements Element tree (array of element nodes).
	 * @return mixed Sanitized element tree.
	 */
	public static function sanitize_raw_html_widgets( $elements ) {
		if ( ! is_array( $elements ) ) {
			return $elements;
		}

		// Map of widgetType => setting key that holds raw markup.
		$raw_html_widgets = [
			'html'        => 'html',
			'shortcode'   => 'shortcode',
			'text-editor' => 'editor',
		];

		foreach ( $elements as &$element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$widget_type = $element['widgetType'] ?? '';

			if ( isset( $raw_html_widgets[ $widget_type ] ) ) {
				$setting_key = $raw_html_widgets[ $widget_type ];

				if ( isset( $element['settings'][ $setting_key ] ) && is_string( $element['settings'][ $setting_key ] ) ) {
					$element['settings'][ $setting_key ] = wp_kses_post( $element['settings'][ $setting_key ] );
				}
			}

			// Strip XSS vectors from generic settings (event-handler custom
			// attributes, attribute-breaking CSS classes, markup in custom CSS) at
			// any depth — these apply to every element type, not just the raw-HTML
			// widgets above, and the direct-meta save below bypasses Elementor's
			// own gate.
			if ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) {
				$element['settings'] = self::sanitize_unsafe_settings( $element['settings'] );
			}

			// Recurse into nested children (containers, sections, columns).
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$element['elements'] = self::sanitize_raw_html_widgets( $element['elements'] );
			}
		}

		unset( $element );

		return $elements;
	}

	/**
	 * Recursively neutralize XSS-prone Elementor setting values.
	 *
	 * Targets settings that can carry executable markup regardless of widget
	 * type — custom HTML attributes (event handlers, javascript: URLs), custom
	 * CSS classes (attribute breakout), and custom CSS blocks (</style>
	 * breakout) — at any nesting depth, e.g. link.custom_attributes inside a
	 * button or icon widget.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed $node Settings array (or a nested value).
	 * @return mixed Sanitized settings.
	 */
	public static function sanitize_unsafe_settings( $node ) {
		if ( ! is_array( $node ) ) {
			return $node;
		}

		foreach ( $node as $key => $value ) {
			if ( is_array( $value ) ) {
				$node[ $key ] = self::sanitize_unsafe_settings( $value );
			} elseif ( is_string( $value ) ) {
				$node[ $key ] = self::sanitize_unsafe_setting_value( (string) $key, $value );
			}
		}

		return $node;
	}

	/**
	 * Sanitize a single setting value, by key, for known XSS vectors.
	 *
	 * @since 2.9.0
	 *
	 * @param string $key   Setting key.
	 * @param string $value Setting value.
	 * @return string Sanitized value.
	 */
	private static function sanitize_unsafe_setting_value( $key, $value ) {
		// Custom HTML attributes — Elementor stores "key|value" per line. Drop
		// event-handler attributes (on*) and other executable attributes, and
		// blank out javascript:/data:/vbscript: URLs in the value.
		if ( 'custom_attributes' === $key ) {
			$lines = preg_split( '/[\r\n]+/', $value );
			$safe  = [];

			foreach ( (array) $lines as $line ) {
				$parts = explode( '|', $line, 2 );
				$attr  = isset( $parts[0] ) ? strtolower( trim( $parts[0] ) ) : '';

				if ( '' === $attr ) {
					continue;
				}

				if ( 0 === strpos( $attr, 'on' ) || in_array( $attr, [ 'style', 'srcdoc', 'formaction', 'href', 'xlink:href' ], true ) ) {
					continue;
				}

				$val = isset( $parts[1] ) ? trim( $parts[1] ) : '';

				if ( preg_match( '#^\s*(javascript|data|vbscript)\s*:#i', $val ) ) {
					$val = '';
				}

				$safe[] = ( '' !== $val ) ? $attr . '|' . $val : $attr;
			}

			return implode( "\n", $safe );
		}

		// Custom CSS class list — keep only valid class-name characters so a value
		// cannot break out of the class="" attribute.
		if ( '_css_classes' === $key ) {
			return preg_replace( '/[^A-Za-z0-9_\- ]/', '', $value );
		}

		// Custom CSS blocks — CSS must never contain markup; strip angle brackets
		// to prevent a </style> breakout.
		if ( 'custom_css' === $key || 'section_custom_css' === $key || ( strlen( $key ) > 11 && '_custom_css' === substr( $key, -11 ) ) ) {
			return str_replace( [ '<', '>' ], '', $value );
		}

		return $value;
	}

	/**
	 * Clear Elementor CSS cache for a template and force regeneration.
	 *
	 * Deletes the post-level CSS meta, triggers a global cache flush,
	 * and force-regenerates the post CSS file so the frontend immediately
	 * reflects changes without requiring a manual "Clear Files & Data".
	 *
	 * @param int $post_id Template post ID.
	 * @return void
	 */
	public static function clear_elementor_cache( $post_id ) {
		delete_post_meta( $post_id, '_elementor_css' );

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		// Clear global Elementor file cache.
		\Elementor\Plugin::$instance->files_manager->clear_cache();

		// Force-regenerate the post CSS file immediately.
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_file = \Elementor\Core\Files\CSS\Post::create( $post_id );
			$css_file->update();
		}

		// Also regenerate global CSS (for global widgets/styles).
		if ( class_exists( '\Elementor\Core\Files\CSS\Global_CSS' ) ) {
			$global_css = \Elementor\Core\Files\CSS\Global_CSS::create( 'global.css' );
			$global_css->update();
		}
	}

	/**
	 * Find an element by ID in the element tree (recursive).
	 *
	 * @param array  $elements Element tree array.
	 * @param string $element_id Target element ID.
	 * @return array|null Found element or null.
	 */
	public static function find_element( $elements, $element_id ) {
		foreach ( $elements as $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$found = self::find_element( $element['elements'], $element_id );

				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Find the parent element and child index for a given element ID.
	 *
	 * Returns by reference so callers can mutate the parent's elements array.
	 *
	 * @param array  $elements   Element tree (passed by reference).
	 * @param string $element_id Target element ID.
	 * @return array|null Array with 'parent' (reference) and 'index', or null.
	 */
	public static function find_element_parent( &$elements, $element_id ) {
		foreach ( $elements as $index => &$element ) {
			if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
				// Element is at this level — return the containing array info.
				return [
					'parent' => &$elements,
					'index'  => $index,
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				$found = self::find_element_parent( $element['elements'], $element_id );

				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Insert an element into the tree at a specified position.
	 *
	 * Positions:
	 * - 'append'                → append to first top-level container
	 * - 'prepend'               → prepend to first top-level container
	 * - [ 'after' => id ]       → insert after sibling element
	 * - [ 'before' => id ]      → insert before sibling element
	 * - [ 'inside' => id ]      → append inside a container element
	 *
	 * @param array $elements    Element tree (modified in place).
	 * @param array $new_element New element to insert.
	 * @param mixed $position    Position descriptor.
	 * @return array|WP_Error    Modified element tree or error.
	 */
	public static function insert_element( $elements, $new_element, $position = 'append' ) {
		// Handle append/prepend to first widget-level container.
		if ( 'append' === $position || 'prepend' === $position ) {
			$path = self::find_widget_container( $elements );

			if ( null === $path ) {
				// No wrapper exists — create appropriate structure for the site.
				$wrapper    = self::create_wrapper( [ $new_element ] );
				$elements[] = $wrapper;
				return $elements;
			}

			// Walk the index path to reach the target container's children.
			$target = &$elements;
			foreach ( $path as $idx ) {
				$target = &$target[ $idx ]['elements'];
			}

			if ( 'append' === $position ) {
				$target[] = $new_element;
			} else {
				array_unshift( $target, $new_element );
			}

			return $elements;
		}

		// Handle numeric index positioning (e.g., position: 1 = insert at index 1).
		if ( is_numeric( $position ) ) {
			$index = (int) $position;
			$path  = self::find_widget_container( $elements );

			if ( null === $path ) {
				$wrapper    = self::create_wrapper( [ $new_element ] );
				$elements[] = $wrapper;
				return $elements;
			}

			$target = &$elements;
			foreach ( $path as $idx ) {
				$target = &$target[ $idx ]['elements'];
			}

			// Clamp index to valid range.
			$max = count( $target );
			if ( $index < 0 ) {
				$index = 0;
			} elseif ( $index > $max ) {
				$index = $max;
			}

			array_splice( $target, $index, 0, [ $new_element ] );
			return $elements;
		}

		// Handle relative positioning (after/before/inside).
		if ( is_array( $position ) ) {
			if ( isset( $position['after'] ) ) {
				return self::insert_relative( $elements, $new_element, $position['after'], 'after' );
			}

			if ( isset( $position['before'] ) ) {
				return self::insert_relative( $elements, $new_element, $position['before'], 'before' );
			}

			if ( isset( $position['inside'] ) ) {
				return self::insert_inside( $elements, $new_element, $position['inside'] );
			}
		}

		return new \WP_Error(
			'hfe_invalid_position',
			__( 'Invalid position. Use "append", "prepend", or an object with "after", "before", or "inside" key.', 'header-footer-elementor' ),
			[ 'status' => 400 ]
		);
	}

	/**
	 * Remove an element from the tree by ID.
	 *
	 * @param array  $elements   Element tree.
	 * @param string $element_id Element ID to remove.
	 * @return array|WP_Error    Modified tree or error if not found.
	 */
	public static function remove_element( $elements, $element_id ) {
		$result = self::remove_element_recursive( $elements, $element_id );

		if ( ! $result['found'] ) {
			return new \WP_Error(
				'hfe_element_not_found',
				__( 'Element not found in template.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		return $result['elements'];
	}

	/**
	 * Move an element to a new position within the tree.
	 *
	 * Removes the element from its current location and inserts at the new position.
	 *
	 * @param array  $elements   Element tree.
	 * @param string $element_id Element ID to move.
	 * @param mixed  $position   New position descriptor (same as insert_element).
	 * @return array|WP_Error    Modified tree or error.
	 */
	public static function move_element( $elements, $element_id, $position ) {
		// Find and extract the element first.
		$element = self::find_element( $elements, $element_id );

		if ( null === $element ) {
			return new \WP_Error(
				'hfe_element_not_found',
				__( 'Element not found in template.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		// Remove from current position.
		$elements = self::remove_element( $elements, $element_id );

		if ( is_wp_error( $elements ) ) {
			return $elements;
		}

		// Insert at new position.
		return self::insert_element( $elements, $element, $position );
	}

	/**
	 * Generate a random Elementor-compatible element ID.
	 *
	 * Elementor uses 7-8 character hex strings.
	 *
	 * @return string Random hex ID.
	 */
	public static function generate_element_id() {
		return substr( md5( wp_generate_uuid4() ), 0, 7 );
	}

	/**
	 * Build a widget element array.
	 *
	 * @param string $widget_type Elementor widget type slug.
	 * @param array  $settings    Widget settings.
	 * @return array Elementor widget element.
	 */
	public static function build_widget_element( $widget_type, $settings = [] ) {
		return [
			'id'         => self::generate_element_id(),
			'elType'     => 'widget',
			'widgetType' => $widget_type,
			'settings'   => ! empty( $settings ) ? self::normalize_elementor_settings( $settings ) : new \stdClass(),
			'elements'   => [],
		];
	}

	/**
	 * Create the appropriate wrapper structure for widgets.
	 *
	 * Detects whether the site uses modern containers or legacy section/column
	 * and creates the correct wrapper so Elementor can render the widgets.
	 *
	 * @param array $children Child widget elements.
	 * @return array Elementor wrapper element (container or section>column).
	 */
	public static function create_wrapper( $children = [] ) {
		if ( self::is_container_active() ) {
			return self::create_container( $children );
		}

		return self::create_section_column( $children );
	}

	/**
	 * Create a container element wrapping child elements.
	 *
	 * @param array $children Child elements.
	 * @return array Elementor container element.
	 */
	public static function create_container( $children = [] ) {
		return [
			'id'       => self::generate_element_id(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => [
				'container_type' => 'flex',
				'content_width'  => 'full',
			],
			'elements' => $children,
		];
	}

	/**
	 * Create a section > column wrapper for legacy Elementor layouts.
	 *
	 * @param array $children Child widget elements.
	 * @return array Elementor section element containing a column.
	 */
	public static function create_section_column( $children = [] ) {
		return [
			'id'       => self::generate_element_id(),
			'elType'   => 'section',
			'settings' => [
				'layout'    => 'full_width',
				'structure' => '10',
			],
			'elements' => [
				[
					'id'       => self::generate_element_id(),
					'elType'   => 'column',
					'settings' => [
						'_column_size' => 100,
						'_inline_size' => 100,
					],
					'elements' => $children,
				],
			],
		];
	}

	/**
	 * Check if the Elementor container (flexbox) experiment is active.
	 *
	 * @return bool True if containers are active, false for legacy section/column.
	 */
	/**
	 * Ensure Elementor widgets are fully registered.
	 *
	 * In WP-CLI/REST/MCP contexts, widget registration may not complete before
	 * abilities execute. This forces Elementor to fire its registration hooks
	 * so HFE and third-party widgets are available for schema introspection.
	 *
	 * @return void
	 */
	public static function ensure_widgets_registered() {
		static $ensured = false;

		if ( $ensured || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$ensured = true;

		// Force Elementor to initialize widgets if not already done.
		// get_widget_types(null) triggers init_widgets() which fires 'elementor/widgets/register'.
		\Elementor\Plugin::$instance->widgets_manager->get_widget_types();
	}

	public static function is_container_active() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$experiments = \Elementor\Plugin::$instance->experiments;

		if ( ! $experiments ) {
			return false;
		}

		return $experiments->is_feature_active( 'container' );
	}

	/**
	 * Create a section element with the specified number of columns.
	 *
	 * For legacy Elementor layouts: creates a section with N columns
	 * at the given size ratios. Supports outer and inner sections.
	 *
	 * @param array $column_sizes Array of column size percentages (must sum to 100).
	 * @param bool  $is_inner     Whether this is an inner section.
	 * @param array $settings     Optional section settings.
	 * @return array Elementor section element with columns.
	 */
	public static function create_section( $column_sizes = [ 100 ], $is_inner = false, $settings = [] ) {
		$columns = [];

		foreach ( $column_sizes as $size ) {
			$columns[] = [
				'id'       => self::generate_element_id(),
				'elType'   => 'column',
				'settings' => [
					'_column_size' => absint( $size ),
					'_inline_size' => (float) $size,
				],
				'elements' => [],
			];
		}

		$structure_code = self::get_structure_preset( $column_sizes );

		$section = [
			'id'       => self::generate_element_id(),
			'elType'   => 'section',
			'settings' => self::normalize_elementor_settings(
				array_merge(
					[
						'layout'    => 'full_width',
						'structure' => $structure_code,
					],
					$settings
				)
			),
			'elements' => $columns,
		];

		if ( $is_inner ) {
			$section['isInner'] = true;
		}

		return $section;
	}

	/**
	 * Get the Elementor structure preset code for a column layout.
	 *
	 * Elementor uses preset codes to describe column layouts in the editor:
	 * 10 = 1 column, 20 = 2 equal (50/50), 21 = 33/66, 22 = 66/33,
	 * 30 = 3 equal (33/33/33), 31 = 50/25/25, 32 = 25/50/25, 33 = 25/25/50,
	 * 40 = 4 equal (25x4).
	 *
	 * Falls back to "{N}0" (e.g., "50" for 5 columns) for non-standard layouts.
	 *
	 * @param array $column_sizes Array of column size percentages.
	 * @return string Structure preset code.
	 */
	public static function get_structure_preset( $column_sizes ) {
		$count = count( $column_sizes );

		// Standard presets by column count and sizes.
		$presets = [
			1 => [ '10' ],
			2 => [
				'20' => [ 50, 50 ],
				'21' => [ 33, 66 ],
				'22' => [ 66, 33 ],
			],
			3 => [
				'30' => [ 33, 34, 33 ],
				'31' => [ 50, 25, 25 ],
				'32' => [ 25, 50, 25 ],
				'33' => [ 25, 25, 50 ],
			],
			4 => [
				'40' => [ 25, 25, 25, 25 ],
			],
		];

		if ( 1 === $count ) {
			return '10';
		}

		if ( isset( $presets[ $count ] ) && is_array( $presets[ $count ] ) ) {
			foreach ( $presets[ $count ] as $code => $sizes ) {
				if ( array_map( 'absint', $column_sizes ) === $sizes ) {
					return (string) $code;
				}
			}

			// No exact match — return the first preset for this column count.
			$codes = array_keys( $presets[ $count ] );
			return (string) $codes[0];
		}

		// Fallback for 5+ columns.
		return (string) ( $count * 10 );
	}

	/**
	 * Create the appropriate structural layout.
	 *
	 * For legacy sites: creates section with columns.
	 * For container sites: creates container (optionally with nested containers for multi-column).
	 *
	 * @param array $column_sizes Column size ratios (e.g., [50, 50] or [33, 34, 33]).
	 * @param bool  $is_inner     For legacy: inner section. For containers: nested container.
	 * @param array $settings     Optional section/container settings.
	 * @return array Elementor structural element.
	 */
	public static function create_layout( $column_sizes = [ 100 ], $is_inner = false, $settings = [] ) {
		// Auto-normalize column sizes to sum to 100.
		$column_sizes = self::normalize_column_sizes( $column_sizes );

		if ( self::is_container_active() ) {
			// Container mode: single-column — widgets go directly into this container.
			if ( count( $column_sizes ) <= 1 ) {
				return [
					'id'       => self::generate_element_id(),
					'elType'   => 'container',
					'isInner'  => $is_inner,
					'settings' => self::normalize_elementor_settings(
						array_merge(
							[
								'container_type' => 'flex',
								'content_width'  => 'full',
								'flex_direction' => 'column',
							],
							$settings
						)
					),
					'elements' => [],
				];
			}

			// Container mode: multi-column — child containers for each column.
			$children = [];
			foreach ( $column_sizes as $size ) {
				$children[] = [
					'id'       => self::generate_element_id(),
					'elType'   => 'container',
					'isInner'  => true,
					'settings' => [
						'container_type' => 'flex',
						'content_width'  => 'full',
						'flex_direction' => 'column',
						'width'          => [
							'size' => (float) $size,
							'unit' => '%',
						],
					],
					'elements' => [],
				];
			}

			return [
				'id'       => self::generate_element_id(),
				'elType'   => 'container',
				'isInner'  => $is_inner,
				'settings' => self::normalize_elementor_settings(
					array_merge(
						[
							'container_type' => 'flex',
							'content_width'  => 'full',
							'flex_direction' => 'row',
						],
						$settings
					)
				),
				'elements' => $children,
			];
		}

		// Legacy mode: create section with columns.
		return self::create_section( $column_sizes, $is_inner, $settings );
	}

	/**
	 * Normalize column size percentages to sum to 100.
	 *
	 * Handles cases where AI passes sizes that don't sum correctly
	 * (e.g., [33, 33, 33] = 99, or [40, 40, 40] = 120).
	 *
	 * @param array $column_sizes Raw column size percentages.
	 * @return array Normalized sizes summing to 100.
	 */
	public static function normalize_column_sizes( $column_sizes ) {
		if ( empty( $column_sizes ) ) {
			return [ 100 ];
		}

		$column_sizes = array_map( 'absint', $column_sizes );
		$sum          = array_sum( $column_sizes );

		if ( 0 === $sum ) {
			// All zeros — distribute evenly.
			$count = count( $column_sizes );
			$each  = intval( 100 / $count );
			$sizes = array_fill( 0, $count, $each );
			// Give remainder to the last column.
			$sizes[ $count - 1 ] += 100 - ( $each * $count );
			return $sizes;
		}

		if ( 100 === $sum ) {
			return $column_sizes;
		}

		// Scale proportionally to sum to 100.
		$scaled = [];
		$total  = 0;
		$count  = count( $column_sizes );

		for ( $i = 0; $i < $count - 1; $i++ ) {
			$scaled[ $i ] = max( 1, intval( round( $column_sizes[ $i ] * 100 / $sum ) ) );
			$total       += $scaled[ $i ];
		}

		// Last column gets the remainder to guarantee sum = 100.
		$scaled[ $count - 1 ] = 100 - $total;

		return $scaled;
	}

	/**
	 * Build a section/container element from a declarative definition.
	 *
	 * Converts a JSON structure definition into Elementor elements. Used by
	 * both the template builder and page builder abilities.
	 *
	 * @param array $definition Section definition with type, columns, settings, children.
	 * @return array|WP_Error Array with 'element' and 'count', or error.
	 */
	public static function build_section_from_definition( $definition ) {
		$type            = $definition['type'] ?? 'section';
		$settings        = isset( $definition['settings'] ) && is_array( $definition['settings'] ) ? $definition['settings'] : [];
		$children        = isset( $definition['children'] ) && is_array( $definition['children'] ) ? $definition['children'] : [];
		$columns         = isset( $definition['columns'] ) && is_array( $definition['columns'] ) ? array_map( 'absint', $definition['columns'] ) : [ 100 ];
		$column_settings = isset( $definition['column_settings'] ) && is_array( $definition['column_settings'] ) ? $definition['column_settings'] : [];
		$is_inner        = ! empty( $definition['is_inner'] );
		$count           = 1; // Count this section/container.

		// Build child widget elements.
		$widget_elements = [];
		foreach ( $children as $child_def ) {
			$child_type = $child_def['type'] ?? '';

			if ( 'widget' === $child_type ) {
				$widget_type     = sanitize_text_field( $child_def['widget_type'] ?? '' );
				$widget_settings = isset( $child_def['settings'] ) && is_array( $child_def['settings'] ) ? $child_def['settings'] : [];

				if ( empty( $widget_type ) ) {
					continue;
				}

				if ( ! self::is_widget_allowed( $widget_type ) ) {
					// Check if it requires a specific plugin (e.g., UAE Pro).
					$requirements = self::check_widget_requirements( $widget_type );

					if ( ! empty( $requirements['required_plugin'] ) && ! $requirements['is_active'] ) {
						return new \WP_Error(
							'hfe_widget_requires_plugin',
							/* translators: %s: Required plugin name */
							sprintf( __( 'This widget requires %s.', 'header-footer-elementor' ), $requirements['required_plugin'] ),
							[ 'status' => 400 ]
						);
					}

					return new \WP_Error(
						'hfe_widget_not_allowed',
						/* translators: %s: Widget type slug */
						sprintf( __( 'Widget type "%s" is not recognized. Use template-builder/list-widget-types to see available types.', 'header-footer-elementor' ), $widget_type ),
						[ 'status' => 400 ]
					);
				}

				$widget_elements[] = self::build_widget_element( $widget_type, $widget_settings );
				++$count;
			} elseif ( 'section' === $child_type || 'container' === $child_type ) {
				// Nested section/container — recurse.
				$nested = self::build_section_from_definition( $child_def );

				if ( is_wp_error( $nested ) ) {
					return $nested;
				}

				$widget_elements[] = $nested['element'];
				$count            += $nested['count'];
			}
		}

		// Create the structural layout.
		$layout        = self::create_layout( $columns, $is_inner, $settings );
		$is_single_col = ( count( $columns ) <= 1 );
		$is_container  = self::is_container_active();

		// Count child columns/containers (single-column container has none).
		if ( ! ( $is_container && $is_single_col ) ) {
			$count += count( $columns );
		}

		// Apply per-column settings (alignment, padding, background, etc.).
		// For single-column container: column_settings[0] merges into the parent container itself.
		if ( ! empty( $column_settings ) ) {
			if ( $is_container && $is_single_col ) {
				if ( isset( $column_settings[0] ) && is_array( $column_settings[0] ) ) {
					$normalized         = self::normalize_elementor_settings( $column_settings[0] );
					$layout['settings'] = array_merge( $layout['settings'], $normalized );
				}
			} elseif ( ! empty( $layout['elements'] ) ) {
				foreach ( $layout['elements'] as $ci => &$col_element ) {
					if ( isset( $column_settings[ $ci ] ) && is_array( $column_settings[ $ci ] ) ) {
						$normalized              = self::normalize_elementor_settings( $column_settings[ $ci ] );
						$col_element['settings'] = array_merge( $col_element['settings'], $normalized );
					}
				}
				unset( $col_element );
			}
		}

		// Distribute widgets across columns/containers.
		if ( ! empty( $widget_elements ) ) {
			if ( $is_container && $is_single_col ) {
				// Single-column container: widgets go directly into the parent container.
				$layout['elements'] = array_merge( $layout['elements'], $widget_elements );
			} elseif ( ! empty( $layout['elements'] ) ) {
				$col_count = count( $layout['elements'] );
				foreach ( $widget_elements as $wi => $widget ) {
					$col_index = min( $wi, $col_count - 1 );
					$layout['elements'][ $col_index ]['elements'][] = $widget;
				}
			}
		}

		return [
			'element' => $layout,
			'count'   => $count,
		];
	}

	/**
	 * Add a column to an existing section element in the tree.
	 *
	 * Recalculates all column sizes to distribute evenly if auto_resize is true.
	 *
	 * @param array  $elements    Element tree.
	 * @param string $section_id  Section element ID to add column to.
	 * @param int    $column_size Column size percentage (ignored if auto_resize).
	 * @param bool   $auto_resize Whether to auto-distribute column sizes.
	 * @return array|WP_Error Modified tree or error.
	 */
	public static function add_column_to_section( $elements, $section_id, $column_size = 0, $auto_resize = true ) {
		$result = self::add_column_recursive( $elements, $section_id, $column_size, $auto_resize );

		if ( ! $result['found'] ) {
			return new \WP_Error(
				'hfe_section_not_found',
				/* translators: %s: element ID */
				sprintf( __( 'Section "%s" not found in template.', 'header-footer-elementor' ), sanitize_text_field( $section_id ) ),
				[ 'status' => 404 ]
			);
		}

		return $result;
	}

	/**
	 * Recursive helper for adding a column to a section.
	 *
	 * @param array  $elements    Element tree.
	 * @param string $section_id  Target section ID.
	 * @param int    $column_size Column size percentage.
	 * @param bool   $auto_resize Auto-distribute sizes.
	 * @return array Result with 'found', 'elements', and 'column_id'.
	 */
	private static function add_column_recursive( $elements, $section_id, $column_size, $auto_resize ) {
		foreach ( $elements as $index => $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $section_id ) {
				$el_type = $element['elType'] ?? '';

				if ( 'section' !== $el_type && 'container' !== $el_type ) {
					return [
						'found'    => false,
						'elements' => $elements,
					];
				}

				$new_column_id = self::generate_element_id();

				if ( 'container' === $el_type ) {
					// Container mode: add a child container.
					$new_col = [
						'id'       => $new_column_id,
						'elType'   => 'container',
						'isInner'  => true,
						'settings' => [
							'container_type' => 'flex',
							'content_width'  => 'full',
						],
						'elements' => [],
					];
				} else {
					// Section mode: add a column.
					$new_col = [
						'id'       => $new_column_id,
						'elType'   => 'column',
						'settings' => [
							'_column_size' => $column_size,
							'_inline_size' => (float) $column_size,
						],
						'elements' => [],
					];
				}

				$elements[ $index ]['elements'][] = $new_col;

				// Auto-resize all columns/containers to distribute evenly.
				if ( $auto_resize ) {
					$col_count = count( $elements[ $index ]['elements'] );
					$each_size = intval( 100 / $col_count );

					if ( 'section' === $el_type ) {
						foreach ( $elements[ $index ]['elements'] as $ci => &$col ) {
							$col['settings']['_column_size'] = $each_size;
							$col['settings']['_inline_size'] = (float) $each_size;
						}
						unset( $col );

						// Update the section's structure preset.
						$new_sizes = array_fill( 0, $col_count, $each_size );
						$elements[ $index ]['settings']['structure'] = self::get_structure_preset( $new_sizes );
					} else {
						// Container mode: update child container widths.
						foreach ( $elements[ $index ]['elements'] as $ci => &$col ) {
							$col['settings']['width'] = [
								'size' => (float) $each_size,
								'unit' => '%',
							];
						}
						unset( $col );
					}
				}

				return [
					'found'     => true,
					'elements'  => $elements,
					'column_id' => $new_column_id,
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				$child_result = self::add_column_recursive( $element['elements'], $section_id, $column_size, $auto_resize );

				if ( $child_result['found'] ) {
					$elements[ $index ]['elements'] = $child_result['elements'];

					return [
						'found'     => true,
						'elements'  => $elements,
						'column_id' => $child_result['column_id'],
					];
				}
			}
		}

		return [
			'found'    => false,
			'elements' => $elements,
		];
	}

	/**
	 * Simplify element tree for API response (strip heavy settings, keep structure).
	 *
	 * Returns a clean representation with id, type, widgetType, and children.
	 * Includes a flattened list of setting keys (not values) to hint at configuration.
	 *
	 * @param array $elements Element tree.
	 * @return array Simplified tree.
	 */
	public static function simplify_tree( $elements ) {
		$result = [];

		foreach ( $elements as $element ) {
			$item = [
				'id'     => $element['id'] ?? '',
				'elType' => $element['elType'] ?? '',
			];

			if ( ! empty( $element['widgetType'] ) ) {
				$item['widgetType'] = $element['widgetType'];
			}

			if ( ! empty( $element['settings'] ) && is_array( $element['settings'] ) ) {
				// Include only non-default settings keys for brevity.
				$setting_keys = array_keys( $element['settings'] );
				if ( ! empty( $setting_keys ) ) {
					$item['setting_keys'] = $setting_keys;
				}
			}

			if ( ! empty( $element['elements'] ) ) {
				$item['elements'] = self::simplify_tree( $element['elements'] );
			} else {
				$item['elements'] = [];
			}

			$result[] = $item;
		}

		return $result;
	}

	// ──────────────────────────────────────────────
	// Private helpers.
	// ──────────────────────────────────────────────

	/**
	 * Recursively convert stdClass objects to arrays in element data.
	 *
	 * Elementor's Document save API expects arrays for settings, but
	 * build_widget_element() uses stdClass for empty settings to ensure
	 * correct JSON encoding ({} instead of []).
	 *
	 * @param mixed $data Element tree data (array, stdClass, or scalar).
	 * @return mixed Converted data with all stdClass replaced by arrays.
	 */
	private static function convert_stdclass_to_array( $data ) {
		if ( $data instanceof \stdClass ) {
			$data = (array) $data;
		}

		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = self::convert_stdclass_to_array( $value );
			}
		}

		return $data;
	}

	/**
	 * Find the index path to the innermost container that can hold widgets.
	 *
	 * Recursively descends through the first structural child (section, column,
	 * or container) until it reaches a level where children are widgets or empty.
	 * Works with both legacy layouts (section > column > widget) and modern
	 * container layouts (container > container > widget) at any nesting depth.
	 *
	 * @param array $elements Element tree at current level.
	 * @return array|null Ordered array of integer indices forming the path, or null.
	 */
	private static function find_widget_container( $elements ) {
		$container_types = [ 'section', 'column', 'container' ];

		foreach ( $elements as $index => $element ) {
			$type = $element['elType'] ?? '';

			if ( ! in_array( $type, $container_types, true ) ) {
				continue;
			}

			// Found a structural element. Check if its children are also structural.
			if ( ! empty( $element['elements'] ) ) {
				$first_child_type = $element['elements'][0]['elType'] ?? '';

				if ( in_array( $first_child_type, $container_types, true ) ) {
					// Children are structural — descend deeper.
					$deeper = self::find_widget_container( $element['elements'] );

					if ( null !== $deeper ) {
						return array_merge( [ $index ], $deeper );
					}
				}
			}

			// Children are widgets or empty — this is the widget-level container.
			return [ $index ];
		}

		return null;
	}

	/**
	 * Insert element relative to a sibling (after or before).
	 *
	 * @param array  $elements    Element tree.
	 * @param array  $new_element New element.
	 * @param string $sibling_id  Sibling element ID.
	 * @param string $direction   'after' or 'before'.
	 * @return array|WP_Error Modified tree or error.
	 */
	private static function insert_relative( $elements, $new_element, $sibling_id, $direction ) {
		$result = self::insert_relative_recursive( $elements, $new_element, $sibling_id, $direction );

		if ( ! $result['found'] ) {
			return new \WP_Error(
				'hfe_sibling_not_found',
				/* translators: %s: element ID */
				sprintf( __( 'Sibling element "%s" not found in template.', 'header-footer-elementor' ), sanitize_text_field( $sibling_id ) ),
				[ 'status' => 404 ]
			);
		}

		return $result['elements'];
	}

	/**
	 * Recursive helper for relative insertion.
	 *
	 * @param array  $elements    Element tree.
	 * @param array  $new_element New element.
	 * @param string $sibling_id  Sibling element ID.
	 * @param string $direction   'after' or 'before'.
	 * @return array Result with 'found' bool and 'elements' array.
	 */
	private static function insert_relative_recursive( $elements, $new_element, $sibling_id, $direction ) {
		foreach ( $elements as $index => $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $sibling_id ) {
				$offset = 'after' === $direction ? $index + 1 : $index;
				array_splice( $elements, $offset, 0, [ $new_element ] );

				return [
					'found'    => true,
					'elements' => $elements,
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				$child_result = self::insert_relative_recursive( $element['elements'], $new_element, $sibling_id, $direction );

				if ( $child_result['found'] ) {
					$elements[ $index ]['elements'] = $child_result['elements'];

					return [
						'found'    => true,
						'elements' => $elements,
					];
				}
			}
		}

		return [
			'found'    => false,
			'elements' => $elements,
		];
	}

	/**
	 * Insert element inside a container.
	 *
	 * @param array  $elements    Element tree.
	 * @param array  $new_element New element.
	 * @param string $parent_id   Parent container ID.
	 * @return array|WP_Error Modified tree or error.
	 */
	private static function insert_inside( $elements, $new_element, $parent_id ) {
		$result = self::insert_inside_recursive( $elements, $new_element, $parent_id );

		if ( ! $result['found'] ) {
			return new \WP_Error(
				'hfe_parent_not_found',
				/* translators: %s: element ID */
				sprintf( __( 'Parent element "%s" not found in template.', 'header-footer-elementor' ), sanitize_text_field( $parent_id ) ),
				[ 'status' => 404 ]
			);
		}

		return $result['elements'];
	}

	/**
	 * Recursive helper for inserting inside a container.
	 *
	 * @param array  $elements    Element tree.
	 * @param array  $new_element New element.
	 * @param string $parent_id   Parent container ID.
	 * @return array Result with 'found' bool and 'elements' array.
	 */
	private static function insert_inside_recursive( $elements, $new_element, $parent_id ) {
		foreach ( $elements as $index => $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $parent_id ) {
				if ( ! isset( $elements[ $index ]['elements'] ) ) {
					$elements[ $index ]['elements'] = [];
				}
				$elements[ $index ]['elements'][] = $new_element;

				return [
					'found'    => true,
					'elements' => $elements,
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				$child_result = self::insert_inside_recursive( $element['elements'], $new_element, $parent_id );

				if ( $child_result['found'] ) {
					$elements[ $index ]['elements'] = $child_result['elements'];

					return [
						'found'    => true,
						'elements' => $elements,
					];
				}
			}
		}

		return [
			'found'    => false,
			'elements' => $elements,
		];
	}

	/**
	 * Recursive helper for element removal.
	 *
	 * @param array  $elements   Element tree.
	 * @param string $element_id Element ID to remove.
	 * @return array Result with 'found' bool and 'elements' array.
	 */
	private static function remove_element_recursive( $elements, $element_id ) {
		foreach ( $elements as $index => $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
				array_splice( $elements, $index, 1 );

				return [
					'found'    => true,
					'elements' => $elements,
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				$child_result = self::remove_element_recursive( $element['elements'], $element_id );

				if ( $child_result['found'] ) {
					$elements[ $index ]['elements'] = $child_result['elements'];

					return [
						'found'    => true,
						'elements' => $elements,
					];
				}
			}
		}

		return [
			'found'    => false,
			'elements' => $elements,
		];
	}

	// ──────────────────────────────────────────────
	// Widget schema generation.
	// ──────────────────────────────────────────────

	/**
	 * Generate a JSON-compatible settings schema for a widget type.
	 *
	 * Introspects Elementor's registered controls for the widget and returns
	 * a schema describing the content-relevant settings an MCP client can set.
	 * Filters out injected controls (motion effects, transforms, display
	 * conditions, promos) to keep the schema focused on widget-owned settings.
	 *
	 * @param string $widget_type Widget type slug.
	 * @param string $tab         Which tab to include: 'content', 'style', or 'all'. Default 'content'.
	 * @return array|WP_Error Schema array or error if widget not found.
	 */
	public static function get_widget_schema( $widget_type, $tab = 'content' ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new \WP_Error(
				'hfe_elementor_not_active',
				__( 'Elementor is not active.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		// Ensure Elementor widgets are fully initialized.
		// In WP-CLI/REST contexts, widget registration may not have completed yet.
		self::ensure_widgets_registered();

		$widget = \Elementor\Plugin::$instance->widgets_manager->get_widget_types( $widget_type );

		if ( ! $widget ) {
			return new \WP_Error(
				'hfe_widget_not_found',
				/* translators: %s: widget type slug */
				sprintf( __( 'Widget type "%s" not found in Elementor.', 'header-footer-elementor' ), sanitize_text_field( $widget_type ) ),
				[ 'status' => 404 ]
			);
		}

		$controls   = $widget->get_controls();
		$properties = [];

		// Sections injected by Elementor core or third-party plugins (not widget-owned).
		$injected_sections = [
			'section_effects',
			'_section_transform',
			'_section_background',
			'_section_border',
			'_section_masking',
			'_section_responsive',
			'_section_attributes',
			'_section_style',
			'section_custom_css',
		];

		// Structural control types that don't map to settings.
		$skip_types = [ 'section', 'tabs', 'tab', 'divider', 'raw_html', 'heading', 'deprecated_notice', 'alert', 'notice' ];

		foreach ( $controls as $key => $ctrl ) {
			// Skip internal controls.
			if ( 0 === strpos( $key, '_' ) ) {
				continue;
			}

			// Tab filter.
			$ctrl_tab = $ctrl['tab'] ?? '';
			if ( 'all' !== $tab && $ctrl_tab !== $tab ) {
				continue;
			}

			// Skip injected sections.
			$section = $ctrl['section'] ?? '';
			if ( in_array( $section, $injected_sections, true ) ) {
				continue;
			}

			// Skip promo/third-party injected sections.
			if ( 0 === strpos( $section, 'hfe_' ) && false !== strpos( $section, '_promo' ) ) {
				continue;
			}
			if ( 0 === strpos( $section, 'eael_' ) ) {
				continue;
			}
			if ( 0 === strpos( $key, 'motion_fx_' ) || 0 === strpos( $key, 'sticky' ) ) {
				continue;
			}

			// Skip structural types.
			$type = $ctrl['type'] ?? '';
			if ( in_array( $type, $skip_types, true ) ) {
				continue;
			}

			$prop = self::map_control_to_schema( $ctrl );

			if ( null !== $prop ) {
				$properties[ $key ] = $prop;
			}
		}

		$result = [
			'widget_type' => $widget_type,
			'title'       => $widget->get_title(),
			'tab'         => $tab,
			'schema'      => [
				'type'       => 'object',
				'properties' => $properties,
			],
		];

		// Include pro alternative info if available (feature-level upsell).
		$pro_alt = self::get_pro_alternative( $widget_type );
		if ( ! empty( $pro_alt ) ) {
			$result['pro_alternative'] = $pro_alt;
		}

		return $result;
	}

	/**
	 * Map a single Elementor control to a JSON schema property.
	 *
	 * @param array $ctrl Elementor control definition.
	 * @return array|null JSON schema property or null if unmappable.
	 */
	private static function map_control_to_schema( $ctrl ) {
		$type    = $ctrl['type'] ?? '';
		$label   = $ctrl['label'] ?? '';
		$default = $ctrl['default'] ?? null;
		$prop    = [];

		switch ( $type ) {
			case 'text':
			case 'textarea':
			case 'hidden':
			case 'animation':
			case 'hover_animation':
			case 'text_shadow':
				$prop['type'] = 'string';
				break;

			case 'number':
				$prop['type'] = 'number';
				break;

			case 'select':
			case 'choose':
				$prop['type'] = 'string';
				$options      = $ctrl['options'] ?? [];
				if ( ! empty( $options ) ) {
					$prop['enum'] = array_keys( $options );
				}
				break;

			case 'select2':
				$prop['type'] = 'string';
				break;

			case 'switcher':
				$prop['type'] = 'string';
				$prop['enum'] = [ 'yes', '' ];
				break;

			case 'color':
				$prop['type']        = 'string';
				$prop['description'] = 'CSS color value (hex, rgb, etc.)';
				break;

			case 'slider':
				$prop['type']       = 'object';
				$prop['properties'] = [
					'size' => [ 'type' => 'number' ],
					'unit' => [
						'type'    => 'string',
						'default' => 'px',
					],
				];
				break;

			case 'dimensions':
				$prop['type']       = 'object';
				$prop['properties'] = [
					'top'      => [ 'type' => 'string' ],
					'right'    => [ 'type' => 'string' ],
					'bottom'   => [ 'type' => 'string' ],
					'left'     => [ 'type' => 'string' ],
					'unit'     => [ 'type' => 'string', 'default' => 'px' ],
					'isLinked' => [ 'type' => 'boolean' ],
				];
				break;

			case 'url':
				$prop['type']       = 'object';
				$prop['properties'] = [
					'url'               => [ 'type' => 'string' ],
					'is_external'       => [ 'type' => 'string', 'enum' => [ 'on', '' ] ],
					'nofollow'          => [ 'type' => 'string', 'enum' => [ 'on', '' ] ],
					'custom_attributes' => [ 'type' => 'string' ],
				];
				break;

			case 'media':
			case 'gallery':
				$prop['type']       = 'object';
				$prop['properties'] = [
					'url' => [ 'type' => 'string' ],
					'id'  => [ 'type' => 'integer' ],
				];
				break;

			case 'popover_toggle':
				$prop['type'] = 'string';
				$prop['enum'] = [ 'yes', '' ];
				break;

			case 'icons':
				$prop['type']       = 'object';
				$prop['properties'] = [
					'value'   => [ 'type' => 'string' ],
					'library' => [ 'type' => 'string' ],
				];
				break;

			case 'typography':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'custom', '' ];
				$prop['description'] = 'Typography toggle. Set to "custom" to enable font overrides. '
					. 'Then set {name}_font_family, {name}_font_size ({"size":16,"unit":"px"}), '
					. '{name}_font_weight (100-900), {name}_line_height, {name}_letter_spacing. '
					. 'These are auto-set by build-template/build-page when you provide the sub-keys.';
				break;

			case 'box_shadow':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'yes', '' ];
				$prop['description'] = 'Box shadow toggle. Sub-keys: {name}_box_shadow_type, '
					. '{name}_box_shadow ({"horizontal":0,"vertical":4,"blur":10,"spread":0,"color":"rgba(0,0,0,0.1)"}).';
				break;

			case 'border':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'solid', 'double', 'dotted', 'dashed', 'groove', '' ];
				$prop['description'] = 'Border type. Sub-keys: {name}_width (dimensions), {name}_color (hex).';
				break;

			case 'background':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'classic', 'gradient', '' ];
				$prop['description'] = 'Background type toggle. Set to "classic" then use {name}_color (hex). '
					. 'For gradient: set to "gradient" then use {name}_color, {name}_color_b, {name}_gradient_angle.';
				break;

			case 'text_shadow':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'yes', '' ];
				$prop['description'] = 'Text shadow toggle. Sub-keys: {name}_text_shadow '
					. '({"horizontal":0,"vertical":2,"blur":5,"color":"rgba(0,0,0,0.3)"}).';
				break;

			case 'css_filter':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'custom', '' ];
				$prop['description'] = 'CSS filter toggle. Sub-keys: {name}_blur, {name}_brightness, '
					. '{name}_contrast, {name}_saturate, {name}_hue.';
				break;

			case 'text_stroke':
				$prop['type']        = 'string';
				$prop['enum']        = [ 'yes', '' ];
				$prop['description'] = 'Text stroke toggle. Sub-keys: {name}_text_stroke '
					. '({"size":1,"unit":"px"}), {name}_stroke_color (hex).';
				break;

			case 'image_size':
				$prop['type']        = 'string';
				$prop['description'] = 'Image size preset (thumbnail, medium, large, full) or custom dimensions.';
				break;

			case 'repeater':
				$prop['type']        = 'array';
				$prop['description'] = 'Array of repeater items. Each item is an object with fields specific to this control. '
					. 'Call get-widget-schema to see the fields for each repeater item.';
				break;

			default:
				// Unknown type — include as string fallback.
				$prop['type'] = 'string';
				break;
		}

		// Set label as description only if we don't already have a more detailed one from group controls.
		if ( ! empty( $label ) && empty( $prop['description'] ) ) {
			$prop['description'] = $label;
		} elseif ( ! empty( $label ) && ! empty( $prop['description'] ) ) {
			// Prepend label to group control descriptions for context.
			$prop['description'] = $label . '. ' . $prop['description'];
		}

		if ( null !== $default && '' !== $default ) {
			$prop['default'] = $default;
		}

		// Include condition/dependency info so MCP clients know about toggle prerequisites.
		if ( ! empty( $ctrl['condition'] ) ) {
			$prop['depends_on'] = $ctrl['condition'];
		}

		// Include section grouping for context.
		if ( ! empty( $ctrl['section'] ) ) {
			$prop['section'] = $ctrl['section'];
		}

		return $prop;
	}

	// ──────────────────────────────────────────────
	// Allowed widget types.
	// ──────────────────────────────────────────────

	/**
	 * Get all widget types allowed for insertion into HFE templates.
	 *
	 * Includes HFE free widgets (always), Elementor core widgets (always),
	 * and UAE Pro widgets (when active). Filterable via `uae_mcp_allowed_template_widgets`.
	 *
	 * @return array Array of widget info: [ [ 'slug' => string, 'title' => string, 'source' => string ], ... ]
	 */
	public static function get_allowed_widget_types() {
		$widgets = [];

		// HFE free widgets — always available.
		$widgets = array_merge( $widgets, self::get_hfe_widget_types() );

		// Elementor core widgets — always available when Elementor is active.
		$widgets = array_merge( $widgets, self::get_elementor_core_widget_types() );

		// UAE Pro widgets — available when UAE Pro is active.
		if ( class_exists( '\UltimateElementor\Classes\UAEL_Config' ) ) {
			$widgets = array_merge( $widgets, self::get_uae_pro_widget_types() );
		}

		/**
		 * Filter the allowed widget types for template building.
		 *
		 * @since 2.9.0
		 *
		 * @param array $widgets Array of widget info arrays.
		 */
		return apply_filters( 'uae_mcp_allowed_template_widgets', $widgets );
	}

	/**
	 * Get allowed widget slugs as a flat array.
	 *
	 * @return array Array of widget type slugs.
	 */
	public static function get_allowed_widget_slugs() {
		return array_column( self::get_allowed_widget_types(), 'slug' );
	}

	/**
	 * Check if a widget type is allowed for insertion.
	 *
	 * @param string $widget_type Widget type slug.
	 * @return bool True if allowed.
	 */
	/**
	 * Get the pro alternative info for a free widget.
	 *
	 * Returns upgrade suggestion when a free widget has a more capable
	 * UAE Pro equivalent with additional features.
	 *
	 * @param string $widget_type Widget type slug.
	 * @return array|null Pro alternative info or null.
	 */
	public static function get_pro_alternative( $widget_type ) {
		$hfe_widgets = self::get_hfe_widget_types();

		foreach ( $hfe_widgets as $widget ) {
			if ( $widget['slug'] === $widget_type && ! empty( $widget['pro_alternative'] ) ) {
				return $widget['pro_alternative'];
			}
		}

		return null;
	}

	public static function is_widget_allowed( $widget_type ) {
		return in_array( $widget_type, self::get_allowed_widget_slugs(), true );
	}

	/**
	 * Get the source/plugin name for a widget type.
	 *
	 * @param string $widget_type Widget type slug.
	 * @return string Source name or empty string.
	 */
	public static function get_widget_source( $widget_type ) {
		$widgets = self::get_allowed_widget_types();

		foreach ( $widgets as $widget ) {
			if ( $widget['slug'] === $widget_type ) {
				return $widget['source'];
			}
		}

		return '';
	}

	/**
	 * Get HFE widget types including pro_alternative data.
	 *
	 * Public accessor for pro/features handler.
	 *
	 * @return array Widget info arrays with pro_alternative when available.
	 */
	public static function get_hfe_widget_types_with_pro() {
		return self::get_hfe_widget_types();
	}

	/**
	 * Get HFE free widget types.
	 *
	 * @return array Widget info arrays.
	 */
	private static function get_hfe_widget_types() {
		// Slugs match Widgets_Config::get_widget_list() — these are the Elementor widget type names.
		// pro_alternative: when the free widget can't do what the user asks, suggest the UAE Pro upgrade.
		$widgets = [
			[ 'slug' => 'retina',                'title' => 'Retina Logo' ],
			[ 'slug' => 'page-title',            'title' => 'Page Title' ],
			[ 'slug' => 'hfe-site-tagline',      'title' => 'Site Tagline' ],
			[ 'slug' => 'hfe-site-title',        'title' => 'Site Title' ],
			[ 'slug' => 'post-info-widget',      'title' => 'Post Info' ],
			[
				'slug'            => 'hfe-basic-posts',
				'title'           => 'Basic Posts',
				'pro_alternative' => [
					'widget'   => 'uael-posts',
					'plugin'   => 'Ultimate Addons for Elementor Pro',
					'features' => 'pagination, AJAX load more, infinite scroll, carousel, 5 layout skins, advanced query builder, post filtering UI, custom post type support',
				],
			],
			[ 'slug' => 'hfe-breadcrumbs-widget', 'title' => 'Breadcrumbs' ],
			[
				'slug'            => 'hfe-cart',
				'title'           => 'WooCommerce Cart',
				'pro_alternative' => [
					'widget'   => 'uael-woo-mini-cart',
					'plugin'   => 'Ultimate Addons for Elementor Pro',
					'features' => 'drawer animations, custom notification styling, advanced cart display options',
				],
			],
			[ 'slug' => 'copyright',             'title' => 'Copyright' ],
			[
				'slug'            => 'navigation-menu',
				'title'           => 'Navigation Menu',
				'pro_alternative' => [
					'widget'   => 'uael-nav-menu',
					'plugin'   => 'Ultimate Addons for Elementor Pro',
					'features' => 'mega menu, offcanvas mobile drawer, dropdown animations, full-width dropdowns, submenu icon customization',
				],
			],
			[ 'slug' => 'site-logo',             'title' => 'Site Logo' ],
			[
				'slug'            => 'hfe-infocard',
				'title'           => 'Info Card',
				'pro_alternative' => [
					'widget'   => 'uael-infobox',
					'plugin'   => 'Ultimate Addons for Elementor Pro',
					'features' => 'multiple icon positions (top/left/right), preset designs, advanced hover effects, number badges, gradient text, icon flip/rotation',
				],
			],
			[
				'slug'            => 'hfe-search-button',
				'title'           => 'Search',
				'pro_alternative' => [
					'widget'   => 'uael-advanced-search',
					'plugin'   => 'Ultimate Addons for Elementor Pro',
					'features' => 'AJAX live search results, search filters, custom post type search, multiple search layouts',
				],
			],
			[
				'slug'            => 'hfe-woo-product-grid',
				'title'           => 'WooCommerce Product Grid',
				'pro_alternative' => [
					'widget'   => 'uael-woo-products',
					'plugin'   => 'Ultimate Addons for Elementor Pro',
					'features' => 'pagination, AJAX load more, infinite scroll, carousel, quick view, sale/featured badges, advanced query filters, multiple skins',
				],
			],
			[ 'slug' => 'hfe-counter',           'title' => 'Counter' ],
		];

		foreach ( $widgets as &$w ) {
			$w['source'] = 'header-footer-elementor';
		}

		return $widgets;
	}

	/**
	 * Get Elementor core widget types commonly used in headers/footers.
	 *
	 * @return array Widget info arrays.
	 */
	private static function get_elementor_core_widget_types() {
		$widgets = [
			[ 'slug' => 'heading',     'title' => 'Heading' ],
			[ 'slug' => 'text-editor', 'title' => 'Text Editor' ],
			[ 'slug' => 'image',       'title' => 'Image' ],
			[ 'slug' => 'button',      'title' => 'Button' ],
			[ 'slug' => 'icon',        'title' => 'Icon' ],
			[ 'slug' => 'icon-list',   'title' => 'Icon List' ],
			[ 'slug' => 'spacer',      'title' => 'Spacer' ],
			[ 'slug' => 'divider',     'title' => 'Divider' ],
			[ 'slug' => 'html',        'title' => 'HTML' ],
			[ 'slug' => 'shortcode',   'title' => 'Shortcode' ],
			[ 'slug' => 'social-icons', 'title' => 'Social Icons' ],
		];

		foreach ( $widgets as &$w ) {
			$w['source'] = 'elementor';
		}

		return $widgets;
	}

	/**
	 * Get UAE Pro widget types.
	 *
	 * @return array Widget info arrays.
	 */
	private static function get_uae_pro_widget_types() {
		if ( ! class_exists( '\UltimateElementor\Classes\UAEL_Config' ) ) {
			return [];
		}

		$widgets = [];

		$uae_widgets = \UltimateElementor\Classes\UAEL_Config::get_widget_list();

		if ( ! empty( $uae_widgets ) && is_array( $uae_widgets ) ) {
			foreach ( $uae_widgets as $key => $config ) {
				if ( ! empty( $config['slug'] ) ) {
					$widgets[] = [
						'slug'   => $config['slug'],
						'title'  => $config['title'] ?? $key,
						'source' => 'ultimate-addons-for-elementor',
					];
				}
			}
		}

		return $widgets;
	}

	/**
	 * Determine which plugin a widget type requires and whether it's active.
	 *
	 * @param string $widget_type Widget type slug.
	 * @return array [ 'required_plugin' => string, 'is_active' => bool ]
	 */
	public static function check_widget_requirements( $widget_type ) {
		// Check HFE widgets.
		$hfe_slugs = array_column( self::get_hfe_widget_types(), 'slug' );
		if ( in_array( $widget_type, $hfe_slugs, true ) ) {
			return [
				'required_plugin' => 'Header Footer Elementor',
				'is_active'       => true,
			];
		}

		// Check Elementor core widgets.
		$elementor_slugs = array_column( self::get_elementor_core_widget_types(), 'slug' );
		if ( in_array( $widget_type, $elementor_slugs, true ) ) {
			return [
				'required_plugin' => 'Elementor',
				'is_active'       => class_exists( '\Elementor\Plugin' ),
			];
		}

		// Check UAE Pro widgets.
		if ( 0 === strpos( $widget_type, 'uael-' ) ) {
			return [
				'required_plugin' => 'Ultimate Addons for Elementor Pro',
				'is_active'       => class_exists( '\UltimateElementor\Classes\UAEL_Config' ),
			];
		}

		return [
			'required_plugin' => '',
			'is_active'       => false,
		];
	}

	/**
	 * Normalize Elementor settings by auto-setting required toggle keys.
	 *
	 * Elementor uses a pattern where feature groups must be "activated" before
	 * their values take effect. For example, `background_color` is ignored
	 * unless `background_background` is set to "classic". This method
	 * auto-sets those toggle keys when their dependent values are present.
	 *
	 * @param array $settings Raw settings array.
	 * @return array Normalized settings with toggle keys auto-set.
	 */
	public static function normalize_elementor_settings( $settings ) {
		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return $settings;
		}

		// Background: if background_color or background_image is set, activate classic background.
		if ( isset( $settings['background_color'] ) || isset( $settings['background_image'] ) ) {
			if ( ! isset( $settings['background_background'] ) ) {
				$settings['background_background'] = 'classic';
			}
		}

		// Background overlay: same pattern with background_overlay_ prefix.
		if ( isset( $settings['background_overlay_color'] ) || isset( $settings['background_overlay_image'] ) ) {
			if ( ! isset( $settings['background_overlay_background'] ) ) {
				$settings['background_overlay_background'] = 'classic';
			}
		}

		// Border: if border_width or border_color is set, activate border.
		if ( isset( $settings['border_width'] ) || isset( $settings['border_color'] ) ) {
			if ( ! isset( $settings['border_border'] ) ) {
				$settings['border_border'] = 'solid';
			}
		}

		// Typography toggles: scan for *_font_family, *_font_size, *_font_weight
		// and auto-set the corresponding *_typography to "custom".
		foreach ( $settings as $key => $value ) {
			if ( preg_match( '/^(.+)_(font_family|font_size|font_weight|line_height|letter_spacing)$/', $key, $matches ) ) {
				$toggle_key = $matches[1] . '_typography';
				if ( ! isset( $settings[ $toggle_key ] ) ) {
					$settings[ $toggle_key ] = 'custom';
				}
			}
		}

		return $settings;
	}
}
