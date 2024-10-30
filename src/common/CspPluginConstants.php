<?php

class CspPluginConstants {
	// Options and settings
	const CSP_PLUGIN_OPTIONS_KEY              = "csp-plugin_options";
	const CSP_PLUGIN_SETTINGS_API_KEY_KEY     = "csp-plugin_setting_api_key";
	const CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT = "csp_plugin_posts_already_sync_count";

	// API
	const CSP_PLUGIN_API_RELATED_POSTS_FEATURE_NAME = "LOOKALIKE";
	const CSP_PLUGIN_API_CATEGORIZE_FEATURE_NAME    = "CATEGORIZE";
	const CSP_PLUGIN_API_NER_FEATURE_NAME           = "NER";
	const CSP_PLUGIN_API_ALLOWED_FEATURES_KEY       = "csp-plugin_allowed_features";
	const CSP_PLUGIN_SETTINGS_API_KEY_VALID_KEY     = "is_api_key_valid";


	// Related posts
	const CSP_PLUGIN_RELATED_POSTS_META_BOX_ID                        = "csp-plugin-related-posts-meta-box";
	const CSP_PLUGIN_RELATED_POSTS_META_KEY                           = "csp-plugin-related-posts";
	const CSP_PLUGIN_RELATED_POSTS_OPTIONS_KEY                        = "csp-plugin_related_posts_options";
	const CSP_PLUGIN_SETTINGS_RELATED_POSTS_NB_RESULTS_SHORT_KEY      = "related_posts_nb_results";
	const CSP_PLUGIN_SETTINGS_RELATED_POSTS_NB_RESULTS_KEY            = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_RELATED_POSTS_NB_RESULTS_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_RELATED_POSTS_SYNC_START_DATE_SHORT_KEY = "related_posts_sync_starting_date";
	const CSP_PLUGIN_SETTINGS_RELATED_POSTS_SYNC_START_DATE_KEY       = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_RELATED_POSTS_SYNC_START_DATE_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY  = "related_posts_sync_last_sync_date";
	const CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_KEY        = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY;

	// Categorize posts
	const CSP_PLUGIN_CATEGORIES_META_KEY                       = "csp-plugin-categories";
	const CSP_PLUGIN_CATEGORIZE_OPTIONS_KEY                    = "csp-plugin_categorize_options";
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_SHORT_KEY  = "categorize_nb_results";
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_KEY        = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_CATEGORIZE_NB_RESULTS_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_SHORT_KEY   = "categorize_threshold";
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_KEY         = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_CATEGORIZE_THRESHOLD_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_SHORT_KEY    = "categorize_taxonomy";
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_KEY          = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_CATEGORIZE_TAXONOMY_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_SHORT_KEY = "categorize_wp_taxonomy";
	const CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_KEY       = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_CATEGORIZE_WP_TAXONOMY_SHORT_KEY;

	// Tagging posts
	const CSP_PLUGIN_TAGGING_OPTIONS_KEY                    = "csp-plugin_tagging_options";
	const CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_SHORT_KEY  = "tagging_nb_results";
	const CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_KEY        = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_TAGGING_NB_RESULTS_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_SHORT_KEY   = "tagging_threshold";
	const CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_KEY         = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_TAGGING_THRESHOLD_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_SHORT_KEY    = "tagging_taxonomy";
	const CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_KEY          = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_TAGGING_TAXONOMY_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_SHORT_KEY = "tagging_wp_taxonomy";
	const CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_KEY       = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_TAGGING_WP_TAXONOMY_SHORT_KEY;

	// NER in posts
	const CSP_PLUGIN_NER_POST_META_KEY                                    = "named_entities";
	const CSP_PLUGIN_NER_OPTIONS_KEY                                      = "csp-plugin_ner_options";
	const CSP_PLUGIN_SETTINGS_NER_THRESHOLD_SHORT_KEY                     = "ner_threshold";
	const CSP_PLUGIN_SETTINGS_NER_THRESHOLD_KEY                           = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_NER_THRESHOLD_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_NER_DICTIONARY_SHORT_KEY                    = "ner_dictionary";
	const CSP_PLUGIN_SETTINGS_NER_DICTIONARY_KEY                          = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_NER_DICTIONARY_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_SHORT_KEY                    = "ner_url_format";
	const CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_KEY                          = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_NER_URL_FORMAT_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_AS_TAG_SHORT_KEY               = "ner_only_add_as_tag";
	const CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_AS_TAG_KEY                     = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_AS_TAG_SHORT_KEY;
	const CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_SHORT_KEY = "ner_only_add_the_first_occurrence";
	const CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_KEY       = "csp-plugin_setting_" . self::CSP_PLUGIN_SETTINGS_NER_ONLY_ADD_THE_FIRST_OCCURRENCE_SHORT_KEY;
}