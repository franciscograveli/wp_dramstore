<?php
/**
 * Abilities API Loader.
 *
 * Registers ability categories, bootstraps the handler registry,
 * and wires all three platforms (WP Abilities API, MCP Adapter, Angie).
 * Requires WordPress 6.9+ (Abilities API).
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Abilities_Loader
 *
 * Bootstraps HFE ability categories, handler registry, and platform integrations.
 *
 * @since 2.9.0
 */
class HFE_Abilities_Loader {

	/**
	 * Singleton instance.
	 *
	 * @var HFE_Abilities_Loader|null
	 */
	private static $instance = null;

	/**
	 * Ability registry instance.
	 *
	 * @var HFE_Ability_Registry
	 */
	private $registry;

	/**
	 * Get singleton instance.
	 *
	 * @return HFE_Abilities_Loader
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get the registry instance.
	 *
	 * @return HFE_Ability_Registry
	 */
	public function get_registry() {
		return $this->registry;
	}

	/**
	 * Constructor. Hooks into Abilities API init actions.
	 *
	 * The framework files and registry instance are loaded unconditionally so
	 * the settings UI can introspect the ability catalogue even when AI tools
	 * are disabled. Side effects (registering abilities with WordPress, creating
	 * an MCP server, booting Angie) are only attached when the master switch
	 * uae_mcp_settings.enable_abilities is on — Astra-style opt-in.
	 */
	private function __construct() {
		$this->load_framework();

		$this->registry = new HFE_Ability_Registry();

		$settings         = get_option( 'uae_mcp_settings', null );
		$enable_abilities = is_array( $settings ) ? ! empty( $settings['enable_abilities'] ) : false;

		if ( ! $enable_abilities ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_categories' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );

		// Standalone MCP server — only when the user explicitly opts in.
		if ( ! empty( $settings['dedicated_server'] ) ) {
			add_action( 'mcp_adapter_init', [ $this, 'register_mcp_server' ], 20 );
		}

		// Angie — only when the user explicitly opts in. Initialised here so its
		// rest_api_init and admin_enqueue_scripts hooks land in time.
		if ( ! empty( $settings['angie_enabled'] ) ) {
			$this->init_angie();
		}
	}

	/**
	 * Load framework files: interface, registry, trait, helpers.
	 *
	 * @return void
	 */
	private function load_framework() {
		$base = HFE_DIR . 'inc/abilities/';

		require_once $base . 'contracts/interface-hfe-ability-handler.php';
		require_once $base . 'registry/class-hfe-ability-registry.php';
		require_once $base . 'trait-hfe-abilities-helpers.php';
		require_once $base . 'class-hfe-element-helpers.php';
	}

	/**
	 * Register all HFE ability categories.
	 *
	 * @return void
	 */
	public function register_categories() {
		$categories = [
			'hfe-info'             => [
				'label'       => __( 'UAE Plugin Information', 'header-footer-elementor' ),
				'description' => __( 'Read-only info: version, health, hooks.', 'header-footer-elementor' ),
			],
			'hfe-templates'        => [
				'label'       => __( 'UAE Template Management', 'header-footer-elementor' ),
				'description' => __( 'CRUD for header/footer/block templates.', 'header-footer-elementor' ),
			],
			'hfe-widgets'          => [
				'label'       => __( 'UAE Widget Management', 'header-footer-elementor' ),
				'description' => __( 'Enable/disable Elementor widgets.', 'header-footer-elementor' ),
			],
			'hfe-extensions'       => [
				'label'       => __( 'UAE Extension Management', 'header-footer-elementor' ),
				'description' => __( 'Toggle Scroll to Top / Progress Bar.', 'header-footer-elementor' ),
			],
			'hfe-display-rules'    => [
				'label'       => __( 'UAE Display Rules', 'header-footer-elementor' ),
				'description' => __( 'Where and to whom templates appear.', 'header-footer-elementor' ),
			],
			'hfe-active-templates' => [
				'label'       => __( 'UAE Active Templates', 'header-footer-elementor' ),
				'description' => __( 'Which templates render on a given page.', 'header-footer-elementor' ),
			],
			'hfe-theme-compat'     => [
				'label'       => __( 'UAE Theme Compatibility', 'header-footer-elementor' ),
				'description' => __( 'Theme detection and fallback config.', 'header-footer-elementor' ),
			],
			'hfe-settings'         => [
				'label'       => __( 'UAE Plugin Settings', 'header-footer-elementor' ),
				'description' => __( 'Plugin-level configuration.', 'header-footer-elementor' ),
			],
			'hfe-shortcodes'       => [
				'label'       => __( 'UAE Template Embedding', 'header-footer-elementor' ),
				'description' => __( 'Shortcode strings and rendered HTML.', 'header-footer-elementor' ),
			],
			'hfe-pages'            => [
				'label'       => __( 'UAE Page Builder', 'header-footer-elementor' ),
				'description' => __( 'Build and manage page content with HFE and Elementor widgets.', 'header-footer-elementor' ),
			],
			'hfe-design'           => [
				'label'       => __( 'UAE Design System', 'header-footer-elementor' ),
				'description' => __( 'Read the site\'s global colors, fonts, and spacing.', 'header-footer-elementor' ),
			],
			'hfe-maintenance'      => [
				'label'       => __( 'UAE Maintenance', 'header-footer-elementor' ),
				'description' => __( 'Cache clearing and maintenance tasks.', 'header-footer-elementor' ),
			],
			'hfe-pro'              => [
				'label'       => __( 'UAE Pro Features', 'header-footer-elementor' ),
				'description' => __( 'Information about UAE Pro premium widgets and upgrade path.', 'header-footer-elementor' ),
			],
		];

		// The hfe-pro category only holds the Lite upsell ability, which is not
		// registered when UAE Pro is active — drop the empty category to match.
		if ( defined( 'UAEL_VER' ) ) {
			unset( $categories['hfe-pro'] );
		}

		foreach ( $categories as $slug => $args ) {
			wp_register_ability_category( $slug, $args );
		}
	}

	/**
	 * Ensure handler classes are loaded and registered in the registry.
	 *
	 * Safe to call multiple times — skips if handlers are already populated.
	 * Does NOT call discover() or wp_register_ability(); use register_abilities()
	 * for full registration with the WP Abilities API.
	 *
	 * @return void
	 */
	public function ensure_handlers_loaded() {
		if ( ! empty( $this->registry->get_handler_names() ) ) {
			return;
		}

		$this->load_handlers();
		$this->register_handlers();

		/** This action is documented in register_abilities(). */
		do_action( 'uae_mcp_register_abilities', $this->registry );
	}

	/**
	 * Load handler files, register handlers, and discover abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		$this->load_handlers();
		$this->register_handlers();

		/**
		 * Allow Pro (or other add-ons) to register additional handlers.
		 *
		 * When both HFE and Pro are active, Pro hooks here to add its
		 * 12 Pro-specific handlers. They appear on HFE's MCP server
		 * and Angie server alongside HFE's abilities.
		 *
		 * @param HFE_Ability_Registry $registry The HFE ability registry.
		 */
		do_action( 'uae_mcp_register_abilities', $this->registry );

		// Discover registers all enabled handlers with wp_register_ability().
		$this->registry->discover();
	}

