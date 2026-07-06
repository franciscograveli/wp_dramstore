<?php
/**
 * Premium Tab
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Product Slider Carousel\Admin
 * @version 1.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>

<style>
	.section{
		margin-left: -20px;
		margin-right: -20px;
		font-family: "Raleway",san-serif;
	}
	.section h1{
		text-align: center;
		text-transform: uppercase;
		color: #808a97;
		font-size: 35px;
		font-weight: 700;
		line-height: normal;
		display: inline-block;
		width: 100%;
		margin: 50px 0 0;
	}
	.section ul{
		list-style-type: disc;
		padding-left: 15px;
	}
	.section:nth-child(even){
		background-color: #fff;
	}
	.section:nth-child(odd){
		background-color: #f1f1f1;
	}
	.section .section-title img{
		display: table-cell;
		vertical-align: middle;
		width: auto;
		margin-right: 15px;
	}
	.section h2,
	.section h3 {
		display: inline-block;
		vertical-align: middle;
		padding: 0;
		font-size: 24px;
		font-weight: 700;
		color: #808a97;
		text-transform: uppercase;
	}

	.section .section-title h2{
		display: table-cell;
		vertical-align: middle;
		line-height: 25px;
	}

	.section-title{
		display: table;
	}

	.section h3 {
		font-size: 14px;
		line-height: 28px;
		margin-bottom: 0;
		display: block;
	}

	.section p{
		font-size: 13px;
		margin: 25px 0;
	}
	.section ul li{
		margin-bottom: 4px;
	}
	.landing-container{
		max-width: 750px;
		margin-left: auto;
		margin-right: auto;
		padding: 50px 0 30px;
	}
	.landing-container:after{
		display: block;
		clear: both;
		content: '';
	}
	.landing-container .col-1,
	.landing-container .col-2{
		float: left;
		box-sizing: border-box;
		padding: 0 15px;
	}
	.landing-container .col-1 img{
		width: 100%;
	}
	.landing-container .col-1{
		width: 55%;
	}
	.landing-container .col-2{
		width: 45%;
	}
	.premium-cta{
		background-color: #808a97;
		color: #fff;
		border-radius: 6px;
		padding: 20px 15px;
	}
	.premium-cta:after{
		content: '';
		display: block;
		clear: both;
	}
	.premium-cta p{
		margin: 7px 0;
		font-size: 14px;
		font-weight: 500;
		display: inline-block;
		width: 60%;
	}
	.premium-cta a.button{
		border-radius: 6px;
		height: 60px;
		float: right;
		background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/upgrade.png) #ff643f no-repeat 13px 13px;
		border-color: #ff643f;
		box-shadow: none;
		outline: none;
		color: #fff;
		position: relative;
		padding: 9px 50px 9px 70px;
	}
	.premium-cta a.button:hover,
	.premium-cta a.button:active,
	.premium-cta a.button:focus{
		color: #fff;
		background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/upgrade.png) #971d00 no-repeat 13px 13px;
		border-color: #971d00;
		box-shadow: none;
		outline: none;
	}
	.premium-cta a.button:focus{
		top: 1px;
	}
	.premium-cta a.button span{
		line-height: 13px;
	}
	.premium-cta a.button .highlight{
		display: block;
		font-size: 20px;
		font-weight: 700;
		line-height: 20px;
	}
	.premium-cta .highlight{
		text-transform: uppercase;
		background: none;
		font-weight: 800;
		color: #fff;
	}

	@media (max-width: 768px) {
		.section{margin: 0}
		.premium-cta p{
			width: 100%;
		}
		.premium-cta{
			text-align: center;
		}
		.premium-cta a.button{
			float: none;
		}
	}

	@media (max-width: 480px){
		.wrap{
			margin-right: 0;
		}
		.section{
			margin: 0;
		}
		.landing-container .col-1,
		.landing-container .col-2{
			width: 100%;
			padding: 0 15px;
		}
		.section-odd .col-1 {
			float: left;
			margin-right: -100%;
		}
		.section-odd .col-2 {
			float: right;
			margin-top: 65%;
		}
	}

	@media (max-width: 320px){
		.premium-cta a.button{
			padding: 9px 20px 9px 70px;
		}

		.section .section-title img{
			display: none;
		}
	}
</style>
<?php
$premium_landing_uri = apply_filters( 'yith_plugin_fw_premium_landing_uri','https://yithemes.com/themes/plugins/yith-woocommerce-product-slider-carousel/', YWCPS_SLUG );
?>
<div class="landing">
<div class="section section-cta section-odd">
	<div class="landing-container">
		<div class="premium-cta">
			<p>
				<?php
				/* translators: %1$s is the tag <span>, %2$s is the tag </span> */
				echo sprintf( __( 'Upgrade to %1$spremium version%2$s of %1$sYITH WooCommerce Product Slider Carousel%2$s to benefit from all features!', 'yith-woocommerce-product-slider-carousel' ), '<span class="highlight">', '</span>' ); //phpcs:ignore WordPress.Security.EscapeOutput 
				?>
			</p>
			<a href="<?php echo esc_url( $premium_landing_uri ); ?>" target="_blank" class="premium-cta-button button btn">
				<span class="highlight"><?php esc_html_e( 'UPGRADE', 'yith-woocommerce-product-slider-carousel' ); ?></span>
				<span><?php esc_html_e( 'to the premium version', 'yith-woocommerce-product-slider-carousel' ); ?></span>
			</a>
		</div>
	</div>
