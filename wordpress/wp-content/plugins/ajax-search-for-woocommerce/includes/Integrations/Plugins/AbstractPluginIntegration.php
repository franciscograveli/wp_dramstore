<?php

namespace DgoraWcas\Integrations\Plugins;

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractPluginIntegration {
	protected const VERSION_CONST = '';
	protected const MIN_VERSION   = '';
	protected const LABEL         = '';

	public static function isActive(): bool {
		$version = static::pluginVersion();

		if ( $version === '' ) {
			return false;
		}

		$minVersion = static::MIN_VERSION;

		return $minVersion === '' ? true : version_compare( $version, $minVersion, '>=' );
	}

	public static function shouldInitLate() : bool {
		return false;
	}

	public static function pluginVersion(): string {
		$const = static::VERSION_CONST;
		if ( $const !== '' && defined( $const ) ) {
			$version = constant( $const );
			if ( is_string( $version ) || is_numeric( $version ) ) {
				return (string) $version;
			}
		}

		return '';
	}

	public static function label(): string {
		return static::LABEL;
	}

	public static function registerTroubleshootingHooks(): void {
		$minVersion     = static::MIN_VERSION;
		$currentVersion = static::pluginVersion();

		if ( $minVersion === '' || $currentVersion === '' || version_compare( $currentVersion, $minVersion, '>=' ) ) {
			return;
		}

		add_filter(
			'dgwt/wcas/troubleshooting/unsupported_plugin_versions',
			function ( $list ) use ( $currentVersion, $minVersion ) {
				$list[] = [
					'name'           => static::label(),
					'currentVersion' => $currentVersion,
					'minimumVersion' => $minVersion,
				];

				return $list;
			}
		);
	}

	abstract public function init(): void;
}
