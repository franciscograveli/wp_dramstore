=== FiboSearch - Ajax Search for WooCommerce  ===
Contributors: damian-gora, matczar
Tags: woocommerce search, ajax search, search by sku, product search, woocommerce
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.33.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The most popular WooCommerce product search plugin. Gives your users a well-designed advanced AJAX search bar with live search suggestions.

== Description ==

The most popular **WooCommerce product search plugin**. It gives your users a well-designed advanced AJAX search bar with live search suggestions.

By default, WooCommerce provides a very simple search solution, without live product search or even SKU search. FiboSearch (formerly Ajax Search for WooCommerce) provides advanced search with live suggestions.

Who doesn’t love instant, as-you-type suggestions? In 2025, customers expect smart product search. Baymard Institute’s latest UX research reveals that search autocomplete, auto-suggest, or an instant search feature **is now offered on 96% of major e-commerce sites**. It's a must-have feature for every online business that can’t afford to lose customers. Why? FiboSearch helps users save time and makes shopping easier. As a result, Fibo really boosts sales.

= Features =
&#9989; **Search by product title, long and short description**
&#9989; **Search by SKU**
&#9989; Show **product image** in live search results
&#9989; Show **product price** in live search results
&#9989; Show **product description** in live search results
&#9989; Show **SKU** in live search results
&#9989; **Mobile first** – special mobile search mode for better UX
&#9989; **Details panels** with extended information – **“add to cart” button** with a **quantity field** and **extended product** data displayed on hovering over the live suggestion
&#9989; **Easy implementation** in your theme - embed the plugin using a **shortcode**, as a **menu item** or as a **widget**
&#9989; **Terms search** – search for product categories and tags
&#9989; **Search history** – the current search history is presented when the user clicked/taped on the search bar, but hasn't yet typed the query.
&#9989; **Limit** displayed suggestions – the number is customizable
&#9989; **The minimum number of characters** required to display suggestions – the number is customizable
&#9989; **Better ordering** – a smart algorithm ensures that the displayed results are as accurate as possible
&#9989; **Support for WooCommerce search results page** - after typing enter, users get the same results as in FiboSearch bar
&#9989; **Grouping instant search results by type** – displaying e.g. first matching categories, then matching products
&#9989; **Google Analytics** support
&#9989; Multilingual support including **WPML**, **Polylang** and **qTranslate-XT**
&#9989; **Personalization** of search bar and autocomplete suggestions - labels, colors, preloader, image and more

= Try the PRO version =
FiboSearch also comes in a Pro version, with a modern, inverted index-based search engine. FiboSearch Pro works up to **10× faster** than the Free version or other popular search solutions for WooCommerce.