</div>
<div class="section section-even clear" style="background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/01-bg.png) no-repeat #fff; background-position: 85% 75%">
	<h1><?PHP esc_html_e( 'Premium Features', 'yith-woocommerce-product-slider-carousel' ); ?></h1>
	<div class="landing-container">
		<div class="col-1">
			<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/01.png" alt="" />
		</div>
		<div class="col-2">
			<div class="section-title">
				<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/01-icon.png" alt=""/>
				<h2><?PHP esc_html_e( 'Tailor-made responsivity ', 'yith-woocommerce-product-slider-carousel' ); ?></h2>
			</div>
			<p>
				<?php
				/* translators: %1$s is the tag <b>, %2$s is the tag </b> */
				echo sprintf(
					__( 'Total control on your slider at any resolution! With the premium version of the plugin, you can set the number of elements to show simoultaneously in the slider according to the specific resolution. Three sizes considered: %1$sStandard Desktop%2$s (from  767 px to 991 px), %1$stablet%2$s (from 478 px to 766 px) and %1$smobile%2$s (from 0 to 478 px).', 'yith-woocommerce-product-slider-carousel' ),
					'<b>',
					'</b>'
				); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</p>
		</div>
	</div>
</div>
<div class="section section-odd clear" style="background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/02-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
	<div class="landing-container">
		<div class="col-2">
			<div class="section-title">
				<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/02-icon.png" alt="icon 02" />
				<h2><?php esc_html_e( 'Unlimited sliders', 'yith-woocommerce-product-slider-carousel' ); ?></h2>
			</div>
			<p>
				<?php esc_html_e( 'No restrictions to the number of sliders available. You will be able to create as many as you want to make products in your shop slide in your site pages', 'yith-woocommerce-product-slider-carousel' ); ?></p>
		</div>
		<div class="col-1">
			<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/02.png" alt="" />
		</div>
	</div>
</div>
<div class="section section-even clear" style="background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/03-bg.png) no-repeat #fff; background-position: 85% 100%">
	<div class="landing-container">
		<div class="col-1">
			<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/03.png" alt="" />
		</div>
		<div class="col-2">
			<div class="section-title">
				<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/03-icon.png" alt="icon 03" />
				<h2><?php esc_html_e( 'Product types', 'yith-woocommerce-product-slider-carousel' ); ?></h2>
			</div>
			<p>
			<?php
			esc_html_e(
				'Enjoy the possibility to show different product for each slider!
                If you did not want to show all products of your shop, you can select only some of them, according to the following selection criteria:',
				'yith-woocommerce-product-slider-carousel'
			);
			?>
			</p>
			<ul>
				<li><?php esc_html_e( '“on sale” products', 'yith-woocommerce-product-slider-carousel' ); ?></li>
				<li><?php esc_html_e( 'most purchased products', 'yith-woocommerce-product-slider-carousel' ); ?></li>
				<li><?php esc_html_e( 'free products', 'yith-woocommerce-product-slider-carousel' ); ?></li>
				<li><?php esc_html_e( 'featured products', 'yith-woocommerce-product-slider-carousel' ); ?></li>
				<li><?php esc_html_e( 'most recent products', 'yith-woocommerce-product-slider-carousel' ); ?></li>
				<li><?php esc_html_e( 'products with a specific ID (any product you want to show)', 'yith-woocommerce-product-slider-carousel' ); ?></li>
			</ul>
		</div>
	</div>
