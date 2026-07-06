<?php
/**
 * Ability Registry.
 *
 * Central registry for all HFE ability handlers. Manages handler storage,
 * disabled-ability filtering, and WP Abilities API registration.
 *
 * Feeds all three platforms:
 * 1. WP Abilities API (wp_register_ability)
 * 2. MCP Adapter ($adapter->create_server)
 * 3. Angie (REST routes)
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Ability_Registry
 *
 * @since 2.9.0
 */
class HFE_Ability_Registry {

	/**
	 * Plugin prefix for ability names.
	 *
	 * @var string
	 */
	const PREFIX = 'uae/';

	/**
	 * Registered handlers keyed by ability name (without prefix).
	 *
	 * @var array<string, array{handler: HFE_Ability_Handler, source: string}>
	 */
	private $handlers = [];

	/**
	 * Registered WP ability names (full, with prefix) after discover().
	 *
	 * @var string[]
	 */
	private $registered_names = [];

	/**
	 * Register a handler.
	 *
	 * Accepts any object implementing the handler contract — get_name(),
	 * get_registration_args(), execute() — rather than a strict
	 * HFE_Ability_Handler type hint. Add-ons such as UAE Pro define their own
	 * handler interface that only extends HFE_Ability_Handler when HFE's
	 * interface is already loaded; depending on plugin load order that is not
	 * guaranteed. A strict hint would throw a TypeError here and abort
	 * registration of EVERY ability (the "0 abilities" symptom on sites running
	 * both plugins). Duck-typing the contract keeps dual-plugin registration
	 * load-order independent.
	 *
	 * @param object $handler Handler instance implementing the ability contract.
	 * @param string $source  Source identifier (e.g., 'hfe', 'uael').
	 * @return void
	 */
	public function register( $handler, $source = 'hfe' ) {
		if ( ! is_object( $handler )
			|| ! method_exists( $handler, 'get_name' )
			|| ! method_exists( $handler, 'get_registration_args' )
			|| ! method_exists( $handler, 'execute' ) ) {
			_doing_it_wrong(
				__METHOD__,
				esc_html__( 'Invalid ability handler: it must implement get_name(), get_registration_args(), and execute().', 'header-footer-elementor' ),
				'2.8.6'
			);
			return;
		}

		$name = $handler->get_name();

		// Defensive: reject duplicate ability names so a later registration
		// (e.g. from UAE Pro) can't silently shadow an existing one. The
		// first source to register a name wins.
		if ( isset( $this->handlers[ $name ] ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: 1: ability name, 2: source attempting to register, 3: existing source */
					esc_html__( 'Ability "%1$s" already registered by "%3$s"; ignoring duplicate registration from "%2$s".', 'header-footer-elementor' ),
					esc_html( $name ),
					esc_html( $source ),
					esc_html( $this->handlers[ $name ]['source'] )
				),
				'2.8.5'
			);
			return;
		}

