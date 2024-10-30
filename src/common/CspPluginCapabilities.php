<?php

/**
 * Class CspPluginCapabilities
 * This class defines all the capabilities used by the plugin and offers the possibility to use them in a consistent way.
 * @since 1.3.0
 */
class CspPluginCapabilities {
	const DEFAULT_ROLES                                     = [ "administrator", "editor", "author", "contributor" ];
	const CSP_PLUGIN_CAPABILITY_CATEGORIZE                  = "csp_plugin.semantic_platform.category";
	const CSP_PLUGIN_CAPABILITY_NER_ENTITIES                = "csp_plugin.semantic_platform.entities";
	const CSP_PLUGIN_CAPABILITY_NER_ADD_SUGGESTED_ENTITIES  = "csp_plugin.semantic_platform.entities.add_suggested";
	const CSP_PLUGIN_CAPABILITY_TAGGING                     = "csp_plugin.semantic_platform.tags";
	const CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER          = "csp_plugin.semantic_platform.similar_article.discover";
	const CSP_PLUGIN_CAPABILITY_LOOKALIKE_INSERT            = "csp_plugin.semantic_platform.similar_article.insert";

	/**
	 * Returns all the capabilities used by the plugin
	 * @return array
	 * @since    1.3.0
	 */
	public static function get_capabilities() {
		return [
			self::CSP_PLUGIN_CAPABILITY_CATEGORIZE,
			self::CSP_PLUGIN_CAPABILITY_NER_ENTITIES,
			self::CSP_PLUGIN_CAPABILITY_NER_ADD_SUGGESTED_ENTITIES,
			self::CSP_PLUGIN_CAPABILITY_TAGGING,
			self::CSP_PLUGIN_CAPABILITY_LOOKALIKE_DISCOVER,
			self::CSP_PLUGIN_CAPABILITY_LOOKALIKE_INSERT,
		];
	}

	/**
	 * Adds the capabilities to the roles
	 * By default all roles get all capabilities (except for the subscriber role)
	 * @return void
	 * @since    1.3.0
	 */
	public static function add_capabilities_to_default_roles() {
		foreach ( self::DEFAULT_ROLES as $roleName ) {
			$role = get_role( $roleName );
			if ( ! $role ) {
				continue;
			}

			foreach ( self::get_capabilities() as $capability ) {
				$role->add_cap( $capability );
			}
		}
	}

	/**
	 * Removes the capabilities from all the editable roles
	 * Used during the deactivation of the plugin
	 * @return void
	 * @since    1.3.0
	 */
	public static function remove_capabilities_from_all_editable_roles() {
		foreach ( get_editable_roles() as $roleName => $roleInfos ) {
			$role = get_role( $roleName );
			if ( ! $role ) {
				continue;
			}

			foreach ( self::get_capabilities() as $capability ) {
				$role->remove_cap( $capability );
			}
		}

	}

	/**
	 * Gets the list of plugins capabilities for the current user
	 * @return bool[]
	 * @since   1.3.0
	 */
	public static function get_current_user_csp_capabilities() {
		$current_user = wp_get_current_user();

		return array_filter( $current_user->allcaps, function ( $capability ) {
			return strpos( $capability, 'csp_' ) === 0;
		},                   ARRAY_FILTER_USE_KEY );
	}
}