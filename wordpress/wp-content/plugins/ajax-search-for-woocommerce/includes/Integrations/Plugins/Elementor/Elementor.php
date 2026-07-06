<?php

namespace DgoraWcas\Integrations\Plugins\Elementor;

use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Elementor extends AbstractPluginIntegration {
    protected const VERSION_CONST = 'ELEMENTOR_PRO_VERSION';

    protected const MIN_VERSION = '3.6.0';

    protected const LABEL = 'Elementor Pro';

    public function init() : void {
        add_action( 'elementor/widgets/register', [$this, 'registerWidgets'], 20 );
        add_action( 'elementor/editor/before_enqueue_scripts', [$this, 'editorEnqueueScripts'] );
    }

    /**
     * @param Widgets_Manager $widgets_manager
     *
     * @return void
     */
    public function registerWidgets( $widgets_manager ) {
        // Register "FiboSearch" widget.
        $widgets_manager->register( new FiboSearchWidget() );
    }

    /**
     * @return void
     */
    public function editorEnqueueScripts() {
        wp_enqueue_style(
            'fibosearch-elementor-fibosearchicon',
            DGWT_WCAS_URL . 'assets/elementor-icons/style.css',
            [],
            DGWT_WCAS_VERSION
        );
    }

}