	/**
	 * Require all handler class files.
	 *
	 * @return void
	 */
	private function load_handlers() {
		$base = HFE_DIR . 'inc/abilities/handlers/';

		// Info.
		require_once $base . 'info/class-hfe-info-get-handler.php';

		// Active templates.
		require_once $base . 'active/class-hfe-active-get-handler.php';

		// Templates.
		require_once $base . 'templates/class-hfe-template-list-handler.php';
		require_once $base . 'templates/class-hfe-template-get-handler.php';
		require_once $base . 'templates/class-hfe-template-create-handler.php';
		require_once $base . 'templates/class-hfe-template-update-handler.php';
		require_once $base . 'templates/class-hfe-template-delete-handler.php';
		require_once $base . 'templates/class-hfe-template-restore-handler.php';
		require_once $base . 'templates/class-hfe-template-duplicate-handler.php';

		// Pages.
		require_once $base . 'pages/class-hfe-page-create-handler.php';
		require_once $base . 'pages/class-hfe-page-list-handler.php';
		require_once $base . 'pages/class-hfe-page-delete-handler.php';
		require_once $base . 'pages/class-hfe-page-restore-handler.php';
		require_once $base . 'pages/class-hfe-page-update-status-handler.php';
		require_once $base . 'pages/class-hfe-page-update-meta-handler.php';

		// Widgets.
		require_once $base . 'widgets/class-hfe-widget-list-handler.php';
		require_once $base . 'widgets/class-hfe-widget-activate-handler.php';
		require_once $base . 'widgets/class-hfe-widget-deactivate-handler.php';
		require_once $base . 'widgets/class-hfe-widget-bulk-toggle-handler.php';
		require_once $base . 'widgets/class-hfe-widget-deactivate-unused-handler.php';
		require_once $base . 'widgets/class-hfe-widget-usage-handler.php';

		// Extensions.
		require_once $base . 'extensions/class-hfe-extension-list-handler.php';
		require_once $base . 'extensions/class-hfe-extension-toggle-handler.php';

		// Display Rules.
		require_once $base . 'display-rules/class-hfe-display-rules-update-handler.php';
		require_once $base . 'display-rules/class-hfe-display-rules-locations-handler.php';

		// Builder (unified template-builder + page-builder).
		require_once $base . 'builder/class-hfe-build-handler.php';
		require_once $base . 'builder/class-hfe-insert-widget-handler.php';
		require_once $base . 'builder/class-hfe-update-widget-handler.php';
		require_once $base . 'builder/class-hfe-remove-element-handler.php';
		require_once $base . 'builder/class-hfe-move-element-handler.php';
		require_once $base . 'builder/class-hfe-structure-handler.php';
		require_once $base . 'builder/class-hfe-add-section-handler.php';
		require_once $base . 'builder/class-hfe-add-column-handler.php';
		require_once $base . 'builder/class-hfe-css-handler.php';
		require_once $base . 'builder/class-hfe-widget-types-handler.php';
		require_once $base . 'builder/class-hfe-schema-handler.php';
		require_once $base . 'builder/class-hfe-undo-handler.php';

		// Theme.
		require_once $base . 'theme/class-hfe-theme-info-handler.php';
		require_once $base . 'theme/class-hfe-theme-method-handler.php';

		// Settings.
		require_once $base . 'settings/class-hfe-settings-get-handler.php';
		require_once $base . 'settings/class-hfe-settings-update-handler.php';

		// Shortcode.
		require_once $base . 'shortcode/class-hfe-shortcode-render-handler.php';

		// Design System.
		require_once $base . 'design-system/class-hfe-design-tokens-handler.php';

		// Maintenance.
		require_once $base . 'maintenance/class-hfe-maintenance-clear-cache-handler.php';

		// Pro.
		require_once $base . 'pro/class-hfe-pro-features-handler.php';
	}

