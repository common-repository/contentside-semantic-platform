import {useEntityProp} from '@wordpress/core-data';
import {useBlockProps} from '@wordpress/block-editor';
import {useSelect} from '@wordpress/data';
import {useState} from '@wordpress/element';
import {useEffect} from "react";
import {SettingsClient} from "../../services/settings-client";
import {NamedEntitiesModal} from "./named-entities-modal";

const EditNamedEntitiesBlock = (props) => {
    const blockProps = useBlockProps();
    const postType = useSelect(
        (select) => select('core/editor').getCurrentPostType(),
        []
    );
    const currentPost = useSelect((select) => (select('core/editor').getCurrentPost()));
    const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
    const [settings, setSettings] = useState(undefined);
    const [isApiKeyValid, setIsApiKeyValid] = useState(false);

    useEffect(() => {
        SettingsClient.getSettings().then((settings) => setSettings(settings));
        SettingsClient.isApiKeyValid().then((isApiKeyValid) => {
            SettingsClient.isFeatureAllowed("NER").then((isFeatureAllowed) => {
                setIsApiKeyValid(isApiKeyValid && isFeatureAllowed)
            });
        });

    }, []);

    return (
        <>
            {isApiKeyValid === false &&
                null
            }
            {isApiKeyValid === true &&
                <div {...blockProps}>
                    <NamedEntitiesModal
                        currentPost={currentPost}
                        setMeta={setMeta}
                        meta={meta}
                        pluginSettings={settings}
                    />
                </div>
            }
        </>
    );
}

export default EditNamedEntitiesBlock;