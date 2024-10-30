import {useEntityProp} from '@wordpress/core-data';
import {useSelect} from '@wordpress/data';
import {useState} from '@wordpress/element';
import {useEffect} from "react";
import {SettingsClient} from "../../../clients/settings-client";
import {NamedEntitiesModal} from "./named-entities-modal";
import {__} from "@wordpress/i18n";
import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {store as editorStore} from '@wordpress/editor';
import {CapabilitiesService} from "../../../services/capabilities-service";

const EditNamedEntitiesSettingsPanel = (props) => {
    const postType = useSelect(
        (select) => select(editorStore).getCurrentPostType(),
        []
    );
    const currentPost = useSelect((select) => (select(editorStore).getCurrentPost()));
    const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
    const [settings, setSettings] = useState(undefined);
    const [isApiKeyValid, setIsApiKeyValid] = useState(false);
    const [isUserAllowed, setIsUserAllowed] = useState(false);

    useEffect(() => {
        SettingsClient.getSettings().then((settings) => setSettings(settings));
        SettingsClient.isApiKeyValid().then((isApiKeyValid) => {
            SettingsClient.isFeatureAllowed("NER").then((isFeatureAllowed) => {
                setIsApiKeyValid(isApiKeyValid && isFeatureAllowed)
            });
        });
        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.entities")
            .then((isAllowed) => {
                setIsUserAllowed(isAllowed);
            });
    }, []);

    return (
        <>
            {isUserAllowed === false || isApiKeyValid === false &&
                null
            }
            {isUserAllowed === true && isApiKeyValid === true &&
                <PluginDocumentSettingPanel title={__("Named entities", "csp-plugin")}>
                    <NamedEntitiesModal
                        currentPost={currentPost}
                        setMeta={setMeta}
                        meta={meta}
                        pluginSettings={settings}
                    />
                </PluginDocumentSettingPanel>
            }
        </>
    );
}

export default EditNamedEntitiesSettingsPanel;