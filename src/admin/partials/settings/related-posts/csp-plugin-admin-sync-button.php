<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly // Silence is golden

/**
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */
$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
$now     = new DateTime();

$nbPostsSynced = 0;
if ( isset( $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ] ) ) {
	$nbPostsSynced = $options[ CspPluginConstants::CSP_PLUGIN_RELATED_POSTS_SYNC_COUNT ];
}
$nbPosts = intval( wp_count_posts()->publish );

$isSyncPossible = false;
$isSyncOnGoing  = false;

$syncLastDate = new DateTime();
if ( isset( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY ] )
     && ! empty( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY ] )
) {
	try {
		$syncLastDate = new DateTime( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_RELATED_POSTS_LAST_SYNC_DATE_SHORT_KEY ] );

		// We re-estimate the end date based on the last sync date
		$estimatedSyncEndDate = clone $syncLastDate;
		$estimatedSyncEndDate->add( DateInterval::createFromDateString( ( $nbPosts * 2 ) . ' seconds' ) );
		$isSyncOnGoing = ( $nbPosts > $nbPostsSynced ) && ( $now < $estimatedSyncEndDate );
	} catch ( Exception $e ) {
		$isSyncPossible = true;
	}
} else {
	$isSyncPossible = true;
}

$onGoingStyle = ''
?>

<div style="width: 100%;">
	<?php if ( $isSyncPossible ) { ?>
        <button
                id="csp-plugin-start-sync-button"
                class="button button-primary csp-plugin-start-transaction-button"
                data-action="start_synchronization"
                data-nonce="<?php echo wp_create_nonce( 'csp-plugin_start_synchronization' ) ?>"
                data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>"
                style="margin-right: 2em;"
        >
			<?php esc_attr_e( 'Synchronize posts', 'csp-plugin' ) ?>
        </button>

        <span id="csp-plugin-sync-info"
              style="line-height: 30px;"><?php esc_attr_e( 'This action might take a long time to complete',
		                                                   'csp-plugin' ); ?></span>
	<?php } elseif ( $isSyncOnGoing ) { ?>
        <button
                id="csp-plugin-start-sync-button"
                class="button button-primary csp-plugin-start-transaction-button"
                style="margin-right: 2em;"
                disabled
        >
			<?php esc_attr_e( 'Loading...', 'csp-plugin' ) ?>
        </button>
	<?php } else { ?>
        <button
                id="csp-plugin-start-sync-button"
                class="button button-primary csp-plugin-start-transaction-button"
                style="margin-right: 2em; background-color: #4CAF50 !important; color: white !important"
                disabled
        >
			<?php esc_attr_e( 'Done', 'csp-plugin' ) ?>
        </button>
	<?php } ?>
</div>