<?php

namespace DgoraWcas\Admin\Promo;

use DgoraWcas\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FiboFiltersTeaser {

	const URL = 'https://fibofilters.com/?utm_source=wp-admin&utm_medium=referral&utm_campaign=fibofilters-teaser&utm_gen=utmdc';

	const LOGO_PATH = 'assets/img/fibofilters-logo.png';

	/**
	 * Settings API hook for the bottom of the "Starting" form.
	 *
	 * @var string
	 */
	private $renderHook;

	public function __construct() {
		$this->renderHook = sanitize_title( DGWT_WCAS_SETTINGS_KEY ) . '-form_bottom_dgwt_wcas_basic';

		add_action( $this->renderHook, [ $this, 'render' ] );
		add_action( 'admin_head', [ $this, 'loadStyle' ] );
	}

	public function render(): void {
		if ( ! Helpers::isSettingsPage() ) {
			return;
		}
		?>
		<table class="form-table dgwt-wcas-fibofilters-teaser-table" role="presentation">
			<tbody>
			<tr class="dgwt-wcas-only-desc">
				<th scope="row" aria-hidden="true"></th>
				<td>
					<a
						class="dgwt-wcas-fibofilters-teaser"
						href="<?php echo esc_url( self::URL ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php esc_attr_e( 'Try FiboFilters', 'ajax-search-for-woocommerce' ); ?>"
					>
						<span class="dgwt-wcas-fibofilters-teaser__logo-wrap">
							<img
								class="dgwt-wcas-fibofilters-teaser__logo"
								src="<?php echo esc_url( DGWT_WCAS_URL . self::LOGO_PATH ); ?>"
								alt="<?php esc_attr_e( 'FiboFilters', 'ajax-search-for-woocommerce' ); ?>"
								loading="lazy"
								decoding="async"
							/>
						</span>
						<div class="dgwt-wcas-fibofilters-teaser__content">
							<span class="dgwt-wcas-fibofilters-teaser__title"><?php esc_html_e( 'Faster search. Faster filtering.', 'ajax-search-for-woocommerce' ); ?></span>
							<span class="dgwt-wcas-fibofilters-teaser__link">
								<?php esc_html_e( 'Try FiboFilters', 'ajax-search-for-woocommerce' ); ?>
							</span>
						</div>
					</a>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	public function loadStyle(): void {
		if ( ! Helpers::isSettingsPage() ) {
			return;
		}

		wp_enqueue_style( 'dgwt-wcas-admin-style' );
	}
}
