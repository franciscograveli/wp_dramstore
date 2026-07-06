<?php

namespace DgoraWcas;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Multilingual {

	public static $currentCurrency = '';
	public static $langs           = null;

	/**
	 * Check if the website is multilingual
	 *
	 * @return bool
	 */
	public static function isMultilingual() {

		$isMultilingual = false;

		if ( defined( 'DGWT_WCAS_DISABLE_MULTILINGUAL' ) && DGWT_WCAS_DISABLE_MULTILINGUAL ) {
			return false;
		}

		if ( count( self::getLanguages() ) > 0 && self::getProvider() !== 'not set' ) {
			$isMultilingual = true;
		}

		return $isMultilingual;
	}

	/**
	 * Check if WPMl is active
	 *
	 * @return bool
	 */
	public static function isWPML() {
		return class_exists( 'SitePress' );
	}

	/**
	 * Check if Polylang is active
	 *
	 * @return bool
	 */
	public static function isPolylang() {
		return did_action( 'pll_init' );
	}

	/**
	 * Get Provider
	 *
	 * @return string
	 */
	public static function getProvider() {
		$provider = 'not set';

		if ( self::isWPML() ) {
			$provider = 'WPML';
		}

		if ( self::isPolylang() ) {
			$provider = 'Polylang';
		}

		$provider = apply_filters( 'dgwt/wcas/multilingual/provider', $provider );

		return $provider;
	}

	/**
	 * Check if language code has one of the following format:
	 * aa, aaa, aa-aa
	 *
	 * @param $lang
	 *
	 * @return bool
	 */
	public static function isLangCode( $lang ) {
		return ! empty( $lang ) && is_string( $lang ) && (bool) preg_match( '/^([a-zA-Z]{2,15})$|^([a-zA-Z]{2,15}[-_][a-zA-Z]{2,15})$/', $lang );
	}

	/**
	 * Get default language
	 *
	 * @return string
	 */
	public static function getDefaultLanguage() {
		$defaultLang = 'en';

		if ( self::isWPML() ) {
			$defaultLang = apply_filters( 'wpml_default_language', null );
		}

		if ( self::isPolylang() ) {
			$defaultLang = pll_default_language( 'slug' );
		}

		if ( empty( $defaultLang ) ) {
			$locale      = get_locale();
			$defaultLang = substr( $locale, 0, 2 );
		}

		return apply_filters( 'dgwt/wcas/multilingual/default-language', $defaultLang );
	}

