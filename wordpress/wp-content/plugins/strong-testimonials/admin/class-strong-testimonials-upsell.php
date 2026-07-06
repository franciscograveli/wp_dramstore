<?php
/**
 * Class Strong_Testimonials_Upsell
 *
 * @since 2.38
 */
class Strong_Testimonials_Upsell {

	/**
	 * Holds the upsells object
	 *
	 * @var bool
	 */
	private $extensions = false;

	public $store_upgrade_url;

	public function __construct() {
		$this->set_store_upgrade_url();
		$options = get_option( 'wpmtst_options' );

		if ( apply_filters( 'wpmtst_disable_upsells', false ) ) {
			return;
		}

		require_once WPMTST_ADMIN . 'rest-api/class-strong-testimonials-extensions-base.php';
		$this->extensions = Strong_Testimonials_Extensions_Base::get_instance();

		add_action( 'wpmtst_admin_after_settings_form', array( $this, 'general_upsell' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-role-management' ) ) {
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_role_upsell' ), 20 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-country-selector' ) ) {
			add_action( 'wpmtst_after_form_type_selection', array( $this, 'output_country_selector_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_country_selector_upsell' ), 95 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-custom-fields' ) ) {
			add_action( 'wpmtst_after_form_type_selection', array( $this, 'output_custom_fields_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_custom_fields_upsell' ), 90 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-multiple-forms' ) ) {
			add_action( 'wpmtst_before_fields_settings', array( $this, 'output_multiple_form_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_multiple_form_upsell' ), 30 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-review-markup' ) ) {
			add_action( 'wpmtst_view_editor_after_groups', array( $this, 'output_review_markup_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_review_markup_upsell' ), 15 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-advanced-views' ) ) {
			add_action( 'wpmtst_view_editor_after_group_select', array( $this, 'output_advanced_views_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_advanced_views_upsell' ), 35 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-captcha' ) ) {
			add_action( 'wpmtst_fields_before_fields_editor_preview', array( $this, 'output_captcha_editor_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_captcha_upsell' ), 40 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-pro-templates' ) ) {
			add_action( 'wpmtst_views_after_template_list', array( $this, 'output_pro_templates_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_pro_templates_upsell' ), 20 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-emails' ) ) {
			add_action( 'wpmtst_after_mail_notification_settings', array( $this, 'output_enhanced_emails_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_enhanced_emails_upsell' ), 45 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-infinite-scroll' ) ) {
			add_action( 'wpmtst_view_editor_pagination_row_end', array( $this, 'output_infinite_scroll_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_infinite_scroll_upsell' ), 50 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-filters' ) ) {
			add_action( 'wpmtst_after_style_view_section', array( $this, 'output_filters_upsell' ) );
			add_filter( 'wpmtst_general_upsell_items', array( $this, 'add_filters_upsell' ), 15 );
		}

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-mailchimp' ) ) {
			add_action( 'wpmtst_after_form_settings', array( $this, 'output_mailchip_form_settings_upsell' ) );
		}
	}

	/**
	 * Get upsell button HTML with filterable output.
	 *
	 * @param string $url     The button URL.
	 * @param string $context The upsell context identifier.
	 * @param string $text    The button text. Default 'Upgrade'.
	 *
	 * @return string
	 * @since 3.3.0
	 */
	public function get_upsell_button( $url, $context, $text = '' ) {
		if ( empty( $text ) ) {
			$text = __( 'Upgrade', 'strong-testimonials' );
		}
		$text   = esc_html( apply_filters( 'wpmtst_upsells_button_text', $text ) );
		$button = '<a class="button button-primary" target="_blank" href="' . esc_url( $url ) . '">' . $text . '</a>';

		return apply_filters( 'wpmtst_upsell_buttons', $button, $context );
	}

	public function add_meta_boxes() {

		if ( $this->extensions->is_upgradable_addon( 'strong-testimonials-importer' ) ) {

			// remove "submitdiv" metabox so we can add it back in desired order.
			$post_type = 'wpm-testimonial';
			remove_meta_box( 'post_submit_meta_box', $post_type, 'side' );
			add_meta_box( 'submitdiv', __( 'Publish', 'strong-testimonials' ), 'post_submit_meta_box', $post_type, 'side', 'high' );

			add_meta_box(
				'wpmtst-importer-upsell',      // Unique ID
				esc_html__( 'Import', 'strong-testimonials' ),    // Title
				array( $this, 'output_importer_upsell' ),   // Callback function
				'wpm-testimonial',         // Admin page (or post type)
				'side',         // Context
				'high'         // Priority
			);
		}
	}

	public function set_store_upgrade_url() {

		$this->store_upgrade_url = WPMTST_STORE_UPGRADE_URL . '?utm_source=st-lite&utm_campaign=upsell';

		//append license key
		$license = trim( get_option( 'strong_testimonials_license_key' ) );
		if ( $license ) {
			$this->store_upgrade_url .= '&license=' . $license;
		}
	}

	public function output_importer_upsell() {
		?>
		<div class="wpmtst-alert">
			<h2><?php esc_html_e( 'Automatically pull in & display new reviews as your customers leave their feedback on external platforms', 'strong-testimonials' ); ?></h2>
			<p><?php esc_html_e( 'Upgrade today and get the ability to import testimonials from:', 'strong-testimonials' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Facebook', 'strong-testimonials' ); ?></li>
				<li><?php esc_html_e( 'Google', 'strong-testimonials' ); ?></li>
				<li><?php esc_html_e( 'Yelp', 'strong-testimonials' ); ?></li>
				<li><?php esc_html_e( 'Zomato', 'strong-testimonials' ); ?></li>
				<li><?php esc_html_e( 'WooCommerce', 'strong-testimonials' ); ?></li>
				<li><?php esc_html_e( 'and more...', 'strong-testimonials' ); ?></li>
			</ul>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=importer-metabox', 'importer-metabox', __( 'Upgrade Now', 'strong-testimonials' ) ); ?>
			</p>
		</div>
		<?php
	}

	public function general_upsell() {

		$general_upsells = apply_filters( 'wpmtst_general_upsell_items', array() );

		if ( ! empty( $general_upsells ) ) {

			?>

		<div class="wpmtst-settings-upsell">
			<div class="wpmtst-alert">
				<h3><?php esc_html_e( 'Upgrade now', 'strong-testimonials' ); ?></h3>
				<ul>
					<?php foreach ( $general_upsells as $general_upsell ) { ?>
						<li>
							<span>
								<?php echo wp_kses_post( $general_upsell ); ?>
							</span>
						</li>
					<?php } ?>
				</ul>

				<?php
				$button_url  = WPMTST_STORE_URL . '/pricing?utm_source=st-lite&utm_campaign=upsell&utm_medium=general-settings-upsell';
				$button_text = esc_html( apply_filters( 'wpmtst_upsells_button_text', __( 'Upgrade now', 'strong-testimonials' ) ) );
				$button      = '<a href="' . esc_url( $button_url ) . '" target="_blank" class="button button-primary button-hero" style="width:100%;display:block;margin-top:20px;text-align:center;">' . $button_text . '</a>';
				echo apply_filters( 'wpmtst_upsell_buttons', $button, 'general-settings' );
				?>

			</div>
		</div>

			<?php
		}
	}

	public function add_role_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonial extension page.
			esc_html__( 'Control who approves testimonials or who has access to the plugins’ settings panel with %s extension. Get total granular control over who has access to your testimonials.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/role-management?utm_source=st-lite&utm_campaign=upsell&utm_medium=role-management-general-upsell' ),
				esc_html__( 'Role Management', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Country Selector
	*/
	public function output_country_selector_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 10px">
			<?php esc_html_e( 'Want to know where are your customers located?', 'strong-testimonials' ); ?>
			<br/>
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonial extension page.
				esc_html__( 'Install the %s extension', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/country-selector?utm_source=st-lite&utm_campaign=upsell&utm_medium=fields-country-selector-upsell' ),
					esc_html__( 'Strong Testimonials: Country Selector', 'strong-testimonials' )
				)
			);
			?>
			<p>

				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=fields-country-selector-upsell', 'country-selector' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_country_selector_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Show where your customers are located with the %s extension. ', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/country-selector?utm_source=st-lite&utm_campaign=upsell&utm_medium=country-selector-general-upsell' ),
				esc_html__( 'Country Selector', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Custom fields
	*/
	public function output_custom_fields_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 10px">
			<?php esc_html_e( 'Know your customers by having access to more advanced custom fields.', 'strong-testimonials' ); ?>
			<br/>
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'Install the %s extension', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/custom-fields?utm_source=st-lite&utm_campaign=upsell&utm_medium=fields-custom-fields-upsell' ),
					esc_html__( 'Strong Testimonials: Custom Fields', 'strong-testimonials' )
				)
			);
			?>
			<p>

				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=fields-custom-fields-upsell', 'custom-fields' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_custom_fields_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Get to know your customers by installing our %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/custom-fields?utm_source=st-lite&utm_campaign=upsell&utm_medium=custom-fields-general-upsell' ),
				esc_html__( 'Custom Fields', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	* Multiple forms
	*/
	public function output_multiple_form_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 10px">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'Create multiple submission forms by installing the %s extension.', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/multiple-forms?utm_source=st-lite&utm_campaign=upsell&utm_medium=fields-multiple-forms-upsell' ),
					esc_html__( 'Strong Testimonials: Multiple Forms', 'strong-testimonials' )
				)
			);
			?>
			<p>

				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=fields-multiple-forms-upsell', 'multiple-forms' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_multiple_form_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Create multiple submission forms by installing the %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/multiple-forms?utm_source=st-lite&utm_campaign=upsell&utm_medium=multiple-forms-general-upsell' ),
				esc_html__( 'Multiple Forms', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	* Review Markup
	*/
	public function output_review_markup_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 10px">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'Add SEO-friendly & Schema.org compliant Testimonials with our %s extension.', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/review-markup?utm_source=st-lite&utm_campaign=upsell&utm_medium=views-review-markup-upsell' ),
					esc_html__( 'Strong Testimonials: Review Markup', 'strong-testimonials' )
				)
			);
			?>
				<ul>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'With this extensions, search engines will display star ratings in search results for your site.', 'strong-testimonials' ); ?></li>
				</ul>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=views-review-markup-upsell', 'review-markup' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_review_markup_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Add SEO-friendly & Schema.org compliant Testimonials with our %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/review-markup?utm_source=st-lite&utm_campaign=upsell&utm_medium=review-markup-general-upsell' ),
				esc_html__( 'Review Markup', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Advanced Views
	*/
	public function output_advanced_views_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 1.5rem">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'With the %s extension you can:', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/advanced-views?utm_source=st-lite&utm_campaign=upsell&utm_medium=views-advanced-views-upsell' ),
					esc_html__( 'Strong Testimonials: Advanced Views', 'strong-testimonials' )
				)
			);

			?>
			<ul>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'filter & display testimonials based on their rating or on a pre-defined condition.', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'easily define the display order of your testimonial fields. Re-order the name, image, url and testimonial content fields through drag & drop.', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'edit, in real time, the way your testimonials will look on your site. Stop losing clients because of poor design.', 'strong-testimonials' ); ?></li>

