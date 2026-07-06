<?php

namespace DgoraWcas\Analytics;

use DgoraWcas\Helpers;
use DgoraWcas\Multilingual;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget {
	/**
	 * @var Analytics
	 */
	private $analytics;

	/**
	 * @var UserInterface
	 */
	private $ui;

	/**
	 * Constructor
	 *
	 * @param Analytics $analytics
	 * @param UserInterface $ui
	 */
	public function __construct( Analytics $analytics, UserInterface $ui ) {
		$this->analytics = $analytics;
		$this->ui        = $ui;
	}

	public function init() {
		if ( $this->analytics->isModuleEnabled() && $this->isCriticalSearchesWidgetEnabled() ) {
			if ( current_user_can( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) {
				add_action( 'wp_dashboard_setup', [ $this, 'addWidget' ] );

				if ( Multilingual::isMultilingual() ) {
					add_action( 'admin_init', [ $this, 'enqueueTabsScript' ], 5 );
				}
			}
		}
	}

	/**
	 * Check if the Analytics widget is enabled
	 *
	 * @return bool
	 */
	public function isCriticalSearchesWidgetEnabled() {
		return DGWT_WCAS()->settings->getOption( 'analytics_critical_searches_widget_enabled', 'off' ) === 'on';
	}

	public function addWidget() {
		wp_add_dashboard_widget(
			'fibosearch_analytics_critical_searches',
			esc_html__( 'FiboSearch - Search Analytics', 'ajax-search-for-woocommerce' ),
			[ $this, 'render' ]
		);
	}

	public function render() {
		$data = new Data();

		$vars = [
			'days'                    => $this->ui->getExpirationInDays(),
			'critical-searches'       => [],
			'critical-searches-total' => 0,
			'settings-analytics-url'  => admin_url( 'admin.php?page=dgwt_wcas_settings#analytics' ),
			'multilingual'            => [],
		];

		if ( Multilingual::isMultilingual() ) {
			$vars['multilingual'] = [
				'current-lang' => Multilingual::getCurrentLanguage(),
				'langs'        => [],
			];
			foreach ( Multilingual::getLanguages() as $lang ) {
				$data->setLang( $lang );
				$vars['multilingual']['langs'][ $lang ] = [
					'name'                    => Multilingual::getLanguageField( $lang, 'name' ),
					'critical-searches'       => $data->getCriticalSearches( UserInterface::CRITICAL_SEARCHES_LOAD_LIMIT ),
					'critical-searches-total' => $data->getTotalCriticalSearches(),
				];
			}
		} else {
			$vars['critical-searches']       = $data->getCriticalSearches( UserInterface::CRITICAL_SEARCHES_LOAD_LIMIT );
			$vars['critical-searches-total'] = $data->getTotalCriticalSearches();
		}

		ob_start();
		?>
		<h3><strong><?php _e( 'Critical searches without result', 'ajax-search-for-woocommerce' ); ?></strong></h3>
		<?php if ( ! empty( $vars['multilingual'] ) ) { ?>
			<p>
				<?php _e( 'Language', 'ajax-search-for-woocommerce' ); ?>:
				<span class="dgwt-wcas-widget-tab-wrapper">
					<?php
					$next = false;
					foreach ( $vars['multilingual']['langs'] as $lang => $langData ) {
						if ( $next ) {
							echo ' | ';
						}
						printf( '<a class="dgwt-wcas-widget-tab" title="%s" href="%s">%s</a>', esc_attr( $langData['name'] ), esc_attr( '#dgwt-wcas-widget-tab-content-' . $lang ), esc_html( $langData['name'] ) );
						$next = true;
					}
					?>
				</span>
			</p>
			<?php
			foreach ( $vars['multilingual']['langs'] as $lang => $langName ) {
				?>
				<div
					class="dgwt-wcas-widget-tab-content"
					id="dgwt-wcas-widget-tab-content-<?php echo esc_attr( $lang ); ?>">
					<?php
					$this->renderTable(
						array_merge( $vars, $vars['multilingual']['langs'][ $lang ] )
					);
					?>
				</div>
				<?php
			}
		} else {
			$this->renderTable( $vars );
		}
		?>
		<p>
			<?php printf( __( 'Go to FiboSearch Settings page → %s to see more.', 'ajax-search-for-woocommerce' ), sprintf( '<a title="%2$s" href="%1$s">%2$s</a>', $vars['settings-analytics-url'], esc_attr__( 'Analytics tab', 'ajax-search-for-woocommerce' ) ) ); ?>
		</p>
		<?php
		echo ob_get_clean();
	}

	private function renderTable( $vars ) {
		if ( ! empty( $vars['critical-searches'] ) ) {
			?>
			<p>
				<?php
				// TODO
				//phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders,WordPress.WP.I18n.MissingSingularPlaceholder
				printf( _n( 'The FiboSearch analyzer found <b>1 critical search phrase</b>.', 'The FiboSearch analyzer found <b>%d critical search phrases</b>.', $vars['critical-searches-total'], 'ajax-search-for-woocommerce' ), $vars['critical-searches-total'] );
				echo ' ';
				//phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders,WordPress.WP.I18n.MissingSingularPlaceholder
				printf( _n( 'These phrases have been typed by users over the last 1 day.', 'These phrases have been typed by users over the last %d days.', $vars['days'], 'ajax-search-for-woocommerce' ), $vars['days'] );
				echo ' ';
				_e( "These phrases don`t return any search results. It's time to fix it.", 'ajax-search-for-woocommerce' );
				?>
			</p>
			<table class="widefat fixed">
				<thead>
				<tr>
					<th>#</th>
					<th><?php _e( 'Phrase', 'ajax-search-for-woocommerce' ); ?></th>
					<th><?php _e( 'Repetitions', 'ajax-search-for-woocommerce' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i = 1;
				foreach ( $vars['critical-searches'] as $row ) {
					?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo esc_html( $row['phrase'] ); ?></td>
						<td><?php echo esc_html( $row['qty'] ); ?></td>
					</tr>
					<?php
					$i ++;
				}
				?>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<p>
				<?php printf( __( "Fantastic! The FiboSearch analyzer hasn't found any critical search phrases for the last %d days.", 'ajax-search-for-woocommerce' ), $vars['days'] ); ?>
			</p>
			<?php
		}
	}

	public function enqueueTabsScript() {
		wp_enqueue_style( 'jquery' );
		ob_start();
		?>
		<script>
			(function ($) {
					$(document).ready(function () {
						const tabs = $('.dgwt-wcas-widget-tab');
						tabs.on('click', function (event) {
							event.preventDefault();

							$('.dgwt-wcas-widget-tab.dgwt-wcas-widget-tab-active').removeClass('dgwt-wcas-widget-tab-active');
							$(this).addClass('dgwt-wcas-widget-tab-active');
							$('.dgwt-wcas-widget-tab-content').hide();
							$($(this).attr('href')).show();
						});

						if (tabs.length > 0) {
							$(tabs[0]).trigger('click');
						}
					});
				}(jQuery)
			);
		</script>
		<?php
		$script = str_replace( [ '<script>', '</script>' ], [ '', '' ], ob_get_clean() );
		wp_add_inline_script( 'jquery', $script );
	}
}