		$this->handlers[ $name ] = [
			'handler' => $handler,
			'source'  => $source,
		];
	}

	/**
	 * Discover all enabled handlers and register them with WP Abilities API.
	 *
	 * Respects:
	 * - disabled_abilities setting (per-ability toggles from settings page)
	 * - allow_modifications setting (gates write abilities)
	 *
	 * @return string[] Array of registered ability names (with prefix).
	 */
	public function discover() {
		$settings            = get_option( 'uae_mcp_settings', [] );
		$allow_modifications = ! empty( $settings['allow_modifications'] );
		$disabled_abilities  = ! empty( $settings['disabled_abilities'] ) && is_array( $settings['disabled_abilities'] )
			? $settings['disabled_abilities']
			: [];

		$this->registered_names = [];

		// Register real handlers.
		foreach ( $this->handlers as $name => $entry ) {
			
			$full_name = self::PREFIX . $name;

			// Check per-ability toggle.
			if ( in_array( $name, $disabled_abilities, true ) || in_array( $full_name, $disabled_abilities, true ) ) {
				continue;
			}

			$handler = $entry['handler'];
			$args    = $handler->get_registration_args();

			// Gate write abilities behind allow_modifications.
			if ( ! $allow_modifications ) {
				$is_readonly = ! empty( $args['meta']['annotations']['readonly'] );

				if ( ! $is_readonly ) {
					continue;
				}
			}

			// Set execute callback to route through registry.
			$args['execute_callback'] = [ $handler, 'execute' ];

			wp_register_ability( $full_name, $args );
			$this->registered_names[] = $full_name;
		}

		return $this->registered_names;
	}

	/**
	 * Execute an ability by name.
	 *
	 * Looks up the handler by name and executes it.
	 *
	 * @param string $name   Ability name (with or without prefix).
	 * @param array  $params Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $name, $params = [] ) {
		// Strip prefix if present.
		$short_name = 0 === strpos( $name, self::PREFIX )
			? substr( $name, strlen( self::PREFIX ) )
			: $name;

		// Verify the ability passed discover() gating.
		if ( ! $this->is_registered( $short_name ) ) {
			$reason      = $this->get_gate_reason( $short_name );
			$settings_url = admin_url( 'admin.php?page=hfe#settings' );

			if ( 'allow_modifications' === $reason ) {
				$message = sprintf(
					/* translators: 1: ability name, 2: settings URL */
					__( 'Ability "%1$s" requires "Allow Modifications" to be enabled. Turn it on at: %2$s', 'header-footer-elementor' ),
					$name,
					$settings_url
				);
			} else {
				$message = sprintf(
					/* translators: 1: ability name, 2: settings URL */
					__( 'Ability "%1$s" is currently disabled. Enable it in AI Tools settings: %2$s', 'header-footer-elementor' ),
					$name,
					$settings_url
				);
			}

			return new \WP_Error(
				'hfe_ability_disabled',
				$message,
				[
					'status'       => 403,
					'reason'       => $reason,
					'settings_url' => $settings_url,
				]
			);
		}

		if ( isset( $this->handlers[ $short_name ] ) ) {
			return $this->handlers[ $short_name ]['handler']->execute( $params );
		}

		return new \WP_Error(
			'hfe_ability_not_found',
			/* translators: %s: ability name */
			sprintf( __( 'Ability "%s" not found.', 'header-footer-elementor' ), $name ),
			[ 'status' => 404 ]
		);
	}

	/**
	 * Get all registered handler names (without prefix).
	 *
	 * @return string[]
	 */
	public function get_handler_names() {
		return array_keys( $this->handlers );
	}

	/**
	 * Get all registered WP ability names (with prefix) after discover().
	 *
	 * @return string[]
	 */
	public function get_registered_names() {
		return $this->registered_names;
	}

	/**
	 * Check if an ability is registered (passed discover() gating).
	 *
	 * @param string $name Handler name (without prefix).
	 * @return bool
	 */
	public function is_registered( $name ) {
		
		$full_name = self::PREFIX . $name;
		return in_array( $full_name, $this->registered_names, true );
	}

	/**
	 * Get the reason an ability was gated by discover().
	 *
	 * @param string $name Handler name (without prefix).
	 * @return string 'allow_modifications'|'disabled'|'unknown'
	 */
	public function get_gate_reason( $name ) {
		if ( ! isset( $this->handlers[ $name ] ) ) {
			return 'unknown';
		}

		$settings           = get_option( 'uae_mcp_settings', [] );
		$disabled_abilities = ! empty( $settings['disabled_abilities'] ) && is_array( $settings['disabled_abilities'] )
			? $settings['disabled_abilities']
			: [];

		
		$full_name = self::PREFIX . $name;

		if ( in_array( $name, $disabled_abilities, true ) || in_array( $full_name, $disabled_abilities, true ) ) {
			return 'disabled';
		}

		$allow_modifications = ! empty( $settings['allow_modifications'] );

		if ( ! $allow_modifications ) {
			$args        = $this->handlers[ $name ]['handler']->get_registration_args();
			$is_readonly = ! empty( $args['meta']['annotations']['readonly'] );

			if ( ! $is_readonly ) {
				return 'allow_modifications';
			}
		}

		return 'unknown';
	}

	/**
	 * Get a handler by name.
	 *
	 * @param string $name Handler name (without prefix).
	 * @return HFE_Ability_Handler|null
	 */
	public function get_handler( $name ) {
		return isset( $this->handlers[ $name ] ) ? $this->handlers[ $name ]['handler'] : null;
	}

	/**
	 * Get all handlers grouped by namespace.
	 *
	 * @return array<string, array<string, HFE_Ability_Handler>>
	 */
	public function get_handlers_by_namespace() {
		$grouped = [];

		foreach ( $this->handlers as $name => $entry ) {
			$parts     = explode( '/', $name, 2 );
			$namespace = $parts[0];

			if ( ! isset( $grouped[ $namespace ] ) ) {
				$grouped[ $namespace ] = [];
			}

			$grouped[ $namespace ][ $name ] = $entry['handler'];
		}

		return $grouped;
	}

	/**
	 * Get all handlers with their registration args (for settings UI / REST).
	 *
	 * @return array Array of ability info objects.
	 */
	public function get_abilities_info() {
		$abilities = [];

		foreach ( $this->handlers as $name => $entry ) {
			$handler = $entry['handler'];
			$args    = $handler->get_registration_args();

			$abilities[] = [
				'name'        => $name,
				'full_name'   => self::PREFIX . $name,
				'label'       => $args['label'] ?? '',
				'description' => $args['description'] ?? '',
				'category'    => $args['category'] ?? '',
				'source'      => $entry['source'],
				'readonly'    => ! empty( $args['meta']['annotations']['readonly'] ),
				'destructive' => ! empty( $args['meta']['annotations']['destructive'] ),
				];
		}

		return $abilities;
	}

}