	/**
	 * Register all handler instances with the registry.
	 *
	 * @return void
	 */
	private function register_handlers() {
		$handlers = [
			// Info (1).
			new HFE_Info_Get_Handler(),

			// Active (1).
			new HFE_Active_Get_Handler(),

			// Templates (7).
			new HFE_Template_List_Handler(),
			new HFE_Template_Get_Handler(),
			new HFE_Template_Create_Handler(),
			new HFE_Template_Update_Handler(),
			new HFE_Template_Delete_Handler(),
			new HFE_Template_Restore_Handler(),
			new HFE_Template_Duplicate_Handler(),

			// Pages (6).
			new HFE_Page_Create_Handler(),
			new HFE_Page_List_Handler(),
			new HFE_Page_Delete_Handler(),
			new HFE_Page_Restore_Handler(),
			new HFE_Page_Update_Status_Handler(),
			new HFE_Page_Update_Meta_Handler(),

			// Widgets (6).
			new HFE_Widget_List_Handler(),
			new HFE_Widget_Activate_Handler(),
			new HFE_Widget_Deactivate_Handler(),
			new HFE_Widget_Bulk_Toggle_Handler(),
			new HFE_Widget_Deactivate_Unused_Handler(),
			new HFE_Widget_Usage_Handler(),

			// Extensions (2).
			new HFE_Extension_List_Handler(),
			new HFE_Extension_Toggle_Handler(),

			// Display Rules (2).
			new HFE_Display_Rules_Update_Handler(),
			new HFE_Display_Rules_Locations_Handler(),

			// Builder (12).
			new HFE_Build_Handler(),
			new HFE_Insert_Widget_Handler(),
			new HFE_Update_Widget_Handler(),
			new HFE_Remove_Element_Handler(),
			new HFE_Move_Element_Handler(),
			new HFE_Structure_Handler(),
			new HFE_Add_Section_Handler(),
			new HFE_Add_Column_Handler(),
			new HFE_CSS_Handler(),
			new HFE_Widget_Types_Handler(),
			new HFE_Schema_Handler(),
			new HFE_Undo_Handler(),

			// Theme (2).
			new HFE_Theme_Info_Handler(),
			new HFE_Theme_Method_Handler(),

			// Settings (2).
			new HFE_Settings_Get_Handler(),
			new HFE_Settings_Update_Handler(),

			// Shortcode (1).
			new HFE_Shortcode_Render_Handler(),

			// Design System (1).
			new HFE_Design_Tokens_Handler(),

			// Maintenance (1).
			new HFE_Maintenance_Clear_Cache_Handler(),
		];

		// Pro (1) — upgrade/upsell info ability. Only register it when UAE Pro is
		// NOT active. When Pro is active it exposes its own richer pro-info
		// abilities, so this Lite ability would be redundant and confusing.
		if ( ! defined( 'UAEL_VER' ) ) {
			$handlers[] = new HFE_Pro_Features_Handler();
		}

		foreach ( $handlers as $handler ) {
			$this->registry->register( $handler, 'hfe' );
		}
	}

