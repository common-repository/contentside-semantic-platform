import {SettingsClient} from "./admin/js/gutenberg/clients/settings-client";

/**
 * Entrypoint for all the js scripts enqueued in the admin
 */
import AdminSettings from './admin/js/settings/admin-settings';
import RelatedPostsMetaBox from './admin/js/meta-box/related-posts/meta-box';
import Toolbar from "./admin/js/gutenberg/components/toolbar";
import {CspPluginPanelController} from "./admin/js/gutenberg/components/sidebar";

SettingsClient.isApiKeyValid().then((isApiKeyValid) => {
    if (isApiKeyValid) {
        wp.domReady(() => {
            SettingsClient.isFeatureAllowed("LOOKALIKE").then((isFeatureAllowed) => {
                if (isFeatureAllowed) {
                    // Block editor
                    Toolbar.register();
                }
            });

            // SettingsClient.isFeatureAllowed("NER").then((isFeatureAllowed) => {
            //     if (isFeatureAllowed) {
                    // Sidebar
            CspPluginPanelController.register();
                // }
            // });
        });
    }
});

window.addEventListener('load', () => {
    AdminSettings.bindEvents();
    RelatedPostsMetaBox.bindEvents();
});
