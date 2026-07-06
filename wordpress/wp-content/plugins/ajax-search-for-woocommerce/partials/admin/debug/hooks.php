<?php
// Exit if accessed directly.
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

if ( ! class_exists( 'DGWT_WCAS_Debug_Hooks' ) ) {
	class DGWT_WCAS_Debug_Hooks {

		private const MAX_BYTES     = 131072;
		private const ALLOWED_FILES = [ 'fibosearch.php', 'asfw-filters.php', 'ajax-search-for-woocommerce.php', 'ajax-search-filters.php' ];
		private const HOOK_PATTERNS = [
			'/\b(add_action|add_filter)\s*\(\s*(["\'])(dgwt\/wcas\/[^"\']+|fibosearch\/[^"\']+)\2/mi',
			'/\b(do_action|apply_filters)\s*\(\s*(["\'])(dgwt\/wcas\/[^"\']+|fibosearch\/[^"\']+)\2/mi',
		];

		private array $roots = [];

		public static function render(): string {
			$self = new self();

			return $self->render_html( $self->collect_entries() );
		}

		private function __construct() {
			$this->roots = $this->collect_roots();
		}

		private function collect_roots(): array {
			$roots = [];

			if ( defined( 'WP_CONTENT_DIR' ) ) {
				$this->add_root( $roots, WP_CONTENT_DIR, 'wp-content' );
			}

			$stylesheet_dir  = get_stylesheet_directory();
			$template_dir    = get_template_directory();
			$stylesheet_real = realpath( $stylesheet_dir );
			$template_real   = realpath( $template_dir );

			if ( $stylesheet_real && $template_real && self::normalize_path( $stylesheet_real ) === self::normalize_path( $template_real ) ) {
				$this->add_root( $roots, $template_dir, 'Theme' );
			} else {
				$this->add_root( $roots, $stylesheet_dir, 'Child theme' );
				$this->add_root( $roots, $template_dir, 'Parent theme' );
			}

			return $roots;
		}

		private function add_root( array &$roots, string $path, string $label ): void {
			$real = realpath( $path );
			if ( ! $real || ! is_dir( $real ) ) {
				return;
			}

			$real = self::normalize_path( $real );

			if ( ! isset( $roots[ $real ] ) ) {
				$roots[ $real ] = $label;
			}
		}

		private function collect_entries(): array {
			$entries = [];
			$seen    = [];

			foreach ( $this->roots as $root => $label ) {
				foreach ( self::ALLOWED_FILES as $name ) {
					$file = $this->resolve_file_path( $root, $name );
					if ( ! $file || isset( $seen[ $file ] ) ) {
						continue;
					}
					$seen[ $file ] = true;

					$entries[] = $this->build_entry( $file, $label );
				}
			}

			return $entries;
		}

		private function build_entry( string $file, string $label ): array {
			$content = $this->read_file( $file );
			$hooks   = $content !== null ? $this->extract_hooks( $content ) : [];

			return [
				'path'           => $file,
				'masked_path'    => $this->mask_path( $file ),
				'location_label' => $label,
				'hooks'          => $hooks,
				'readable'       => $content !== null,
			];
		}

		private function resolve_file_path( string $root, string $name ): ?string {
			if ( ! in_array( $name, self::ALLOWED_FILES, true ) ) {
				return null;
			}

			if ( $name === '' || $name !== basename( $name ) ) {
				return null;
			}

			$root_dir = self::ensure_trailing_slash( $root );
			$file     = $root_dir . $name;

			if ( ! is_file( $file ) || ! is_readable( $file ) || is_link( $file ) ) {
				return null;
			}

			$real_file = realpath( $file );
			if ( ! $real_file ) {
				return null;
			}

			$real_file = self::normalize_path( $real_file );
			$real_root = self::ensure_trailing_slash( self::normalize_path( $root_dir ) );

			if ( strpos( $real_file, $real_root ) !== 0 ) {
				return null;
			}

			if ( basename( $real_file ) !== $name ) {
				return null;
			}

			return $real_file;
		}

		private function read_file( string $file ): ?string {
			if ( ! $this->ensure_wp_filesystem() ) {
				return null;
			}

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				return null;
			}

			$size = method_exists( $wp_filesystem, 'size' ) ? $wp_filesystem->size( $file ) : false;
			if ( $size === false || $size > self::MAX_BYTES ) {
				return null;
			}

			$data = $wp_filesystem->get_contents( $file );
			if ( $data === false || strlen( $data ) > self::MAX_BYTES ) {
				return null;
			}

			$data = str_replace( [ "\r\n", "\r" ], "\n", $data );

			if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $data ) ) {
				return null;
			}

			return $data;
		}

		private function extract_hooks( string $code ): array {
			$hooks = [];

			foreach ( self::HOOK_PATTERNS as $pattern ) {
				if ( preg_match_all( $pattern, $code, $m, PREG_OFFSET_CAPTURE ) ) {
					foreach ( $m[0] as $i => $_ ) {
						$context = $m[1][ $i ][0];
						$name    = $m[3][ $i ][0];
						$offset  = (int) $m[0][ $i ][1];
						$line    = self::offset_to_line( $code, $offset );

						$hooks[] = [
							'name'    => $name,
							'context' => $context,
							'line'    => $line,
						];
					}
				}
			}

			$hooks = $this->dedupe_hooks( $hooks );

			usort(
				$hooks,
				static function ( array $a, array $b ): int {
					$line_a = (int) ( $a['line'] ?? 0 );
					$line_b = (int) ( $b['line'] ?? 0 );

					if ( $line_a === $line_b ) {
						return strcmp( (string) ( $a['name'] ?? '' ), (string) ( $b['name'] ?? '' ) );
					}

					return $line_a <=> $line_b;
				}
			);

			return $hooks;
		}

		private function dedupe_hooks( array $hooks ): array {
			$result = [];
			$seen   = [];

			foreach ( $hooks as $hook ) {
				$key = ( $hook['context'] ?? '' ) . '|' . ( $hook['name'] ?? '' ) . '|' . (int) ( $hook['line'] ?? 0 );
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;
				$result[]     = $hook;
			}

			return $result;
		}

		private function mask_path( string $path ): string {
			$real = realpath( $path );
			$wc   = defined( 'WP_CONTENT_DIR' ) ? realpath( WP_CONTENT_DIR ) : null;

			if ( $real ) {
				$real = self::normalize_path( $real );
			}
			if ( $wc ) {
				$wc = self::normalize_path( $wc );
			}

			if ( $real && $wc ) {
				$wc_slash = self::ensure_trailing_slash( $wc );

				if ( strpos( $real, $wc_slash ) === 0 ) {
					return '...' . substr( $real, strlen( $wc ) );
				}
			}

			$len = strlen( $path );

			return '...' . substr( $path, max( 0, $len - 40 ) );
		}

		private function render_html( array $entries ): string {
			ob_start();
			?>
			<div class="dgwt-wcas-debug-hooks">
				<h3><?php esc_html_e( 'Hooks', 'ajax-search-for-woocommerce' ); ?></h3>

				<div class="dgwt-wcas-debug-hooks__panel">
					<?php if ( empty( $entries ) ) : ?>
						<p class="description dgwt-wcas-debug-hooks__empty">
							<?php esc_html_e( 'No allowed hooks file was found in supported locations.', 'ajax-search-for-woocommerce' ); ?>
						</p>
					<?php else : ?>
						<?php foreach ( $entries as $entry ) : ?>
							<?php $this->render_entry( $entry ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<?php

			return (string) ob_get_clean();
		}

		private function render_entry( array $entry ): void {
			$path      = isset( $entry['path'] ) ? (string) $entry['path'] : '';
			$file      = $this->file_label( $path );
			$loc_label = isset( $entry['location_label'] ) ? (string) $entry['location_label'] : '';
			$masked    = isset( $entry['masked_path'] ) ? (string) $entry['masked_path'] : '';
			$hooks     = ( isset( $entry['hooks'] ) && is_array( $entry['hooks'] ) ) ? $entry['hooks'] : [];
			$readable  = ! empty( $entry['readable'] );
			?>
			<div class="dgwt-wcas-debug-hooks__card">
				<h4 class="dgwt-wcas-debug-hooks__head">
					<code class="dgwt-wcas-debug-hooks__filename"><?php echo self::esc( $file ); ?></code>
					<?php if ( $loc_label !== '' ) : ?>
						<span class="dgwt-wcas-debug-hooks__location">— <?php echo self::esc( $loc_label ); ?></span>
					<?php endif; ?>
				</h4>

				<div class="dgwt-wcas-debug-hooks__chips">
					<span class="dgwt-wcas-debug-hooks__chip">
						<?php esc_html_e( 'Path:', 'ajax-search-for-woocommerce' ); ?>
						<code><?php echo self::esc( $masked ); ?></code>
					</span>
					<span class="dgwt-wcas-debug-hooks__chip">
						<?php esc_html_e( 'Hooks:', 'ajax-search-for-woocommerce' ); ?>
						<?php echo (int) count( $hooks ); ?>
					</span>
				</div>

				<?php if ( ! $readable ) : ?>
					<p class="description dgwt-wcas-debug-hooks__empty">
						<?php esc_html_e( 'File found, but it could not be read (permissions or file too large).', 'ajax-search-for-woocommerce' ); ?>
					</p>
				<?php elseif ( ! empty( $hooks ) ) : ?>
					<?php $this->render_hooks_table( $hooks ); ?>
				<?php else : ?>
					<p class="description dgwt-wcas-debug-hooks__empty">
						<?php esc_html_e( 'No hooks with dgwt/wcas/* or fibosearch/* prefixes were found.', 'ajax-search-for-woocommerce' ); ?>
					</p>
				<?php endif; ?>
			</div>
			<?php
		}

		private function render_hooks_table( array $hooks ): void {
			?>
			<table class="widefat striped dgwt-wcas-debug-hooks__table" role="table" aria-label="<?php esc_attr_e( 'Detected hooks', 'ajax-search-for-woocommerce' ); ?>">
				<thead>
				<tr>
					<th>#</th>
					<th><?php esc_html_e( 'Hook', 'ajax-search-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Call', 'ajax-search-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Line', 'ajax-search-for-woocommerce' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $hooks as $idx => $hook ) : ?>
					<tr>
						<td><?php echo (int) ( $idx + 1 ); ?></td>
						<td><code><?php echo self::esc( $hook['name'] ?? '' ); ?></code></td>
						<td><?php echo self::esc( $hook['context'] ?? '' ); ?></td>
						<td><?php echo (int) ( $hook['line'] ?? 0 ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		}

		private function file_label( string $path ): string {
			if ( $path === '' ) {
				return '';
			}

			if ( function_exists( 'wp_basename' ) ) {
				return wp_basename( $path );
			}

			return basename( $path );
		}

		private function ensure_wp_filesystem(): bool {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				$wp_file = defined( 'ABSPATH' ) ? ABSPATH . 'wp-admin/includes/file.php' : '';
				if ( $wp_file !== '' && file_exists( $wp_file ) ) {
					require_once $wp_file;
				}
			}

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				return false;
			}

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				WP_Filesystem();
			}

			return (bool) $wp_filesystem;
		}

		private static function normalize_path( string $path ): string {
			if ( function_exists( 'wp_normalize_path' ) ) {
				return wp_normalize_path( $path );
			}

			return str_replace( '\\', '/', $path );
		}

		private static function ensure_trailing_slash( string $path ): string {
			if ( function_exists( 'trailingslashit' ) ) {
				return trailingslashit( $path );
			}

			return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		}

		private static function offset_to_line( string $text, int $offset ): int {
			$prefix = substr( $text, 0, max( 0, $offset ) );

			return substr_count( $prefix, "\n" ) + 1;
		}

		private static function esc( $value ): string {
			if ( function_exists( 'esc_html' ) ) {
				return esc_html( (string) $value );
			}

			return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
		}
	}
}

echo DGWT_WCAS_Debug_Hooks::render();