	/**
	 * Register a dedicated HFE MCP server with the MCP Adapter.
	 *
	 * @param object $mcp_adapter The MCP Adapter instance.
	 * @return void
	 */
	public function register_mcp_server( $mcp_adapter ) {
		// Pull tools from the WP-wide ability registry (post-registration) and
		// filter to HFE-prefixed abilities. Reading from wp_get_abilities() —
		// rather than our internal registry — is more resilient to ordering
		// and gating edge cases, matching the Astra pattern.
		$abilities = function_exists( 'wp_get_abilities' ) ? wp_get_abilities() : [];
		$tools     = [];

		foreach ( $abilities as $ability ) {
			if ( 0 === strpos( $ability->get_name(), HFE_Ability_Registry::PREFIX ) ) {
				$tools[] = $ability->get_name();
			}
		}

		if ( empty( $tools ) ) {
			return;
		}

		// Transport class fallback — older MCP Adapter builds expose
		// HttpTransport at a different namespace.
		$transport_class = class_exists( '\WP\MCP\Transport\HttpTransport' )
			? \WP\MCP\Transport\HttpTransport::class
			: '\WP\MCP\Transport\Http\RestTransport';

		// Explicit error + observability handlers — newer MCP Adapter releases
		// require concrete classes rather than null.
		$error_handler = class_exists( '\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler' )
			? \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class
			: null;

		$observability = class_exists( '\WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler' )
			? \WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class
			: null;

		$mcp_adapter->create_server(
			'uae-mcp-server',
			'uae',
			'mcp',
			__( 'UAE MCP Server', 'header-footer-elementor' ),
			__( 'AI tools for Header Footer Elementor — manage headers, footers, templates, pages, and widgets.', 'header-footer-elementor' ),
			'v' . ( defined( 'HFE_VER' ) ? HFE_VER : '0.0.0' ),
			[ $transport_class ],
			$error_handler,
			$observability,
			$tools,
			[],
			[]
		);
	}

	/**
	 * Initialize Angie integration.
	 *
	 * Creates the Angie bridge (REST routes + JS enqueue) after abilities
	 * are registered so all handlers are available.
	 *
	 * @return void
	 */
	public function init_angie() {
		// Loaded here (not in load_framework) so the Angie class is only required
		// when the Angie integration is actually enabled.
		require_once HFE_DIR . 'inc/angie/class-hfe-angie.php';
		new HFE_Angie( $this->registry );
	}
}

HFE_Abilities_Loader::instance();