	/**
	 * Current language
	 *
	 * @return string
	 */
	public static function getCurrentLanguage() {
		$currentLang = self::getDefaultLanguage();

		if ( self::isWPML() ) {
			$currentLang = apply_filters( 'wpml_current_language', null );
		}

		if ( self::isPolylang() ) {
			$lang = pll_current_language( 'slug' );

			if ( $lang ) {
				$currentLang = $lang;
			} else {
				$currentLang = pll_default_language( 'slug' );
			}
		}
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $currentLang ) && ! empty( $_GET['lang'] ) && self::isLangCode( $_GET['lang'] ) ) {
			$currentLang = $_GET['lang'];
		}
		//phpcs:enable

		return apply_filters( 'dgwt/wcas/multilingual/current-language', $currentLang );
	}

	/**
	 * Get Language of post or product
	 *
	 * @param int $postID
	 *
	 * @return string
	 */
	public static function getPostLang( $postID, $postType = 'product' ) {
		$lang = self::getDefaultLanguage();

		if ( self::isWPML() ) {
			global $wpdb;

			$postType = 'post_' . $postType;
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
			$tranlationsTable = $wpdb->prefix . 'icl_translations';
			$sql              = $wpdb->prepare(
				"SELECT language_code
                                          FROM $tranlationsTable
                                          WHERE element_type=%s
                                          AND element_id=%d",
				sanitize_key( $postType ),
				$postID
			);

			$result = $wpdb->get_var( $sql );
			// phpcs:enable

			if ( self::isLangCode( $result ) ) {
				$lang = $result;
			}
		}

		if ( self::isPolylang() ) {
			$lang = pll_get_post_language( $postID, 'slug' );
		}

		$lang = apply_filters( 'dgwt/wcas/multilingual/post-language', $lang, $postID, $postType );

		return $lang;
	}

	/**
	 * Get term lang
	 *
	 * @param int $term ID
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	public static function getTermLang( $termID, $taxonomy ) {
		$lang = self::getDefaultLanguage();

		if ( self::isWPML() ) {
			global $wpdb;

			$elementType      = 'tax_' . sanitize_key( $taxonomy );
			$tranlationsTable = $wpdb->prefix . 'icl_translations';

			$term = \WP_Term::get_instance( $termID, $taxonomy );
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
			if ( is_a( $term, 'WP_Term' ) ) {
				$sql = $wpdb->prepare(
					"SELECT language_code
                                          FROM $tranlationsTable
                                          WHERE element_type = %s
                                          AND element_id=%d",
					$elementType,
					$term->term_taxonomy_id
				);

				$result = $wpdb->get_var( $sql );
				// phpcs:enable

				if ( self::isLangCode( $result ) ) {
					$lang = $result;
				}
			}
		}

		if ( self::isPolylang() ) {
			$lang = pll_get_term_language( $termID, 'slug' );
		}

		// TranslatePress/qTranslate-XT has no language relationship with the post, so we always return the default
		$lang = apply_filters( 'dgwt/wcas/multilingual/term-language', $lang, $termID, $taxonomy );

		return $lang;
	}

	/**
	 * Get permalink
	 *
	 * @param string $postID
	 * @param string $url
	 * @param string $lang
	 *
	 * @return string
	 */
	public static function getPermalink( $postID, $url = '', $lang = '' ) {
		$permalink = $url;

		if ( self::isWPML() && self::getDefaultLanguage() !== $lang ) {
			/**
			 *  1 if the option is *Different languages in directories*
			 *  2 if the option is *A different domain per language*
			 *  3 if the option is *Language name added as a parameter*.
			 */
			$urlType = apply_filters( 'wpml_setting', 0, 'language_negotiation_type' );

			if ( $urlType == 3 ) {
				$permalink = apply_filters( 'wpml_permalink', $url, $lang );
			} else {
				$permalink = apply_filters( 'wpml_permalink', $url, $lang, true );
			}
		}

		$permalink = apply_filters( 'dgwt/wcas/multilingual/post-permalink', $permalink, $lang, $postID );

		return $permalink;
	}

	/**
	 * Active languages
	 *
	 * @param bool $includeInvalid Also return invalid languages
	 *
	 * @return array
	 */
	public static function getLanguages( $includeInvalid = false ) {
		$includeHidden = apply_filters( 'dgwt/wcas/multilingual/languages/include-hidden', false );

		if ( self::$langs !== null && ! $includeInvalid && ! $includeHidden ) {
			return self::$langs;
		}

		$langs = [];

		if ( self::isWPML() ) {
			$wpmlLangs = apply_filters( 'wpml_active_languages', null, [ 'skip_missing' => 0 ] );

			if ( is_array( $wpmlLangs ) ) {
				foreach ( $wpmlLangs as $langCode => $details ) {
					if ( self::isLangCode( $langCode ) || $includeInvalid ) {
						$langs[] = $langCode;
					}
				}
			}

			if ( ! $includeHidden ) {
				$hiddenLangs = apply_filters( 'wpml_setting', [], 'hidden_languages' );
				if ( ! empty( $hiddenLangs ) && is_array( $hiddenLangs ) ) {
					foreach ( $hiddenLangs as $hiddenLang ) {
						if ( ! self::isLangCode( $hiddenLang ) && $includeInvalid ) {
							continue;
						}
						$langs = array_diff( $langs, [ $hiddenLang ] );
					}
				}
			}
		}

		if ( self::isPolylang() ) {
			$langs = pll_languages_list(
				[
					'hide_empty' => false,
					'fields'     => '',
				]
			);

			// Filter not-active languages
			$langs = array_filter(
				$langs,
				function ( $lang ) {
					// By default, 'active' prop isn't available; It is set the first time the administrator deactivates the language
					if ( isset( $lang->active ) && ! $lang->active ) {
						return false;
					}

					return true;
				}
			);

			$langs = wp_list_pluck( $langs, 'slug' );
		}

		if ( empty( $langs ) ) {
			$langs[] = self::getDefaultLanguage();
		}

		$langs = apply_filters( 'dgwt/wcas/multilingual/languages', $langs, $includeInvalid, $includeHidden );

		if ( ! $includeInvalid && ! $includeHidden ) {
			self::$langs = $langs;
		}

		return $langs;
	}

	/**
	 * Get language details by language code
	 *
	 * @param string $lang
	 * @param string $field | name | locale |
	 *
	 * @return string
	 */
	public static function getLanguageField( $lang, $field ) {
		$value = $lang;

		if ( self::isWPML() ) {
			global $sitepress;
			$details = $sitepress->get_language_details( $lang );

			if ( $field === 'name' && ! empty( $details['display_name'] ) ) {
				$value = $details['display_name'];
			}

			if ( $field === 'locale' && ! empty( $details['default_locale'] ) ) {
				$value = $details['default_locale'];
			}
		}

		if ( self::isPolylang() ) {
			$langs = pll_languages_list(
				[
					'hide_empty' => false,
					'fields'     => '',
				]
			);

			if ( ! empty( $langs ) && is_array( $langs ) ) {
				foreach ( $langs as $object ) {
					if ( ! empty( $object->slug ) && $object->slug === $lang ) {

						if ( $field === 'name' ) {
							$value = $object->name;
						}

						if ( $field === 'locale' ) {
							$value = $object->locale;
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Get all terms in one taxonomy for all languages
	 *
	 * @param string $taxonomy
	 *
	 * @return array of WP_Term objects
	 */
	public static function getTermsInAllLangs( $taxonomy ) {
		$terms = [];

		if ( self::isWPML() ) {
			$currentLang = self::getCurrentLanguage();
			$usedIds     = [];

			foreach ( self::getLanguages() as $lang ) {
				do_action( 'wpml_switch_language', $lang );
				$args        = [
					'taxonomy'         => $taxonomy,
					'hide_empty'       => true,
					'suppress_filters' => false,
				];
				$termsInLang = get_terms( apply_filters( 'dgwt/wcas/search/' . $taxonomy . '/args', $args ) );

				if ( ! empty( $termsInLang ) && is_array( $termsInLang ) ) {
					foreach ( $termsInLang as $termInLang ) {
						if ( ! in_array( $termInLang->term_id, $usedIds ) ) {
							$terms[]   = $termInLang;
							$usedIds[] = $termInLang->term_id;
						}
					}
				}
			}

			do_action( 'wpml_switch_language', $currentLang );
		}

		if ( self::isPolylang() ) {

			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
					'lang'       => '', // query terms in all languages
				]
			);

		}

		$terms = apply_filters( 'dgwt/wcas/multilingual/terms-in-all-languages', $terms, $taxonomy );

		return $terms;
	}

	/**
	 * Get terms in specific language
	 *
	 * @param array $args
	 * @param string $lang
	 *
	 * @return \WP_Term[]
	 */
	public static function getTermsInLang( $args = [], $lang = '' ) {
		$terms = [];

		if ( empty( $lang ) ) {
			$lang = self::getDefaultLanguage();
		}

		if ( self::isWPML() ) {
			$currentLang = self::getCurrentLanguage();
			$usedIds     = [];

			do_action( 'wpml_switch_language', $lang );
			$args        = wp_parse_args(
				$args,
				[
					'taxonomy'         => '',
					'hide_empty'       => true,
					'suppress_filters' => false,
				]
			);
			$termsInLang = get_terms( apply_filters( 'dgwt/wcas/search/' . $args['taxonomy'] . '/args', $args ) );

			if ( ! empty( $termsInLang ) && is_array( $termsInLang ) ) {
				foreach ( $termsInLang as $termInLang ) {

					if ( ! in_array( $termInLang->term_id, $usedIds ) ) {
						$terms[]   = $termInLang;
						$usedIds[] = $termInLang->term_id;
					}
				}
			}

			do_action( 'wpml_switch_language', $currentLang );
		}

		if ( self::isPolylang() ) {
			$args = wp_parse_args(
				$args,
				[
					'taxonomy'   => '',
					'hide_empty' => true,
					'lang'       => $lang,
				]
			);

			$terms = get_terms( $args );
		}

		$terms = apply_filters( 'dgwt/wcas/multilingual/terms-in-language', $terms, $args, $lang );

		return $terms;
	}

	public static function searchTerms( $taxonomy, $query, $lang = '' ) {
		$terms = [];

		if ( empty( $lang ) ) {
			$lang = self::getDefaultLanguage();
		}

		$args  = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'search'     => $query,
		];
		$terms = get_terms( $args );
	}

	/**
	 * Get term in specific language
	 *
	 * @param int $termID
	 * @param string $taxonomy
	 * @param string $lang
	 *
	 * @return object WP_Term
	 */
	public static function getTerm( $termID, $taxonomy, $lang ) {
		$term = null;

		if ( self::isWPML() ) {
			$currentLang = self::getCurrentLanguage();
			do_action( 'wpml_switch_language', $lang );

			$term = get_term( $termID, $taxonomy );

			do_action( 'wpml_switch_language', $currentLang );
		}

		if ( self::isPolylang() ) {

			$termID = pll_get_term( $termID, $lang );

			if ( $termID ) {
				$term = get_term( $termID, $taxonomy );
			}
		}

		$term = apply_filters( 'dgwt/wcas/multilingual/term', $term, $termID, $taxonomy, $lang );

		return $term;
	}

	/**
	 * Check if multicurrency module is enabled
	 *
	 * @return bool
	 */
	public static function isMultiCurrency() {

		$multiCurrency = false;

		if ( self::isWPML() && function_exists( 'wcml_is_multi_currency_on' ) && wcml_is_multi_currency_on() ) {
			$multiCurrency = true;
		}

		return $multiCurrency;
	}

	/**
	 * Get currency code assigned to language
	 *
	 * @param string $lang
	 *
	 * @return string
	 */
	public static function getCurrencyForLang( $lang ) {
		$currencyCode = '';

		if ( self::isWPML() ) {
			global $woocommerce_wpml;
			if ( ! empty( $woocommerce_wpml ) && is_object( $woocommerce_wpml ) && ! empty( $lang ) ) {

				if ( ! empty( $woocommerce_wpml->settings['default_currencies'][ $lang ] ) ) {
					$currencyCode = $woocommerce_wpml->settings['default_currencies'][ $lang ];
				}
			}
		}

		return $currencyCode;
	}

	/**
	 * Set currenct currency
	 *
	 * @return void
	 */
	public static function setCurrentCurrency( $currency ) {
		self::$currentCurrency = $currency;
	}

	/**
	 * Get currenct currency
	 *
	 * @return string
	 */
	public static function getCurrentCurrency() {
		return self::$currentCurrency;
	}

	/**
	 * Switch language
	 *
	 * @param $lang
	 */
	public static function switchLanguage( $lang ) {
		if ( self::isWPML() && ! empty( $lang ) ) {
			do_action( 'wpml_switch_language', $lang );
		}

		/**
		 * Some plugins (e.g. Permalink Manager for WooCommerce) use the get_the_terms() function,
		 * which caches terms related to the product, and we need to clear this cache when changing the language.
		 */
		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( 'product_cat_relationships' );
		} else {
			wp_cache_flush();
		}

		do_action( 'dgwt/wcas/multilingual/switch-language', $lang );
	}

}
