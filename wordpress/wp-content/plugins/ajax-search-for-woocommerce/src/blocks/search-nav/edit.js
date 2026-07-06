/** @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/ */
import { Disabled, PanelBody, SelectControl, ToggleControl } from '@wordpress/components';

/** @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-server-side-render/ */
import ServerSideRender from '@wordpress/server-side-render';

/** @see https://developer.wordpress.org/block-editor/packages/packages-i18n/ */
import { __ } from '@wordpress/i18n';

/** @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/ */
import { useBlockProps, InspectorControls, PanelColorSettings } from '@wordpress/block-editor';

/** @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-data/ */
import { useSelect } from '@wordpress/data';

/** @see https://www.npmjs.com/package/@wordpress/scripts#using-css */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit( props ) {
	const { deviceType } = useSelect( ( select ) => {
		let deviceTypeTmp = '';
		const coreEditor = select( 'core/editor' );

		if ( typeof coreEditor === 'object' && typeof coreEditor.getDeviceType === 'function' ) {
			deviceTypeTmp = coreEditor.getDeviceType();
			// eslint-disable-next-line no-undef
		} else if ( typeof editPost === 'object' && editPost.__experimentalGetPreviewDeviceType === 'function' ) {
			deviceTypeTmp = editPost.__experimentalGetPreviewDeviceType(); // eslint-disable-line no-undef
		}

		return {
			deviceTypeTmp,
		};
	}, [] );
	const blockProps = useBlockProps( {
		className: `wp-block-fibosearch-search__device-preview-${ deviceType.toLowerCase() }`,
	} );
	const { attributes } = props;
	const {
		attributes: { darkenedBackground, mobileOverlay, inheritPluginSettings, layout, iconColor },
		name,
		setAttributes,
	} = props;

	function getColorSettings() {
		if ( inheritPluginSettings ) {
			return null;
		}

		if ( layout === 'classic' ) {
			return null;
		}

		return (
			<PanelColorSettings
				__experimentalHasMultipleOrigins
				__experimentalIsRenderedInSidebar
				title={ __( 'Color', 'ajax-search-for-woocommerce' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: iconColor,
						onChange: ( newColor ) => {
							setAttributes( {
								iconColor: newColor,
							} );
						},
						label: __( 'Icon', 'ajax-search-for-woocommerce' ),
					},
				] }
			></PanelColorSettings>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Settings', 'ajax-search-for-woocommerce' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Inherit global plugin settings', 'ajax-search-for-woocommerce' ) }
						checked={ inheritPluginSettings }
						onChange={ () =>
							setAttributes( {
								inheritPluginSettings: ! inheritPluginSettings,
							} )
						}
						__nextHasNoMarginBottom
					/>
					{ inheritPluginSettings ? null : (
						<SelectControl
							label={ __( 'Layout', 'ajax-search-for-woocommerce' ) }
							value={ layout }
							options={ [
								{
									label: __( 'Search bar', 'ajax-search-for-woocommerce' ),
									value: 'classic',
								},
								{
									label: __( 'Search icon', 'ajax-search-for-woocommerce' ),
									value: 'icon',
								},
								{
									label: __( 'Icon on mobile, search bar on desktop', 'ajax-search-for-woocommerce' ),
									value: 'icon-flexible',
								},
								{
									label: __( 'Icon on desktop, search bar on mobile', 'ajax-search-for-woocommerce' ),
									value: 'icon-flexible-inv',
								},
							] }
							onChange={ ( newLayout ) => {
								setAttributes( {
									layout: newLayout,
								} );
								if ( newLayout === 'icon' || newLayout === 'icon-flexible' || newLayout === 'icon-flexible-inv' ) {
									setAttributes( {
										mobileOverlay: true,
									} );
								}
							} }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
					) }
					{ inheritPluginSettings ? null : (
						<ToggleControl
							label={ __( 'Darkened background', 'ajax-search-for-woocommerce' ) }
							checked={ darkenedBackground }
							onChange={ () =>
								setAttributes( {
									darkenedBackground: ! darkenedBackground,
								} )
							}
							__nextHasNoMarginBottom
						/>
					) }
					{ inheritPluginSettings ? null : (
						<ToggleControl
							label={ __( 'Overlay on mobile', 'ajax-search-for-woocommerce' ) }
							checked={ mobileOverlay }
							onChange={ () =>
								setAttributes( {
									mobileOverlay: ! mobileOverlay,
								} )
							}
							help={ mobileOverlay ? __( 'The search will open in overlay on mobile', 'ajax-search-for-woocommerce' ) : '' }
							__nextHasNoMarginBottom
						/>
					) }
					{ getColorSettings() }
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender block={ name } attributes={ attributes } />
			</Disabled>
		</div>
	);
}
