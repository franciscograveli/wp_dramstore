<?php

namespace DgoraWcas\Admin\Promo;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DgoraWcas\Helpers;

class FeedbackNotice {

	const ACTIVATION_DATE_OPT = 'dgwt_wcas_activation_date';

	const HIDE_NOTICE_OPT = 'dgwt_wcas_dismiss_review_notice';

	const DISMISS_AJAX_ACTION = 'dgwt_wcas_dismiss_notice';

	const REVIEW_URL = 'https://wordpress.org/support/plugin/ajax-search-for-woocommerce/reviews/?filter=5';

	/**
	 * @var NoticePolicy
	 */
	private $policy;

	public function __construct() {
		$this->policy = new NoticePolicy();

		add_action( 'admin_init', [ $this, 'checkInstallationDate' ] );

		add_action( 'wp_ajax_' . self::DISMISS_AJAX_ACTION, [ $this, 'dismissNotice' ] );

		add_action( 'admin_head', [ $this, 'loadStyle' ] );

		add_action( 'admin_footer', [ $this, 'printDismissJS' ] );
	}

	/**
	 * Check if is possible to display admin notice on the current screen
	 *
	 * @return bool
	 */
	private function canShowOnCurrentScreen() {
		return $this->policy->isSettingsPage();
	}

	/**
	 * Display feedback notice
	 *
	 * @return void
	 */
	public function displayNotice() {
		global $current_user;

		if ( $this->canShowOnCurrentScreen() && ! $this->policy->isPremium() ) {
			?>

			<div class="notice-info notice dgwt-wcas-notice dgwt-wcas-review-notice">
				<div class="dgwt-wcas-review-notice-logo"></div>
				<?php
				printf(
					__( "Hey %1\$s, it's Damian Góra from %2\$s. You have used this free plugin for some time now, and I hope you like it!", 'ajax-search-for-woocommerce' ),
					'<strong>' . $current_user->display_name . '</strong>',
					'<strong>' . DGWT_WCAS_NAME . '</strong>'
				);
				?>
				<br/>
				<?php
				printf(
					__( 'The FiboSearch team have spent countless hours developing it, and it would mean a lot to me if you %1$ssupport it with a quick review on WordPress.org.%2$s', 'ajax-search-for-woocommerce' ),
					'<strong><a target="_blank" href="' . self::REVIEW_URL . '">',
					'</a></strong>'
				);
				?>
				<div class="button-container">
					<a href="<?php echo self::REVIEW_URL; ?>" target="_blank" data-link="follow" class="button-secondary js-dgwt-review-notice-dismiss">
						<span class="dashicons dashicons-star-filled"></span>
						<?php printf( __( 'Review %s', 'ajax-search-for-woocommerce' ), DGWT_WCAS_NAME ); ?>
					</a>
					<a href="#" class="button-secondary js-dgwt-review-notice-dismiss dgwt-review-notice-dismiss">
						<span class="dashicons dashicons-no-alt"></span>
						<?php _e( 'No thanks', 'ajax-search-for-woocommerce' ); ?>
					</a>
				</div>
				<button class="dgwt-review-notice-dismiss-x js-dgwt-review-notice-dismiss"
						aria-label="<?php _e( 'Close', 'ajax-search-for-woocommerce' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
						<path
							d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path>
					</svg>
				</button>
			</div>
			<?php

		}
	}

	/**
	 * Check instalation date
	 *
	 * @return void
	 */
	public function checkInstallationDate() {
		$date = get_option( self::ACTIVATION_DATE_OPT );
		if ( empty( $date ) ) {
			add_option( self::ACTIVATION_DATE_OPT, time() );
		}

		if ( $this->policy->shouldShowFeedbackNotice() ) {
			add_action( 'admin_notices', [ $this, 'displayNotice' ] );
		}
	}

	/**
	 * Hide admin notice
	 *
	 * @return void
	 */
	public function dismissNotice() {
		if ( ! current_user_can( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		check_ajax_referer( 'dgwt_wcas_dismiss_feedback_notice' );

		update_option( self::HIDE_NOTICE_OPT, true );

		wp_send_json_success();
	}

	/**
	 * Print JS for close admin notice
	 *
	 * @return void
	 */
	public function printDismissJS() {
		if ( ! $this->canShowOnCurrentScreen() ) {
			return;
		}
		?>
		<script>
			(function ($) {

				$(document).on('click', '.js-dgwt-review-notice-dismiss', function () {
					var $box = $(this).closest('.dgwt-wcas-review-notice'),
						isLink = $(this).attr('data-link') === 'follow' ? true : false;

					$box.fadeOut(700);

					$.ajax({
						url: ajaxurl,
						data: {
							_wpnonce: '<?php echo wp_create_nonce( 'dgwt_wcas_dismiss_feedback_notice' ); ?>',
							action: '<?php echo self::DISMISS_AJAX_ACTION; ?>',
						}
					}).done(function (data) {

						setTimeout(function () {
							$box.remove();
						}, 700);

					});

					if (!isLink) {
						return false;
					}
				});

			}(jQuery));
		</script>

		<?php
	}

	/**
	 * Load the necessary CSS
	 *
	 * @return void
	 */
	public function loadStyle() {
		if ( $this->canShowOnCurrentScreen() ) {
			wp_enqueue_style( 'dgwt-wcas-admin-style' );
		}
	}
}