			</ul>
			<p>

				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=views-advanced-views-upsell', 'advanced-views' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_advanced_views_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Start filtering, changing the order, or even editing your testimonials in real-time with the %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/advanced-views?utm_source=st-lite&utm_campaign=upsell&utm_medium=advanced-views-general-upsell' ),
				esc_html__( 'Advanced Views', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Captcha extensio
	*/
	public function output_captcha_editor_upsell() {
		?>
		<div class="wpmtst-alert">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'Protect your form against spam with the %s extension.', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/captcha?utm_source=st-lite&utm_campaign=upsell&utm_medium=form-settings-upsell' ),
					esc_html__( 'Strong Testimonials: Captcha', 'strong-testimonials' )
				)
			);
			?>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=form-settings-captcha-upsell', 'captcha-editor' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_captcha_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Protect your form against spam. Add Google ReCaptcha or honeypot anti-spam with the %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/captcha?utm_source=st-lite&utm_campaign=upsell&utm_medium=form-settings-captcha-general-upsell' ),
				esc_html__( 'Captcha', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	PRO Templates
	*/
	public function output_pro_templates_upsell() {
		?>
		<div class="wpmtst-alert">
			<?php
			echo wp_kses_post( sprintf( __( 'With the %1$sStrong Testimonials: PRO Templates%2$s you can impress your potential clients with profesionally designed, pixel-perfect templates that increase your chances of standing out and landing more clients.', 'strong-testimonials' ), '<a href="' . WPMTST_STORE_URL . '/extensions/pro-templates/" target="_blank">', '</a>' ) );
			?>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=views-pro-templates-upsell', 'pro-templates' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_pro_templates_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Get access to professionally designed testimonial templates with the %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/pro-templates?utm_source=st-lite&utm_campaign=upsell&utm_medium=pro-templates-general-upsell' ),
				esc_html__( 'Pro Templates', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Enhanced Emails
	*/
	public function output_enhanced_emails_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 10px">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'Use the %s extension to:', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/enhanced-emails?utm_source=st-lite&utm_campaign=upsell&utm_medium=enhanced-emails-upsell' ),
					esc_html__( 'Strong Testimonials: Enhanced Emails', 'strong-testimonials' )
				)
			);
			?>
				<ul>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'send a thank you email to your client once his testimonial\'s approved', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'increase brand loyalty by showing you really care about your clients', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'keep your clients engaged and increase your chances of selling more', 'strong-testimonials' ); ?></li>
				</ul>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=enhanced-emails-upsell', 'enhanced-emails' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_enhanced_emails_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Send a thank-you email to your clients once their testimonial is approved using %s extension. This way, you increase brand loyalty and grow your chances of seeling more. ', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/enhanced-emails?utm_source=st-lite&utm_campaign=upsell&utm_medium=enhanced-emails-general-upsell' ),
				esc_html__( 'Enhanced Emails', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Inifinite Scroll
	*/
	public function output_infinite_scroll_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top: 10px">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'With the %s extension you can:', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/infinite-scroll?utm_source=st-lite&utm_campaign=upsell&utm_medium=infinite-scroll-upsell' ),
					esc_html__( 'Strong Testimonials: Infinite Scroll', 'strong-testimonials' )
				)
			);
			?>
				<ul>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'display a fixed number of testimonials on first view and have more of them load when the user starts scrolling', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'reduce your page\'s initial load time, making your site faster in the process and not driving clients away because of a slow loading website', 'strong-testimonials' ); ?></li>
				</ul>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=infinite-scroll-upsell', 'infinite-scroll' ); ?>
			</p>
		</div>
		<?php
	}

	public function add_infinite_scroll_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Reduce your page’s initial load time - display a fixed number of testimonials on the first view and have more loading when you scroll down with %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/infinite-scroll?utm_source=st-lite&utm_campaign=upsell&utm_medium=infinite-scroll-general-upsell' ),
				esc_html__( 'Infinite Scroll', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	/*
	Filters
	*/
	public function output_filters_upsell() {
		?>
		<div class="wpmtst-alert" style="margin-top:1.5rem;">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'Use the %s extensions to:', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/filters?utm_source=st-lite&utm_campaign=upsell&utm_medium=views-filters-upsell' ),
					esc_html__( 'Strong Testimonials: Filters', 'strong-testimonials' )
				)
			);
			?>
				<ul>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'create category-like filters for your testimonials', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'group testimonials by associated product or service', 'strong-testimonials' ); ?></li>
				<li class="wpmtst-upsell-checkmark"><?php esc_html_e( 'help potential clients appreciate the great work you do by showcasing reviews from other clients', 'strong-testimonials' ); ?></li>
				</ul>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=filters-upsell', 'filters' ); ?>
			</p>
		</div>
		<?php
	}
	public function add_filters_upsell( $upsells ) {
		$upsell = sprintf(
			// translators: %s is a link to a Strong Testimonials extension page.
			esc_html__( 'Add category-like filters for testimonials, group testimonials by associated product/service, and help potential clients appreciate the great work you do by showcasing reviews from other clients with %s extension.', 'strong-testimonials' ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( WPMTST_STORE_URL . '/extensions/filters?utm_source=st-lite&utm_campaign=upsell&utm_medium=filters-general-upsell' ),
				esc_html__( 'Filters', 'strong-testimonials' )
			)
		);

		$upsells[] = $upsell;
		return $upsells;
	}

	public function output_mailchip_form_settings_upsell() {
		?>
		<hr>

		<h3><?php esc_html_e( 'Mailchimp', 'strong-testimonials' ); ?></h3>

		<div class="wpmtst-alert">
			<?php
			printf(
				// translators: %s is a link to a Strong Testimonials extension page.
				esc_html__( 'With this extension you can automatically subscribe your users to a MailChimp email list. Follow up with a targeted message or a coupon to thank them for leaving a good review. Unlock even more marketing & automation potential. ', 'strong-testimonials' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( WPMTST_STORE_URL . '/extensions/mailchimp?utm_source=st-lite&utm_campaign=upsell&utm_medium=form-settings-upsell' ),
					esc_html__( 'Strong Testimonials: Captcha', 'strong-testimonials' )
				)
			);
			?>
			<p>
				<?php echo $this->get_upsell_button( $this->store_upgrade_url . '&utm_medium=form-settings-captcha-upsell', 'mailchimp' ); ?>
			</p>
		</div>
		<?php
	}
}


new Strong_Testimonials_Upsell();