[Upgrade to PRO and boost your sales!](https://fibosearch.com/pricing/?utm_source=readme&utm_medium=referral&utm_content=pricing&utm_campaign=asfw)

= PRO features =

&#9989; **Ultra-fast search engine** based on the inverted index – works very fast, even with 100,000+ products
&#9989; **Fuzzy search** – works even with minor typos
&#9989; **Search in custom fields** with dedicated support for ACF
&#9989; **Search in attributes**
&#9989; **Search in categories**. Supports category thumbnails.
&#9989; **Search in tags**
&#9989; **Search in brands** (We support WooCommerce Brands, Perfect Brands for WooCommerce, Brands for WooCommerce, YITH WooCommerce Brands). Supports brand thumbnails.
&#9989; **Search by variation product SKU** – also shows variable products in live search after typing in the exact matching SKU
&#9989; **Search for posts** – also shows matching posts in live search
&#9989; **Search for pages** – also shows matching posts in live search
&#9989; **Synonyms**
&#9989; **Conditional exclusion of products**
&#9989; **TranslatePress** compatible
&#9989; Professional and fast **help with embedding** or replacing the search bar in your theme
&#9989; and more...
&#9989; SEE ALL PRO [FEATURES](https://fibosearch.com/pro-vs-free/?utm_source=readme&utm_medium=referral&utm_content=features&utm_campaign=asfw)!

= Showcase =
See how it works for others: [Showcase](https://fibosearch.com/showcase/?utm_source=readme&utm_medium=referral&utm_campaign=asfw&utm_content=showcase&utm_gen=utmdc).

= Feedback =
Any suggestions or comments are welcome. Feel free to contact us via the [contact form](https://fibosearch.com/contact/?utm_source=readme&utm_medium=referral&utm_campaign=asfw&utm_content=contact&utm_gen=utmdc).

== Installation ==

1. Install the plugin from within the Dashboard or upload the directory `ajax-search-for-woocommerce` and all its contents to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to `WooCommerce → FiboSearch` and set your preferences.
4. Embed the search bar in your theme.

== Frequently Asked Questions ==

= How do I embed the search bar in my theme? =
There are many easy ways to display the FiboSearch bar in your theme:

– **Replacing the existing search bar with one click** - it is possible for dozens of popular themes
– **As a menu item** - in your WordPress admin panel, go to `Appearance → Menu` and add `FiboSearch bar` as a menu item
– **Using a shortcode**

`[fibosearch]`

– **As a widget** - in your WordPress admin panel, go to `Appearance → Widgets` and choose `FiboSearch`
– **As a block** - [learn how to use blocks](https://fibosearch.com/documentation/get-started/how-to-add-fibosearch-to-your-website/#add-fibosearch-with-the-dedicated-fibosearch-block) and FiboSearch together
– **Using PHP**

`<?php echo do_shortcode('[fibosearch]'); ?>`

– **We will do it for you!** - we offer free search bar implementation for Pro users. Become one now!

Or insert this function inside a PHP file (often, it is used to insert a form inside page template files):

= How do I replace the existing search bar in my theme with FiboSearch? =
We have prepared a one-click replacement of the search bar for the following themes:

*  Storefront
*  Divi
*  Flatsome
*  OceanWP
*  Astra
*  Avada
*  Sailent
*  and 46 more... See a complete list of integrated themes on [our documentation](https://fibosearch.com/documentation/themes-integrations/?utm_source=readme&utm_medium=referral&utm_campaign=asfw&utm_content=theme-integrations).


If you want to replace your search bar in another theme, please [contact our support team](https://fibosearch.com/contact/?utm_source=readme&utm_medium=referral&utm_campaign=asfw&utm_content=contact&utm_gen=utmdc).
We will assist with replacing the search bar in your theme for free after you upgrade to the Pro version.

= Can I add the search bar as a WordPress menu item? =
**Yes, you can!** Go to `Appearance → Menu`. You will see a new menu item called “FiboSearch”. Select it and click “Add to menu”. Done!

= How can I ask a question? =
You can submit a ticket on the plugin [website](https://fibosearch.com/contact/?utm_source=readme&utm_medium=referral&utm_campaign=asfw&utm_content=contact&utm_gen=utmdc) and the support team will get in touch with you shortly. We also answer questions on the [WordPress Support Forum](https://wordpress.org/support/plugin/ajax-search-for-woocommerce/).

= Do you offer customization support? =
Depending on the theme you use, sometimes the search bar requires minor improvements in appearance. We guarantee fast CSS corrections for all Pro plugin users, but we also help Free plugin users.

= Where can I find plugin settings? =
In your WordPress dashboard, go to `WooCommerce → FiboSearch`. The FiboSearch settings page is a submenu of the WooCommerce menu.

= Who is the Pro plugin version for? =
The Pro plugin version is for all online sellers looking to **increase sales** by providing an ultra-fast smart search engine to their clients.

The main difference between the Pro and Free versions is search speed and search scope. The Pro version has a new fast smart search engine. For some online stores that offer a lot of products for sale, search speed can be increased **up to 10×**, providing a whole new experience to end users.

All in all, the Pro version is dedicated to all WooCommerce shops where autocomplete suggestions work too slowly.

You can read more and compare Pro and Free features here: [Full comparison - Pro vs Free](https://fibosearch.com/pro-vs-free/).

== Screenshots ==

1. Search suggestions with a details panel
2. Search suggestions
3. Search suggestions with a details panel
4. Search suggestions (Pirx search style)
5. Search suggestions (Pirx compact search style)
6. Sample settings page (Starting tab)
7. Sample settings page (Search bar tab)
8. Sample settings page (Autocomplete tab)
9. Sample settings page (Search Analytics tab)

== Changelog ==

= 1.33.0, April 27, 2026 =
* ADDED: JetSmartFilters — support for custom queries
* ADDED: [FiboSearch 2.0 early access](https://fibosearch.com/fibosearch-2-0-is-on-the-way/) teaser in admin settings
* ADDED: Added link on the settings page to our second plugin, [FiboFilters](https://fibofilters.com/).
* FIXED: Prevent unwanted redirection for single product variations
* FIXED: Cached results were missing dynamic pricing
* FIXED: Undefined "items" key warning in DetailsBox AJAX handler
* FIXED: Header JS not loading in Mobex theme
* FIXED: Hide Categories and Products plugin integration compatibility with excluded categories methods
* UPDATED: Updating the `.pot` file
* UPDATED: Freemius SDK

= 1.32.2, January 05, 2026 =
* ADDED: Integration with the Themify Builder Pro plugin
* ADDED: Integration with the FOX WooCommerce Currency Switcher plugin
* FIXED: Improved visibility checks for products
* FIXED: Added nonce validation in an AJAX action
* FIXED: Improved detection of visibility plugin integrations

= 1.32.1, December 15, 2025 =
* FIXED: The Details panel inside the results wrapper didn’t expand to match the width of the search bar
* FIXED: Improved input handling in TheGem theme integration
* FIXED: The search didn’t work when the "Load JavaScript deferred" feature in WP Rocket was enabled
* FIXED: No search results appeared when using a multilingual setup and the exact SKU match function was triggered
* REFACTOR: Implemented a more consistent and unified PHP code style

= 1.32.0, November 17, 2025 =
* ADDED: Integration with the [Bacola theme](https://fibosearch.com/documentation/themes-integrations/bacola-theme/).
* ADDED: Debugger view for active plugin integrations
* ADDED: Helper function for retrieving plugin information
* ADDED: Option to make the search endpoint return only product IDs
* ADDED: Greek language files
* FIXED: Mobile search bar display issue in the **WoodMart theme**
* FIXED: Compatibility issue in the **Bricks theme**. The **“Enable AJAX add to cart”** option now works properly with the FiboSearch details panel
* FIXED: Search replacement issue on the **Flatsome theme** 404 page
* FIXED: Support for diacritic and accent characters in search and scoring calculation
* FIXED: Infinite loop on mobile devices caused by an **Impreza theme** event conflict
* FIXED: Improved CSS styles for the details panel in the **Bricks theme**
* FIXED: Limited the number of suggestions in preview settings to a maximum of 15 items
* FIXED: Removed unnecessary banners from the settings page
* FIXED: Replaced preloader with the correct search bar or icon in Elementor editor preview
* FIXED: HTML semantic issue in category suggestions caused by an unescaped `>` character in titles
* FIXED: Deprecated PHP `8.3` and `8.4` warnings
* FIXED: Protection against external CSS styles (e.g., targeting the `.woocommerce` class) overriding the details panel width
* TWEAK: Added a prefix to the Freemius placeholder class to prevent potential conflicts
* TWEAK: Moved Freemius SDK to the vendor directory
* TWEAK: Cleaned up code style
* TWEAK: Passed additional data to filters to improve customization of search results display
* REFACTOR: Moved indexer logs to a separate database table
* UPDATED: Updating the `.pot` file
* UPDATED: Freemius SDK

= 1.31.0, July 16, 2025 =
* ADDED: Info about **Elementor widget**
* ADDED: Documentation links to **Search in SKU** and **GUID**
* ADDED: Add filter to conditionally disable analytics recording (e.g. by IP, phrase, lang)
* ADDED: Add the ARIA label to the search icon for accessibility improvement
* ADDED: Option to skip plugin loading on search page by adding the `nofibosearch=1` parameter to the URL
* ADDED: Troubleshooting — tests for the minimum required versions of themes and plugins with available integrations
* ADDED: New filter to optionally show “**Products**” headline when only product suggestions are returned
* ADDED: Filter to always show “**See all products**” button in autocomplete
* ADDED: Filter to disable inline styles from the `Personalization` class
* ADDED: CSS adjustments in **Uncode theme** — centered the search bar in the menu
* FIXED: Managing initial search bar interactivity before the main script has loaded
* FIXED: Incorrect language code in speech recognition
* FIXED: Removed the `.woocommerce` class from the FiboSearch bar widget
* FIXED: Prevent JS error when `getFormWrapper` returns undefined or empty `jQuery` object
* FIXED: Deprecated: `Automatic conversion of false to array` (PHP >= 8.1)
* FIXED: Styling issues in the **Enfold theme**
* FIXED: There was an error in the SQL syntax that occurred during the plugin uninstallation process
* FIXED: Escape double quotes in `optionsRaw` to prevent `JSON.parse` errors
* TWEAK: Moved the JS 'fibosearch/show-details-panel' event to just before the preloader is hidden
* TWEAK: Code styling adjustments
* TWEAK: Hiding unwanted banner on settings page
* TWEAK: Optimized database queries
* REFACTOR: Removed **Listeo** theme integration
* UPDATED: Updating the `.pot` file
* UPDATED: Freemius SDK




= 1.30.0, January 27, 2025 =
* ADDED: “**Bricks theme**” integration – support for “Products” element on the search results page
* ADDED: “**Bricks theme**” integration – incorrect order of results on the search results page
* ADDED: Integration with the “**Discontinued Product Stock Status for WooCommerce**” plugin
* ADDED: The link to edit the FiboSearch bar in Customizer
* ADDED: The `x` button was added to the review request notice
* FIXED: Missing data in the “**Details Panel**” for product variations
* FIXED: Resolved an error when attempting to insert FiboSearch blocks in the block editor
* FIXED: Eliminated duplicate search results in autocomplete
* FIXED: Enabled searching for products whose variations are all out of stock but have the “**allow backorders**” status 
* FIXED: **WCAG** “Links do not have a discernible name”
* FIXED: **WPML** integration – no results when option "use translation if available or fallback to default language" is used
* FIXED: **Flatsome** theme integration – can't search in mobile overlay view when search is activated from the mobile menu
* FIXED: Incorrect display of the FiboSearch form when it is embedded in the shop page description
* FIXED: Mobile search in the menu does not work when the Divi Mobile plugin is active
* FIXED: Unnecessary header “Search results for...” when there are no results for post types other than products
* FIXED: Google PageSpeed Insights – avoid non-composited animations
* FIXED: Search icon padding when the search icon is a part of the main menu
* TWEAK: Theme integration is not loaded when the minimal version condition is not met
* TWEAK: Improved clearing of plugin data on uninstall
* TWEAK: Hiding unwanted banner on the settings page
* TWEAK: Remove the “**(beta)**” suffix from the “User search history” option
* REFACTOR: Remove the “**Grouped results**” option
* UPDATED: The `.pot` file
* UPDATED: Freemius SDK

= 1.29.0, October 29, 2024 =
* ADDED: Integration with the **Listeo theme**
* ADDED: New troubleshooting test – warning when “**Ajax Search Lite**” or “**Ajax Search Pro**” plugins are active
* ADDED: **WCAG improvements** – possibility to select and open a search icon using the keyboard
* ADDED: Search icon preloader. In some rare cases, a user can wait longer to display the search icon or search bar. Instead of an empty place, a placeholder is displayed
* FIXED: **Generatepress theme** integration – incorrect mobile overlay on the checkout in the Generatepress theme
* FIXED: A better way of calculating window width when the breakpoint is checked
* TWEAK: Hiding the **XStore** theme documentation button on the settings page
* UPDATED: The `.pot` file.
* UPDATED: Freemius SDK

= 1.28.1, June 28, 2024 =
* FIXED: Removed the phrase “**polyfill.io**” from the JavaScript code comment. FiboSearch has never linked to this compromised library, but some security tools recognize this JavaScript comment as a potential link to malware. All reports are **false positive**.
* FIXED: PHP deprecated notice in `\DgoraWcas\Helpers::keyIsValid`
* FIXED: Unnecessary display of warning in **Troubleshooting** when products are displayed using a widget from “**JetSmartFilters**”

= 1.28.0, May 27, 2024 =
* ADDED: New search bar style - a compact version of a “Pirx” style
* ADDED: Integration with the “Cartzilla theme”
* ADDED: Integration with the “Rey theme”
* ADDED: Placeholders to display custom content for new suggestion types like taxonomy, posts, pages, and product variation
* FIXED: “Woodmart theme” - unable to close the mobile menu after exiting the mobile search overlay
* FIXED: “Flatsome theme” - disappearing search bar on mobile phones
* FIXED: “Flatsome” - cannot change search bar style to Pirx
* FIXED: “Divi theme” - shortcodes are not rendered in the description in the Details Panel for pages
* FIXED: “XStore theme” - the integration doesn't replace all search forms
* FIXED: Force disabling transition effect on search input to avoid unexpected layout bouncing
* FIXED: Allowing to calculate score including one and two-character words
* FIXED: Better recognition of iOS
* FIXED: Uncode theme - the search icon doesn't show on the header
* TWEAK: Removed OPcache invalidation for the shortcode template file
* UPDATED: The .pot file
* UPDATED: Freemius SDK to v2.7.2


= 1.27.0, January 31, 2024 =
* ADDED: Integration with the “Betheme theme”
* ADDED: Highlight words in search results with Greek letters regardless of accent
* ADDED: Support for “Full-width Search” in the “XStore theme”
* FIXED: Multiple search containers on mobile in the “Astra theme” integration
* FIXED: No focus on search input for mobile devices in the “Astra theme” integration
* FIXED: Allow an HTML `&lt;i&gt;` tag in suggestion titles and headlines
* FIXED: Multilingual support is active even for one language
* FIXED: Overriding the search icon and form in the header was not working properly in the “WoodMart integration”
* FIXED: Missing filters from “Advanced AJAX Product Filters” plugin in the “Divi theme”
* FIXED: Replace `&#37` for more stable format `%%` in a `sprintf` function
* FIXED: An unwanted modal after closing the search overlay on mobile in the “Flatsome theme”
* FIXED: Missing colors after updating the “Bloksy theme” to 2.x
* FIXED: Incorrect calculation of a product's position in search results when it contains Greek letters
* FIXED: Incorrect term language detection in the WPML plugin. Replacing `term_id` with `term_taxonomy_id`
* FIXED: Unwanted ampersand entity in the product description of search results
* UPDATED: Requires PHP: 7.4
* UPDATED: The `.pot` file
* UPDATED: Polish translation
* UPDATED: Freemius SDK v2.6.2

= 1.26.1, October 19, 2023 =
* FIXED: Details panel - wrong HTML format of stock status element 

= 1.26.0, October 17, 2023 =
* ADDED: Integration with “Bricks builder”
* ADDED: Integration with “Brizy builder”
* FIXED: Calc score by comparing every word of the search phrase instead of all search phrase
* FIXED: WooCommerce Wholesale Prices plugin - invalid search results e.g. not hidden products and categories in the search results
* FIXED: Flatsome - when there are more search icons, only one is replaced
* FIXED: WPRocket - in some cases search fields/icons are not replaced immediately after the page load
* FIXED: Highlight matched words instead of the whole search phrase
* TWEAK: Allowing access to the `Personalization` class via `DGWT_WCAS()` function
* TWEAK: HUSKY - Products Filter Professional for WooCommerce plugin - disable the test in the Troubleshooting module for newer versions of this plugin
* REFACTOR: Replace `.click()` with `trigger('click')`, `.focus()` with `trigger('focus')`, `.blur()` with `trigger('blur')`
* REFACTOR: Replace `jQuery.fn.mouseup()` with `$(document).on('mouseup')`
* REFACTOR: Replace `jQuery.isFunction()` with `typeof fn === 'function'`
* UPDATED: Freemius SDK v2.5.12

= 1.25.0, July 06, 2023 =
* ADDED: Possibility to search for taxonomy terms regardless of accents in a phrase or term name
* ADDED: Added some new filters to change URLs of results in autocomplete and details panel
* FIXED: Warnings due to `open_basedir` restrictions
* FIXED: Integration with the Impreza theme - broken AJAX pagination for Grid element
* FIXED: Integration with the TheGem theme - missing search results when the “Layout Type” option is set to “Products Grid”
* FIXED: Integration with the Divi theme - mobile overlay not showing up
* FIXED: Stronger sanitization of the details panel output
* UPDATED: Freemius SDK v2.5.10
* UPDATED: Polish translation

[See changelog for all versions](https://fibosearch.com/changelog/).
