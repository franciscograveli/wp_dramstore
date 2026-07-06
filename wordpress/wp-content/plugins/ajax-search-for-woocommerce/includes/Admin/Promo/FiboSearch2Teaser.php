<?php

namespace DgoraWcas\Admin\Promo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FiboSearch2Teaser {

	const URL = 'https://fibosearch.com/fibosearch-2-0-is-on-the-way/?utm_source=wp-admin&utm_medium=referral&utm_campaign=fibosearch-2-0-teaser&utm_gen=utmdc';

	const IMAGE_PATH = 'assets/img/fibosearch-2-teaser.png';

	const DISMISS_META_KEY = 'dgwt_wcas_dismiss_fibosearch_2_teaser';

	const DISMISS_AJAX_ACTION = 'dgwt_wcas_dismiss_fibosearch_2_teaser';

	/**
	 * @var NoticePolicy
	 */
	private $policy;

	public function __construct() {
		$this->policy = new NoticePolicy();

		add_action( 'current_screen', [ $this, 'maybeDisplayNotice' ] );
		add_action( 'wp_ajax_' . self::DISMISS_AJAX_ACTION, [ $this, 'dismissNotice' ] );
		add_action( 'admin_head', [ $this, 'loadStyle' ] );
		add_action( 'admin_footer', [ $this, 'printDismissJS' ] );
	}

	private function shouldShowNotice(): bool {
		return $this->policy->shouldShowFiboSearch2Teaser();
	}

	public function maybeDisplayNotice(): void {
		if ( ! $this->shouldShowNotice() ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'displayNotice' ] );
	}

	public function displayNotice(): void {
		?>
		<div class="notice notice-info dgwt-wcas-notice dgwt-wcas-fs2-teaser">
			<div class="dgwt-wcas-fs2-teaser__media">
				<img
					class="dgwt-wcas-fs2-teaser__image"
					src="<?php echo esc_url( DGWT_WCAS_URL . self::IMAGE_PATH ); ?>"
					alt="<?php esc_attr_e( 'FiboSearch 2.0 teaser', 'ajax-search-for-woocommerce' ); ?>"
					width="100"
					height="100"
				/>
			</div>
			<div class="dgwt-wcas-fs2-teaser__content">
				<h2 class="dgwt-wcas-fs2-teaser__title"><?php esc_html_e( 'FiboSearch 2.0 is on the way', 'ajax-search-for-woocommerce' ); ?></h2>
				<p class="dgwt-wcas-fs2-teaser__text"><?php esc_html_e( 'A faster, smarter, and more refined search experience with better relevance, deeper insights, and more control over search performance.', 'ajax-search-for-woocommerce' ); ?></p>
				<div class="dgwt-wcas-fs2-teaser__actions">
					<a class="button-primary dgwt-wcas-fs2-teaser__button" href="<?php echo esc_url( self::URL ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Preview what\'s coming', 'ajax-search-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
			<button class="dgwt-wcas-fs2-teaser__dismiss js-dgwt-fs2-teaser-dismiss" type="button" aria-label="<?php esc_attr_e( 'Close', 'ajax-search-for-woocommerce' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path>
				</svg>
			</button>
		</div>
		<?php
	}

	public function dismissNotice(): void {
		if ( ! is_user_logged_in() ) {
			wp_die( -1, 403 );
		}

		check_ajax_referer( 'dgwt_wcas_dismiss_fibosearch_2_teaser' );

		update_user_meta( get_current_user_id(), self::DISMISS_META_KEY, 1 );

		wp_send_json_success();
	}

	public function printDismissJS(): void {
		if ( ! $this->shouldShowNotice() ) {
			return;
		}
		?>
		<script>
			(function ($) {
				$(document).on('click', '.js-dgwt-fs2-teaser-dismiss', function () {
					var $box = $(this).closest('.dgwt-wcas-fs2-teaser');

					$box.fadeOut(250, function () {
						$box.remove();
					});

					$.ajax({
						url: ajaxurl,
						data: {
							_wpnonce: '<?php echo esc_js( wp_create_nonce( 'dgwt_wcas_dismiss_fibosearch_2_teaser' ) ); ?>',
							action: '<?php echo esc_js( self::DISMISS_AJAX_ACTION ); ?>'
						}
					});
				});
			}(jQuery));
		</script>
		<?php
	}

	public function loadStyle(): void {
		if ( ! $this->shouldShowNotice() ) {
			return;
		}

		wp_enqueue_style( 'dgwt-wcas-admin-style' );
	}
}
