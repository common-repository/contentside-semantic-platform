<?php

class CspPluginUtils {

	/**
	 * @return boolean
	 * @throws Exception
	 * @since 2.0.0
	 * @access public
	 */
	public static function isApiKeyValid() {
		$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] ) ) {
			return false;
		}

		$apiKey = $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ];
		if ( empty( $apiKey ) ) {
			return false;
		}

		$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] ) ) {
			return false;
		}

		return boolval( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY ] );
	}

	/**
	 * @param $featureName
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	public static function isFeatureAllowed( $featureName ) {
		$allowedFeatures = get_option( CspPluginConstants::CSP_PLUGIN_API_ALLOWED_FEATURES_KEY );

		if ( ! is_array( $allowedFeatures ) ) {
			return false;
		}

		return in_array( $featureName, $allowedFeatures );
	}
}