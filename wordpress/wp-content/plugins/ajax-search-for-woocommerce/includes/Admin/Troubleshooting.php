<?php

namespace DgoraWcas\Admin;

use DgoraWcas\Admin\Promo\FeedbackNotice;
use DgoraWcas\Admin\Promo\Upgrade;
use DgoraWcas\Engines\TNTSearchMySQL\Indexer\Logger;
use DgoraWcas\Helpers;
use DgoraWcas\Engines\TNTSearchMySQL\Indexer\Builder;
use DgoraWcas\Multilingual;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Troubleshooting {
    const SECTION_ID = 'dgwt_wcas_troubleshooting';

    const TRANSIENT_RESULTS_KEY = 'dgwt_wcas_troubleshooting_async_results';

    const ASYNC_TEST_NONCE = 'troubleshooting-async-test';

    const FIX_OUTOFSTOCK_NONCE = 'troubleshooting-fix-outofstock';

    const ASYNC_ACTION_NONCE = 'troubleshooting-async-action';

    const MAINTENANCE_ANALYTICS_NONCE = 'troubleshooting-maintenance-analytics';

    const SWITCH_ALTERNATIVE_ENDPOINT = 'troubleshooting-switch-alternative-endpoint';

    // Regenerate images.
    const IMAGES_ALREADY_REGENERATED_OPT_KEY = 'dgwt_wcas_images_regenerated';

    public function __construct() {
        if ( !$this->checkRequirements() ) {
            return;
        }
        add_filter( 'dgwt/wcas/settings', [$this, 'addSettingsTab'] );
        add_filter( 'dgwt/wcas/settings/sections', [$this, 'addSettingsSection'] );
        add_filter( 'dgwt/wcas/scripts/admin/localize', [$this, 'localizeSettings'] );
        add_filter( 'removable_query_args', [$this, 'addRemovableQueryArgs'] );
        add_action( DGWT_WCAS_SETTINGS_KEY . '-form_bottom_' . self::SECTION_ID, [$this, 'tabContent'] );
        add_action( 'wp_ajax_dgwt_wcas_troubleshooting_test', [$this, 'asyncTest'] );
        add_action( 'wp_ajax_dgwt_wcas_troubleshooting_async_action', [$this, 'asyncActionHandler'] );
        add_action( 'admin_notices', [$this, 'showNotices'] );
    }

    /**
     * Add "Troubleshooting" tab on Settings page
     *
     * @param array $settings
     *
     * @return array
     */
    public function addSettingsTab( $settings ) {
        $settings[self::SECTION_ID] = apply_filters( 'dgwt/wcas/settings/section=troubleshooting', [
            10 => [
                'name'  => 'troubleshooting_head',
                'label' => __( 'Troubleshooting', 'ajax-search-for-woocommerce' ),
                'type'  => 'head',
                'class' => 'dgwt-wcas-sgs-header',
            ],
        ] );
        return $settings;
    }

    /**
     * Content of "Troubleshooting" tab on Settings page
     *
     * @param array $sections
     *
     * @return array
     */
    public function addSettingsSection( $sections ) {
        $sections[150] = [
            'id'    => self::SECTION_ID,
            'title' => __( 'Troubleshooting', 'ajax-search-for-woocommerce' ) . '<span class="js-dgwt-wcas-troubleshooting-count dgwt-wcas-tab-mark"></span>',
        ];
        return $sections;
    }

    /**
     * Add custom query variable names to remove
     *
     * @param array $args
     *
     * @return array
     */
    public function addRemovableQueryArgs( $args ) {
        $args[] = 'dgwt-wcas-regenerate-images-started';
        return $args;
    }

    /**
     * Show troubleshooting notices
     *
     * @return void
     */
    public function showNotices() {
        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_REQUEST['dgwt-wcas-regenerate-images-started'] ) ) {
            ?>
			<div class="notice notice-success dgwt-wcas-notice">
				<p><?php 
            _e( 'Regeneration of images started. The process will continue in the background.', 'ajax-search-for-woocommerce' );
            ?></p>
			</div>
			<?php 
        }
    }

    /**
     * AJAX callback for running async test
     */
    public function asyncTest() {
        if ( !current_user_can( ( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) ) {
            wp_die( -1, 403 );
        }
        check_ajax_referer( self::ASYNC_TEST_NONCE );
        $test = ( isset( $_POST['test'] ) ? wc_clean( wp_unslash( $_POST['test'] ) ) : '' );
        if ( !$this->isTestExists( $test ) ) {
            wp_send_json_error();
        }
        $testFunction = sprintf( 'getTest%s', $test );
        if ( method_exists( $this, $testFunction ) && is_callable( [$this, $testFunction] ) ) {
            $data = $this->performTest( [$this, $testFunction] );
            wp_send_json_success( $data );
        }
        wp_send_json_error();
    }

    /**
     * Async action handler
     */
    public function asyncActionHandler() {
        if ( !current_user_can( ( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) ) {
            wp_die( -1, 403 );
        }
        check_ajax_referer( self::ASYNC_ACTION_NONCE );
        $internalAction = $_POST['internal_action'] ?? '';
        $data = [];
        $success = false;
        switch ( $internalAction ) {
            case 'dismiss_elementor_template':
                update_option( 'dgwt_wcas_dismiss_elementor_template', '1' );
                $success = true;
                break;
            case 'reset_async_tests':
                // Reset stored results of async tests.
                delete_transient( self::TRANSIENT_RESULTS_KEY );
                $success = true;
                break;
            case 'dismiss_regenerate_images':
                update_option( self::IMAGES_ALREADY_REGENERATED_OPT_KEY, '1' );
                $success = true;
                break;
            case 'regenerate_images':
                $this->regenerateImages();
                $data['args'] = [
                    'dgwt-wcas-regenerate-images-started' => true,
                ];
                $success = true;
                break;
        }
        ( $success ? wp_send_json_success( $data ) : wp_send_json_error( $data ) );
    }

    /**
     * Pass "troubleshooting" data to JavaScript on Settings page
     *
     * @param array $localize
     *
     * @return array
     */
    public function localizeSettings( $localize ) {
        $localize['troubleshooting'] = [
            'nonce' => [
                'troubleshooting_async_test'                  => wp_create_nonce( self::ASYNC_TEST_NONCE ),
                'troubleshooting_fix_outofstock'              => wp_create_nonce( self::FIX_OUTOFSTOCK_NONCE ),
                'troubleshooting_async_action'                => wp_create_nonce( self::ASYNC_ACTION_NONCE ),
                'troubleshooting_switch_alternative_endpoint' => wp_create_nonce( self::SWITCH_ALTERNATIVE_ENDPOINT ),
                'troubleshooting_maintenance_analytics'       => wp_create_nonce( self::MAINTENANCE_ANALYTICS_NONCE ),
            ],
            'tests' => [
                'direct'        => [],
                'async'         => [],
                'issues'        => [
                    'good'        => 0,
                    'recommended' => 0,
                    'critical'    => 0,
                ],
                'results_async' => [],
            ],
        ];
        $asyncTestsResults = get_transient( self::TRANSIENT_RESULTS_KEY );
        if ( !empty( $asyncTestsResults ) && is_array( $asyncTestsResults ) ) {
            $localize['troubleshooting']['tests']['results_async'] = array_values( $asyncTestsResults );
            foreach ( $asyncTestsResults as $result ) {
                $localize['troubleshooting']['tests']['issues'][$result['status']]++;
            }
        }
        $tests = self::getTests();
        if ( !empty( $tests['direct'] ) && is_array( $tests['direct'] ) ) {
            foreach ( $tests['direct'] as $test ) {
                if ( is_string( $test['test'] ) ) {
                    $testFunction = sprintf( 'getTest%s', $test['test'] );
                    if ( method_exists( $this, $testFunction ) && is_callable( [$this, $testFunction] ) ) {
                        $localize['troubleshooting']['tests']['direct'][] = $this->performTest( [$this, $testFunction] );
                        continue;
                    }
                }
                if ( is_callable( $test['test'] ) ) {
                    $localize['troubleshooting']['tests']['direct'][] = $this->performTest( $test['test'] );
                }
            }
        }
        if ( !empty( $localize['troubleshooting']['tests']['direct'] ) && is_array( $localize['troubleshooting']['tests']['direct'] ) ) {
            foreach ( $localize['troubleshooting']['tests']['direct'] as $result ) {
                $localize['troubleshooting']['tests']['issues'][$result['status']]++;
            }
        }
        if ( !empty( $tests['async'] ) && is_array( $tests['async'] ) ) {
            foreach ( $tests['async'] as $test ) {
                if ( is_string( $test['test'] ) ) {
                    $localize['troubleshooting']['tests']['async'][] = [
                        'test'      => $test['test'],
                        'completed' => isset( $asyncTestsResults[$test['test']] ),
                    ];
                }
            }
        }
        return $localize;
    }

    /**
     * Load content for "Troubleshooting" tab on Settings page
     */
    public function tabContent() {
        require DGWT_WCAS_DIR . 'partials/admin/troubleshooting.php';
    }

    /**
     * Test for incompatible plugins
     *
     * @return array The test result.
     */
    public function getTestIncompatiblePlugins() {
        $result = [
            'label'       => __( 'You are using one or more incompatible plugins', 'ajax-search-for-woocommerce' ),
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'IncompatiblePlugins',
        ];
        $errors = [];
        // GTranslate
        if ( class_exists( 'GTranslate' ) ) {
            $errors[] = sprintf( __( 'You are using the %1$s plugin. The %2$s does not support this plugin.', 'ajax-search-for-woocommerce' ), 'GTranslate', DGWT_WCAS_NAME );
        }
        // WooCommerce Product Sort and Display
        if ( defined( 'WC_PSAD_VERSION' ) ) {
            $errors[] = sprintf( __( 'You are using the %1$s plugin. The %2$s does not support this plugin.', 'ajax-search-for-woocommerce' ), 'WooCommerce Product Sort and Display', DGWT_WCAS_NAME );
        }
        // Ajax Search Lite (by WPDreams) - no search results.
        if ( defined( 'ASL_CURR_VER_STRING' ) ) {
            $errors[] = sprintf( __( 'You use the %s plugin, which may cause errors in the search results returned by our plugin.', 'ajax-search-for-woocommerce' ), '<strong>Ajax Search Lite (by WPDreams)</strong>' );
        }
        // Ajax Search Pro (by WPDreams) - no search results.
        if ( defined( 'ASP_CURR_VER_STRING' ) ) {
            $errors[] = sprintf( __( 'You use the %s plugin, which may cause errors in the search results returned by our plugin.', 'ajax-search-for-woocommerce' ), '<strong>Ajax Search Pro (by WPDreams)</strong>' );
        }
        if ( !empty( $errors ) ) {
            $result['description'] = join( '<br>', $errors );
            $result['status'] = 'critical';
        }
        return $result;
    }

    /**
     * Test for incompatible plugins
     *
     * @return array The test result.
     */
    public function getTestTranslatePress() {
        $result = [
            'label'       => __( 'You are using TranslatePress with Free version of our plugin', 'ajax-search-for-woocommerce' ),
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'TranslatePress',
        ];
        if ( !defined( 'TRP_PLUGIN_VERSION' ) && !class_exists( 'TRP_Translate_Press' ) ) {
            return $result;
        }
        $result['description'] = sprintf( __( 'Due to the way the TranslatePress - Multilingual plugin works, we can only provide support for it in the <a href="%s" target="_blank">Pro version</a>.', 'ajax-search-for-woocommerce' ), Upgrade::getUpgradeUrl() );
        $result['status'] = 'critical';
        return $result;
    }

    /**
     * Test if loopbacks work as expected
     *
     * @return array The test result.
     */
    public function getTestLoopbackRequests() {
        $result = [
            'label'       => __( 'Your site can perform loopback requests', 'ajax-search-for-woocommerce' ),
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'LoopbackRequests',
        ];
        $cookies = [];
        $timeout = 10;
        $headers = [
            'Cache-Control' => 'no-cache',
        ];
        /** This filter is documented in wp-includes/class-wp-http-streams.php */
        $sslverify = apply_filters( 'https_local_ssl_verify', false );
        $authorization = Helpers::getBasicAuthHeader();
        if ( $authorization ) {
            $headers['Authorization'] = $authorization;
        }
        $url = home_url();
        $r = wp_remote_get( $url, compact(
            'cookies',
            'headers',
            'timeout',
            'sslverify'
        ) );
        $markAsCritical = is_wp_error( $r ) || wp_remote_retrieve_response_code( $r ) !== 200;
        // Exclude timeout error
        if ( is_wp_error( $r ) && $r->get_error_code() === 'http_request_failed' && strpos( strtolower( $r->get_error_message() ), 'curl error 28:' ) !== false ) {
            $markAsCritical = false;
        }
        if ( $markAsCritical ) {
            $result['status'] = 'critical';
            $linkToDocs = 'https://fibosearch.com/documentation/troubleshooting/the-search-index-could-not-be-built/';
            $linkToWpHealth = admin_url( 'site-health.php' );
            $result['label'] = __( 'Your site could not complete a loopback request', 'ajax-search-for-woocommerce' );
            $result['description'] .= '<h3 class="dgwt-wcas-font-thin">' . __( 'Solutions:', 'ajax-search-for-woocommerce' ) . '</h3>';
            $result['description'] .= '<h4>' . __( "Your server can't send an HTTP request to itself", 'ajax-search-for-woocommerce' ) . '</h4>';
            $result['description'] .= '<p>' . sprintf( __( 'Go to <a href="%s" target="_blank">Tools -> Site Health</a> in your WordPress. You should see issues related to REST API or Loopback request. Expand descriptions of these errors and follow the instructions. Probably you will need to contact your hosting provider to solve it.', 'ajax-search-for-woocommerce' ), $linkToWpHealth ) . '</p>';
            $result['description'] .= '<p>' . __( 'Is your website publicly available only for whitelisted IPs? <b>Add your server IP to the whitelist</b>. That’s all. This is a common mistake when access is blocked by a <code>.htaccess</code> file. Developers add a list of allowed IPs, but they forget to add the IP of the server to allow it to make HTTP requests to itself.', 'ajax-search-for-woocommerce' ) . '</p>';
        }
        $this->storeResult( $result );
        return $result;
    }

    /**
     * Test for required PHP extensions
     *
     * @return array The test result.
     */
    public function getTestPHPExtensions() {
        $result = [
            'label'       => __( 'One or more required PHP extensions are missing on your server', 'ajax-search-for-woocommerce' ),
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'PHPExtensions',
        ];
        $errors = [];
        if ( !extension_loaded( 'mbstring' ) ) {
            $errors[] = sprintf( __( 'Required PHP extension: %s', 'ajax-search-for-woocommerce' ), 'mbstring' );
        }
        if ( !empty( $errors ) ) {
            $result['description'] = join( '<br>', $errors );
            $result['status'] = 'critical';
        }
        return $result;
    }

    /**
     * Tests for WordPress version and outputs it.
     *
     * @return array The test result.
     */
    public function getTestWordPressVersion() {
        $result = [
            'label'       => __( 'WordPress version', 'ajax-search-for-woocommerce' ),
            'status'      => '',
            'description' => '',
            'actions'     => '',
            'test'        => 'WordPressVersion',
        ];
        $coreCurrentVersion = get_bloginfo( 'version' );
        if ( version_compare( $coreCurrentVersion, '5.2.0' ) >= 0 ) {
            $result['description'] = __( 'Great! Our plugin works great with this version of WordPress.', 'ajax-search-for-woocommerce' );
            $result['status'] = 'good';
        } else {
            $result['description'] = __( 'Install the latest version of WordPress for our plugin for optimal performance!', 'ajax-search-for-woocommerce' );
            $result['status'] = 'critical';
        }
        return $result;
    }

    /**
     * Tests for required "Add to cart" behaviour in WooCommerce settings
     * If the search Details Panel is enabled, WooCommerce "Add to cart" behaviour should be enabled.
     *
     * @return array The test result.
     */
    public function getTestAjaxAddToCart() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'AjaxAddToCart',
        ];
        if ( 'on' === DGWT_WCAS()->settings->getOption( 'show_details_box' ) && ('yes' !== get_option( 'woocommerce_enable_ajax_add_to_cart' ) || 'yes' === get_option( 'woocommerce_cart_redirect_after_add' )) ) {
            $redirectLabel = __( 'Redirect to the cart page after successful addition', 'woocommerce' );
            $ajaxAtcLabel = __( 'Enable AJAX add to cart buttons on archives', 'woocommerce' );
            $settingsUrl = admin_url( 'admin.php?page=wc-settings&tab=products' );
            $result['label'] = __( 'Incorrect "Add to cart" behaviour in WooCommerce settings', 'ajax-search-for-woocommerce' );
            $result['description'] = '<p><b>' . __( 'Solution', 'ajax-search-for-woocommerce' ) . '</b></p>';
            $result['description'] .= '<p>' . sprintf(
                __( 'Go to <code>WooCommerce -> Settings -> <a href="%1$s" target="_blank">Products (tab)</a></code> and check option <code>%2$s</code> and uncheck option <code>%3$s</code>.', 'ajax-search-for-woocommerce' ),
                $settingsUrl,
                $ajaxAtcLabel,
                $redirectLabel
            ) . '</p>';
            $result['description'] .= __( 'Your settings should look like the picture below:', 'ajax-search-for-woocommerce' );
            $result['description'] .= '<p><img style="max-width: 720px" src="' . DGWT_WCAS_URL . 'assets/img/admin-troubleshooting-atc.png" /></p>';
            $result['status'] = 'critical';
        }
        return $result;
    }

    /**
     * Tests if "Searching by Text (old version)" extension from WOOF - WooCommerce Products Filter is enabled.
     * It's incompatible with our plugin and should be disabled.
     *
     * @return array The test result.
     */
    public function getTestWoofSearchText2Extension() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'WoofSearchText2Extension',
        ];
        if ( !defined( 'WOOF_VERSION' ) || !isset( $GLOBALS['WOOF'] ) ) {
            return $result;
        }
        if ( !method_exists( 'WOOF_EXT', 'is_ext_activated' ) ) {
            return $result;
        }
        $extDirs = $GLOBALS['WOOF']->get_ext_directories();
        if ( empty( $extDirs['default'] ) ) {
            return $result;
        }
        $extPaths = array_filter( $extDirs['default'], function ( $path ) {
            return Helpers::endsWith( $path, 'ext/by_text_2' );
        } );
        if ( empty( $extPaths ) ) {
            return $result;
        }
        $extPath = array_shift( $extPaths );
        if ( \WOOF_EXT::is_ext_activated( $extPath ) ) {
            $settingsUrl = admin_url( 'admin.php?page=wc-settings&tab=woof' );
            $result['label'] = __( 'Incompatible "Searching by Text" extension from "WOOF - WooCommerce Products Filter plugin" is active', 'ajax-search-for-woocommerce' );
            $result['description'] = '<p><b>' . __( 'Solution', 'ajax-search-for-woocommerce' ) . '</b></p>';
            $result['description'] .= '<p>' . sprintf( __( 'Go to <code>WooCommerce -> Settings -> <a href="%s" target="_blank">Products Filter (tab)</a> -> Extensions (tab)</code>, uncheck <code>Searching by Text</code> extension and save changes.', 'ajax-search-for-woocommerce' ), $settingsUrl ) . '</p>';
            $result['description'] .= __( 'Extensions should looks like the picture below:', 'ajax-search-for-woocommerce' );
            $result['description'] .= '<p><img style="max-width: 720px" src="' . DGWT_WCAS_URL . 'assets/img/admin-troubleshooting-woof.png?rev=2" /></p>';
            $result['status'] = 'critical';
        }
        return $result;
    }

    /**
     * Tests if "HUSKY - Advanced searching by Text" extension from WOOF - WooCommerce Products Filter is enabled.
     * It's incompatible with our plugin and should be disabled.
     *
     * @return array The test result.
     */
    public function getTestWoofSearchTextExtension() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'WoofSearchTextExtension',
        ];
        if ( !defined( 'WOOF_VERSION' ) || !isset( $GLOBALS['WOOF'] ) ) {
            return $result;
        }
        if ( !method_exists( 'WOOF_EXT', 'is_ext_activated' ) ) {
            return $result;
        }
        $extDirs = $GLOBALS['WOOF']->get_ext_directories();
        if ( empty( $extDirs['default'] ) ) {
            return $result;
        }
        $extPaths = array_filter( $extDirs['default'], function ( $path ) {
            return Helpers::endsWith( $path, 'ext/by_text' );
        } );
        if ( empty( $extPaths ) ) {
            return $result;
        }
        $extPath = array_shift( $extPaths );
        if ( \WOOF_EXT::is_ext_activated( $extPath ) ) {
            $settingsUrl = admin_url( 'admin.php?page=wc-settings&tab=woof' );
            if ( strpos( WOOF_VERSION, '1' ) === 0 && version_compare( WOOF_VERSION, '1.3.2' ) >= 0 || strpos( WOOF_VERSION, '3' ) === 0 && version_compare( WOOF_VERSION, '3.3.2' ) >= 0 ) {
                $result['label'] = __( 'Incompatible "HUSKY - Advanced searching by Text" extension from "HUSKY - Products Filter Professional for WooCommerce" is active', 'ajax-search-for-woocommerce' );
                $result['description'] = '<p><b>' . __( 'Solution', 'ajax-search-for-woocommerce' ) . '</b></p>';
                $result['description'] .= '<p>' . sprintf( __( 'Go to <code>WooCommerce -> Settings -> <a href="%s" target="_blank">Products Filter (tab)</a> -> Advanced (tab) -> Options (tab)</code> -> scroll to the bottom to <code>Forcing disabling of functionality</code> option -> enter the value <code>by_text</code> and save changes.', 'ajax-search-for-woocommerce' ), $settingsUrl ) . '</p>';
                $result['status'] = 'critical';
            } else {
                $result['label'] = __( 'Incompatible "HUSKY - Advanced searching by Text" extension from "WOOF - WooCommerce Products Filter plugin" is active', 'ajax-search-for-woocommerce' );
                $result['description'] = '<p><b>' . __( 'Solution', 'ajax-search-for-woocommerce' ) . '</b></p>';
                $result['description'] .= '<p>' . sprintf( __( 'Go to <code>WooCommerce -> Settings -> <a href="%s" target="_blank">Products Filter (tab)</a> -> Extensions (tab)</code>, uncheck <code>HUSKY - Advanced searching by Text</code> extension and save changes.', 'ajax-search-for-woocommerce' ), $settingsUrl ) . '</p>';
                $result['description'] .= __( 'Extensions should looks like the picture below:', 'ajax-search-for-woocommerce' );
                $result['description'] .= '<p><img style="max-width: 720px" src="' . DGWT_WCAS_URL . 'assets/img/admin-troubleshooting-woof2.png" /></p>';
                $result['status'] = 'critical';
            }
        }
        return $result;
    }

    /**
     * Tests if "Try to ajaxify the shop" option from WOOF - WooCommerce Products Filter is enabled.
     * It's incompatible with our plugin and should be disabled.
     *
     * @return array The test result.
     */
    public function getTestWoofTryToAjaxifyOption() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'WoofTryToAjaxifyOption',
        ];
        if ( !defined( 'WOOF_VERSION' ) ) {
            return $result;
        }
        if ( version_compare( WOOF_VERSION, '1.2.3' ) < 0 ) {
            return $result;
        }
        if ( !get_option( 'woof_try_ajax', 0 ) ) {
            return $result;
        }
        $settingsUrl = admin_url( 'admin.php?page=wc-settings&tab=woof' );
        $result['label'] = __( 'Incompatible "Try to ajaxify the shop" option from WOOF - WooCommerce Products Filter plugin is enabled', 'ajax-search-for-woocommerce' );
        $result['description'] = '<p><b>' . __( 'Solution', 'ajax-search-for-woocommerce' ) . '</b></p>';
        $result['description'] .= '<p>' . sprintf( __( 'Go to <code>WooCommerce -> Settings -> <a href="%s" target="_blank">Products Filter (tab)</a> -> Options (tab)</code>, set <code>Try to ajaxify the shop</code> option to <code>No</code> and save changes.', 'ajax-search-for-woocommerce' ), $settingsUrl ) . '</p>';
        $result['status'] = 'critical';
        return $result;
    }

    /**
     * Test if Elementor has defined correct template for search results
     *
     * @return array The test result.
     */
    public function getTestElementorSearchResultsTemplate() {
        global $wp_query;
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'ElementorSearchTemplate',
        ];
        if ( get_option( 'dgwt_wcas_dismiss_elementor_template' ) === '1' ) {
            return $result;
        }
        if ( !defined( 'ELEMENTOR_VERSION' ) || !defined( 'ELEMENTOR_PRO_VERSION' ) ) {
            return $result;
        }
        if ( version_compare( ELEMENTOR_VERSION, '2.9.0' ) < 0 || version_compare( ELEMENTOR_PRO_VERSION, '2.10.0' ) < 0 ) {
            return $result;
        }
        $conditionsManager = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' )->get_conditions_manager();
        // Prepare $wp_query so that the conditions for checking if there is a search page are true.
        $wp_query->is_search = true;
        $wp_query->is_post_type_archive = true;
        set_query_var( 'post_type', 'product' );
        $documents = $conditionsManager->get_documents_for_location( 'archive' );
        // Reset $wp_query
        $wp_query->is_search = false;
        $wp_query->is_post_type_archive = false;
        set_query_var( 'post_type', '' );
        // Stop checking - a template from a theme or WooCommerce will be used
        if ( empty( $documents ) ) {
            return $result;
        }
        /**
         * @var \ElementorPro\Modules\ThemeBuilder\Documents\Theme_Document $document
         */
        $document = current( $documents );
        if ( $this->doesElementorElementsContainsWidget( $document->get_elements_data(), 'wc-archive-products' ) || $this->doesElementorElementsContainsWidget( $document->get_elements_data(), 'jet-listing-grid' ) ) {
            return $result;
        }
        $linkToDocs = 'https://fibosearch.com/documentation/troubleshooting/the-search-results-page-created-in-elementor-doesnt-display-products/';
        $dismissButton = get_submit_button(
            __( 'Dismiss', 'ajax-search-for-woocommerce' ),
            'secondary',
            'dgwt-wcas-async-action-dismiss-elementor-template',
            false,
            [
                'data-internal-action' => 'dismiss_elementor_template',
            ]
        );
        $templateLink = '<a target="_blank" href="' . admin_url( 'post.php?post=' . $document->get_post()->ID . '&action=elementor' ) . '">' . $document->get_post()->post_title . '</a>';
        $result['label'] = __( 'There is no correct template in the Elementor Theme Builder for the WooCommerce search results page.', 'ajax-search-for-woocommerce' );
        $result['description'] = '<p>' . sprintf( __( 'You are using Elementor and we noticed that the template used in the search results page titled <strong>%s</strong> does not include the <strong>Archive Products</strong> widget.', 'ajax-search-for-woocommerce' ), $templateLink ) . '</p>';
        $result['description'] .= '<p><b>' . __( 'Solution', 'ajax-search-for-woocommerce' ) . '</b></p>';
        $result['description'] .= '<p>' . sprintf( __( 'Add <strong>Archive Products</strong> widget to the template <strong>%1$s</strong> or create a new template dedicated to the WooCommerce search results page. Learn how to do it in <a href="%2$s" target="_blank">our documentation</a>.', 'ajax-search-for-woocommerce' ), $templateLink, $linkToDocs ) . '</p>';
        $result['description'] .= '<br/><hr/><br/>';
        $result['description'] .= '<p>' . sprintf( __( 'If you think the search results page is displaying your products correctly, you can ignore and dismiss this message: %s', 'ajax-search-for-woocommerce' ), $dismissButton ) . '<span class="dgwt-wcas-ajax-loader"></span></p>';
        $result['status'] = 'critical';
        return $result;
    }

    /**
     * Test to verify whether the theme we integrate with meet the minimum supported versions
     *
     * @return array The test result.
     */
    public function getTestMinThemeVersion() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'MinThemeVersion',
        ];
        $unsupportedTheme = apply_filters( 'dgwt/wcas/troubleshooting/unsupported_theme_version', [] );
        if ( !empty( $unsupportedTheme ) ) {
            $result['status'] = 'critical';
            $result['label'] = __( 'Unsupported theme version', 'ajax-search-for-woocommerce' );
            $result['description'] .= '<p>' . sprintf(
                __( 'We’ve detected that you’re using the %1$s theme, which we support, but your current version (%2$s) is not compatible with our integration. To activate and use our integration properly, please update the theme to at least version %3$s.', 'ajax-search-for-woocommerce' ),
                '<strong>' . $unsupportedTheme['name'] . '</strong>',
                '<strong>v' . $unsupportedTheme['currentVersion'] . '</strong>',
                '<strong>v' . $unsupportedTheme['minimumVersion'] . '</strong>'
            ) . '</p>';
        }
        return $result;
    }

    /**
     * Test to verify whether plugins we integrate with meet the minimum supported versions
     *
     * @return array The test result.
     */
    public function getTestMinPluginVersions() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'MinPluginVersions',
        ];
        $unsupportedPlugins = apply_filters( 'dgwt/wcas/troubleshooting/unsupported_plugin_versions', [] );
        if ( !empty( $unsupportedPlugins ) ) {
            $result['status'] = 'critical';
            $result['label'] = __( 'Unsupported plugin versions', 'ajax-search-for-woocommerce' );
            $result['description'] .= '<p>' . __( 'We have detected that you are using plugins that we integrate with, but in versions that we do not support:', 'ajax-search-for-woocommerce' ) . '</p>';
            $result['description'] .= '<ol>';
            foreach ( $unsupportedPlugins as $unsupportedPlugin ) {
                $result['description'] .= '<li>' . sprintf(
                    __( '%1$s, current version: %2$s, minimum version: %3$s', 'ajax-search-for-woocommerce' ),
                    '<strong>' . $unsupportedPlugin['name'] . '</strong>',
                    '<strong>v' . $unsupportedPlugin['currentVersion'] . '</strong>',
                    '<strong>v' . $unsupportedPlugin['minimumVersion'] . '</strong>'
                ) . '</li>';
            }
            $result['description'] .= '</ol>';
            $result['description'] .= '<p>' . __( 'The integrations will not be activated unless the plugins are updated to the minimum required versions.', 'ajax-search-for-woocommerce' ) . '</p>';
        }
        return $result;
    }

    /**
     * Test if Product Filter by WBW has Remove Actions Before Filtering option enabled.
     * If so, it can cause filtering to return 0 results.
     *
     * Enable this option when ajax filtering does not work as expected. For example, sorting does not work. Removes filters such as posts_orderby and pre_get_posts.
     *
     * @return array The test result.
     */
    public function getTestProductFilterByWBW() {
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'ProductFilterByWBW',
        ];
        if ( !defined( 'WPF_VERSION' ) ) {
            return $result;
        }
        if ( !class_exists( '\\FrameWpf' ) ) {
            return $result;
        }
        $filters = \FrameWpf::_()->getModule( 'woofilters' )->getModel()->getFromTbl();
        if ( !is_array( $filters ) ) {
            return $result;
        }
        $removeActionBeforeFilteringOptionEnabled = false;
        foreach ( $filters as $filter ) {
            if ( !isset( $filter['setting_data'] ) ) {
                continue;
            }
            $raw = ( is_array( $filter['setting_data'] ) ? $filter['setting_data'] : unserialize( $filter['setting_data'] ) );
            if ( !is_array( $raw ) ) {
                continue;
            }
            $settings = ( isset( $raw['settings'] ) && is_array( $raw['settings'] ) ? $raw['settings'] : [] );
            // Break if AJAX is disabled for this filter settings.
            if ( isset( $settings['enable_ajax'] ) && $settings['enable_ajax'] === '0' ) {
                continue;
            }
            if ( !empty( $settings['remove_actions'] ) && (string) $settings['remove_actions'] === '1' ) {
                $removeActionBeforeFilteringOptionEnabled = true;
                break;
            }
        }
        if ( !$removeActionBeforeFilteringOptionEnabled ) {
            return $result;
        }
        $result['status'] = 'critical';
        $option_label = __( 'Remove Actions Before Filtering', 'ajax-search-for-woocommerce' );
        $result['label'] = sprintf( __( 'WBW Product Filter: Option %s is enabled', 'ajax-search-for-woocommerce' ), $option_label );
        $result['description'] = '<p>' . sprintf( __( 'We detected that Product Filter by WBW has the %s option enabled. With filtering active, this can cause filtering to return 0 results.', 'ajax-search-for-woocommerce' ), '<code>' . esc_html( $option_label ) . '</code>' ) . '</p>';
        $result['description'] .= '<p><strong>' . __( 'To make everything work properly, turn this option off.', 'ajax-search-for-woocommerce' ) . '</strong></p>';
        $result['description'] .= '<ol>';
        $result['description'] .= '<li>' . __( 'Open the settings of the relevant filter in Product Filter by WBW.', 'ajax-search-for-woocommerce' ) . '</li>';
        $result['description'] .= '<li>' . sprintf( __( 'Turn off the %s option.', 'ajax-search-for-woocommerce' ), '<code>' . esc_html( $option_label ) . '</code>' ) . '</li>';
        $result['description'] .= '<li>' . __( 'Save the settings and test filtering again.', 'ajax-search-for-woocommerce' ) . '</li>';
        $result['description'] .= '</ol>';
        return $result;
    }

    /**
     * Test if images need to be regenerated
     *
     * @return array The test result.
     */
    public function getTestNotRegeneratedImages() {
        global $wpdb;
        $displayImages = DGWT_WCAS()->settings->getOption( 'show_product_image' ) === 'on' || DGWT_WCAS()->settings->getOption( 'show_product_tax_product_cat_images' ) === 'on';
        $regenerated = get_option( self::IMAGES_ALREADY_REGENERATED_OPT_KEY );
        $activationDate = get_option( FeedbackNotice::ACTIVATION_DATE_OPT );
        $isTimeToDisplay = !empty( $activationDate ) && strtotime( '-2 days' ) >= $activationDate;
        $placeholderImage = get_option( 'woocommerce_placeholder_image', 0 );
        //phpcs:disable WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        $totalImages = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*)\n\t\t\tFROM {$wpdb->posts}\n\t\t\tWHERE post_type = 'attachment'\n\t\t\tAND post_mime_type LIKE 'image/%'\n\t\t\tAND ID != %d", $placeholderImage ) );
        $imagesBeforeActivation = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*)\n\t\t\tFROM {$wpdb->posts}\n\t\t\tWHERE post_type = 'attachment'\n\t\t\tAND post_mime_type LIKE 'image/%'\n\t\t\tAND ID != %d\n\t\t\tAND post_date < %s\n\t\t\t", $placeholderImage, wp_date( 'Y-m-d H:i:s', $activationDate ) ) );
        //phpcs:enable
        $percentageOfOldImages = 0;
        if ( $totalImages > 0 ) {
            $percentageOfOldImages = (float) ($imagesBeforeActivation * 100) / $totalImages;
        }
        $result = [
            'label'       => '',
            'status'      => 'good',
            'description' => '',
            'actions'     => '',
            'test'        => 'NotRegeneratedImages',
        ];
        if ( empty( $regenerated ) && $displayImages && $isTimeToDisplay && $percentageOfOldImages > 15 ) {
            $dismissButton = get_submit_button(
                __( 'Dismiss', 'ajax-search-for-woocommerce' ),
                'secondary',
                'dgwt-wcas-async-action-dismiss-regenerate-images',
                false,
                [
                    'data-internal-action' => 'dismiss_regenerate_images',
                ]
            );
            $regenerateImagesButton = get_submit_button(
                __( 'Regenerate WooCommerce images', 'ajax-search-for-woocommerce' ),
                'secondary',
                'dgwt-wcas-async-action-regenerate-images',
                false,
                [
                    'data-internal-action' => 'regenerate_images',
                ]
            );
            $pluginLink = '<a target="_blank" href="https://wordpress.org/plugins/regenerate-thumbnails/">Regenerate Thumbnails</a>';
            $result['label'] = __( 'Regenerate images', 'ajax-search-for-woocommerce' );
            $result['description'] = '<p>' . __( 'It is recommended to generate a special small image size for existing products to ensure a better user experience. This is a one-time action.', 'ajax-search-for-woocommerce' ) . '</p>';
            $result['description'] .= '<p>' . sprintf( __( 'You can do it by clicking %1$s or use an external plugin such as %2$s.', 'ajax-search-for-woocommerce' ), $regenerateImagesButton, $pluginLink ) . '</p>';
            $result['description'] .= '<hr/>';
            $result['description'] .= '<p>' . sprintf( __( 'If you have regenerated the images or do not think it is necessary, you can ignore and dismiss this message: %s', 'ajax-search-for-woocommerce' ), $dismissButton ) . '<span class="dgwt-wcas-ajax-loader"></span></p>';
            $result['status'] = 'critical';
            return $result;
        }
        return $result;
    }

    /**
     * Return a set of tests
     *
     * @return array The list of tests to run.
     */
    public static function getTests() {
        $tests = [
            'direct' => [
                [
                    'label' => __( 'WordPress version', 'ajax-search-for-woocommerce' ),
                    'test'  => 'WordPressVersion',
                ],
                [
                    'label' => __( 'PHP extensions', 'ajax-search-for-woocommerce' ),
                    'test'  => 'PHPExtensions',
                ],
                [
                    'label' => __( 'Incompatible plugins', 'ajax-search-for-woocommerce' ),
                    'test'  => 'IncompatiblePlugins',
                ],
                [
                    'label' => __( 'Incorrect "Add to cart" behaviour in WooCommerce settings', 'ajax-search-for-woocommerce' ),
                    'test'  => 'AjaxAddToCart',
                ],
                [
                    'label' => __( 'Incompatible "Searching by Text" extension in WOOF - WooCommerce Products Filter', 'ajax-search-for-woocommerce' ),
                    'test'  => 'WoofSearchText2Extension',
                ],
                [
                    'label' => __( 'Incompatible "HUSKY - Advanced searching by Text" extension in WOOF - WooCommerce Products Filter', 'ajax-search-for-woocommerce' ),
                    'test'  => 'WoofSearchTextExtension',
                ],
                [
                    'label' => __( 'Incompatible "Try to ajaxify the shop" option in WOOF - WooCommerce Products Filter', 'ajax-search-for-woocommerce' ),
                    'test'  => 'WoofTryToAjaxifyOption',
                ],
                [
                    'label' => __( 'Elementor search results template', 'ajax-search-for-woocommerce' ),
                    'test'  => 'ElementorSearchResultsTemplate',
                ],
                [
                    'label' => __( 'Minimum theme version', 'ajax-search-for-woocommerce' ),
                    'test'  => 'MinThemeVersion',
                ],
                [
                    'label' => __( 'Minimum theme versions', 'ajax-search-for-woocommerce' ),
                    'test'  => 'MinPluginVersions',
                ],
                [
                    'label' => __( 'Remove Actions Before Filtering option is enabled', 'ajax-search-for-woocommerce' ),
                    'test'  => 'ProductFilterByWBW',
                ]
            ],
            'async'  => [[
                'label' => __( 'Not regenerated images', 'ajax-search-for-woocommerce' ),
                'test'  => 'NotRegeneratedImages',
            ]],
        ];
        if ( !dgoraAsfwFs()->is_premium() ) {
            // List of tests only for free plugin version
            $tests['direct'][] = [
                'label' => __( 'TranslatePress', 'ajax-search-for-woocommerce' ),
                'test'  => 'TranslatePress',
            ];
        }
        $tests = apply_filters( 'dgwt/wcas/troubleshooting/tests', $tests );
        return $tests;
    }

    /**
     * Check if WP-Cron has missed events
     *
     * @return bool
     */
    public static function hasWpCronMissedEvents() {
        if ( !self::checkRequirements() ) {
            return false;
        }
        if ( !class_exists( 'WP_Site_Health' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
        }
        $siteHealth = \WP_Site_Health::get_instance();
        $data = $siteHealth->get_test_scheduled_events();
        if ( $data['status'] === 'critical' || $data['status'] === 'recommended' && $siteHealth->has_missed_cron() ) {
            return true;
        }
        return false;
    }

    /**
     * Check if Elementor elements contains specific widget type
     *
     * @param $elements
     * @param $widget
     *
     * @return bool
     */
    private function doesElementorElementsContainsWidget( $elements, $widget ) {
        $result = false;
        if ( !is_array( $elements ) || empty( $elements ) || empty( $widget ) ) {
            return false;
        }
        if ( isset( $elements['widgetType'] ) && $elements['widgetType'] === $widget ) {
            $result = true;
        }
        // Plain array of elements
        if ( !isset( $elements['elements'] ) ) {
            foreach ( $elements as $element ) {
                $result = $result || $this->doesElementorElementsContainsWidget( $element, $widget );
            }
            // Assoc array - single element
        } elseif ( isset( $elements['elements'] ) && is_array( $elements['elements'] ) && !empty( $elements['elements'] ) ) {
            $result = $result || $this->doesElementorElementsContainsWidget( $elements['elements'], $widget );
        }
        return $result;
    }

    /**
     * Check requirements
     *
     * We need WordPress 5.4 from which the Site Health module is available.
     *
     * @return bool
     */
    private static function checkRequirements() {
        global $wp_version;
        return version_compare( $wp_version, '5.4.0' ) >= 0;
    }

    /**
     * Run test directly
     *
     * @param $callback
     *
     * @return mixed|void
     */
    private function performTest( $callback ) {
        return apply_filters( 'dgwt/wcas/troubleshooting/test-result', call_user_func( $callback ) );
    }

    /**
     * Check if test exists
     *
     * @param $test
     *
     * @return bool
     */
    private function isTestExists( $test, $type = 'async' ) {
        if ( empty( $test ) ) {
            return false;
        }
        $tests = self::getTests();
        foreach ( $tests[$type] as $value ) {
            if ( $value['test'] === $test ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get table with server environment
     *
     * @return string
     */
    private function getDebugData() {
        if ( !class_exists( 'WP_Debug_Data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
        }
        $result = '';
        $info = \WP_Debug_Data::debug_data();
        if ( isset( $info['wp-server']['fields'] ) ) {
            ob_start();
            ?>
			<br/>
			<hr/><br/>
			<p><b><?php 
            _e( 'Server environment', 'ajax-search-for-woocommerce' );
            ?></b></p>
			<table style="max-width: 600px" class="widefat striped" role="presentation">
				<tbody>
				<?php 
            foreach ( $info['wp-server']['fields'] as $field_name => $field ) {
                if ( is_array( $field['value'] ) ) {
                    $values = '<ul>';
                    foreach ( $field['value'] as $name => $value ) {
                        $values .= sprintf( '<li>%s: %s</li>', esc_html( $name ), esc_html( $value ) );
                    }
                    $values .= '</ul>';
                } else {
                    $values = esc_html( $field['value'] );
                }
                printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $field['label'] ), $values );
            }
            ?>
				</tbody>
			</table>
			<?php 
            $result = ob_get_clean();
        }
        return $result;
    }

    /**
     * Get result of async test
     *
     * @param string $test Test name
     *
     * @return array
     */
    private function getResult( $test ) {
        $asyncTestsResults = get_transient( self::TRANSIENT_RESULTS_KEY );
        if ( isset( $asyncTestsResults[$test] ) ) {
            return $asyncTestsResults[$test];
        }
        return [];
    }

    /**
     * Storing result of async test
     *
     * Direct tests do not need to be saved.
     *
     * @param $result
     */
    private function storeResult( $result ) {
        $asyncTestsResults = get_transient( self::TRANSIENT_RESULTS_KEY );
        if ( !is_array( $asyncTestsResults ) ) {
            $asyncTestsResults = [];
        }
        $asyncTestsResults[$result['test']] = $result;
        set_transient( self::TRANSIENT_RESULTS_KEY, $asyncTestsResults, 15 * 60 );
    }

    /**
     * Regenerate images
     *
     * @return void
     */
    private function regenerateImages() {
        if ( class_exists( 'WC_Regenerate_Images' ) ) {
            if ( method_exists( 'Jetpack', 'is_module_active' ) && \Jetpack::is_module_active( 'photon' ) ) {
                return;
            }
            if ( apply_filters( 'woocommerce_background_image_regeneration', true ) ) {
                \WC_Regenerate_Images::queue_image_regeneration();
            }
        }
        update_option( self::IMAGES_ALREADY_REGENERATED_OPT_KEY, '1' );
    }

}