</div>
<div class="section section-odd clear" style="background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/04-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
	<div class="landing-container">
		<div class="col-2">
			<div class="section-title">
				<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/04-icon.png" alt="icon 04" />
				<h2><?php esc_html_e( 'Template layout', 'yith-woocommerce-product-slider-carousel' ); ?></h2>
			</div>
			<p>
				<?php
					/* translators: %1$s is the tag <b>, %2$s is the tag </b> */
				echo sprintf(
					__(
						'Graphic appearance is an essential requirement for a quality product and a business card to your users. With the premium version of the plugin, you will
                be able to create %1$sthree different layout%2$s templates and use them for sliders in your shop.',
						'yith-woocommerce-product-slider-carousel'
					),
					'<b>',
					'</b>'
				);//phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</p>
		</div>
		<div class="col-1">
			<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/04.png" alt="" />
		</div>
	</div>
</div>
<div class="section section-even clear" style="background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/05-bg.png) no-repeat #fff; background-position: 85% 100%">
	<div class="landing-container">
		<div class="col-1">
			<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/05.png" alt="" />
		</div>
		<div class="col-2">
			<div class="section-title">
				<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/05-icon.png" alt="icon 05" />
				<h2><?php esc_html_e( 'Hide price and “Add to cart” button', 'yith-woocommerce-product-slider-carousel' ); ?></h2>
			</div>
			<p>
				<?php
				esc_html_e(
					'Choose to show or hide price and/or “Add to cart” button for products shown in your slider.
                 Two tailor-made options that you can directly control from plugin option panel.',
					'yith-woocommerce-product-slider-carousel'
				);
				?>
				</p>
		</div>
	</div>
</div>
	<div class="section section-odd clear" style="background: url(<?php echo esc_attr( YWCPS_URL ); ?>assets/images/06-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
		<div class="landing-container">
			<div class="col-2">
				<div class="section-title">
					<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/06-icon.png" alt="icon 04" />
					<h2><?php esc_html_e( 'Widget', 'yith-woocommerce-product-slider-carousel' ); ?></h2>
				</div>
				<p>
					<?php
					/* translators: %1$s is the tag <b>, %2$s is the tag </b> */
					echo sprintf( __( 'Add your sliders in sidebars using the widget “YITH WooCommerce Product Slider Carousel”. Don’t leave anything to chance.', 'yith-woocommerce-product-slider-carousel' ), '<b>', '</b>' );//phpcs:ignore WordPress.Security.EscapeOutput 
					?>
				</p>
			</div>
			<div class="col-1">
				<img src="<?php echo esc_attr( YWCPS_URL ); ?>assets/images/06.png" alt="" />
			</div>
		</div>
	</div>
<div class="section section-cta section-odd">
	<div class="landing-container">
		<div class="premium-cta">
			<p>
				<?php
				/* translators: %1$s is the tag <span>, %2$s is the tag </span> */
				echo sprintf( __( 'Upgrade to %1$spremium version%2$s of %1$sYITH WooCommerce Product Slider Carousel%2$s to benefit from all features!', 'yith-woocommerce-product-slider-carousel' ), '<span class="highlight">', '</span>' ); //phpcs:ignore WordPress.Security.EscapeOutput 
				?>
			</p>
			<a href="<?php echo esc_attr( $premium_landing_uri ); ?>" target="_blank" class="premium-cta-button button btn">
				<span class="highlight"><?php esc_html_e( 'UPGRADE', 'yith-woocommerce-product-slider-carousel' ); ?></span>
				<span><?php esc_html_e( 'to the premium version', 'yith-woocommerce-product-slider-carousel' ); ?></span>
			</a>
		</div>
	</div>
</div>
</div>
