import {useSelect} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';
import {__} from '@wordpress/i18n';
import {CategoriesModal} from "./categories-modal";
import {useState} from '@wordpress/element';
import {useEffect} from "react";
import {SettingsClient} from "../../../clients/settings-client";
import {store as editorStore} from '@wordpress/editor';
import {CapabilitiesService} from "../../../services/capabilities-service";

const RecommendedCategoryButton = ({asTags = false, settings, ...props}) => {
    const [isPluginCorrectlyConfigured, setIsPluginCorrectlyConfigured] = useState(false);
    const {
        currentPost,
        taxonomy
    } = useSelect((select) => {
        const {getTaxonomy} = select(coreStore);
        const currentPost = select(editorStore).getCurrentPost()
        const taxonomy = getTaxonomy(props.slug)

        return {
            currentPost: currentPost,
            taxonomy: taxonomy
        }
    });

    const [isModalOpen, setModalOpen] = useState(false);

    useEffect(() => {
        if (settings !== undefined) {
            setIsPluginCorrectlyConfigured(
                settings.is_api_key_valid === "true"
                && settings.allowed_features.includes("CATEGORIZE")
                && settings[asTags ? "tagging_taxonomy" : "categorize_taxonomy"]
                && settings[asTags ? "tagging_wp_taxonomy" : "categorize_wp_taxonomy"]
            )
        }
    }, [settings]);

    const buttonTitle = __('See post categories', 'csp-plugin').replace('%s', taxonomy.name);
    const onClick = (e) => {
        if (!isPluginCorrectlyConfigured) {
            e.preventDefault();
            alert(__("No taxonomy is configured, please contact your administrator.", "csp-plugin"))
            return;
        }

        setModalOpen(true)
    }

    return (
        <>
            <button
                className={"button button-primary csp-plugin-meta-box-action-button csp-plugin-categorize-meta-box-action-button"}
                onClick={onClick}
            >
                {buttonTitle}
            </button>

            <CategoriesModal
                asTags={asTags}
                open={isModalOpen}
                setOpen={setModalOpen}
                currentPost={currentPost}
                taxonomy={taxonomy}
                {...props}
            />
        </>
    )
}

const addRecommendedCategoryAndTagsButtons = (OriginalCategorySelector) => {
    return (props) => {
        const [settings, setSettings] = useState(undefined);
        const [selectedWpTaxonomies, setSelectedWpTaxonomies] = useState([]);
        const [isUserAllowed, setIsUserAllowed] = useState(false);
        const [isTag, setIsTag] = useState(false);

        const shouldAddButton = settings !== undefined
            && selectedWpTaxonomies.length > 0
            && props.slug !== undefined
            && selectedWpTaxonomies.includes(props.slug);

        useEffect(() => {
            SettingsClient.getSettings().then((settings) => {
                setSettings(settings);
                // We currently only support categories and tags
                // So we only add the extension on the category and post_tag taxonomies
                //
                // Later on it will be possible to add x number of taxonomies if
                // there's a need for it and the corresponding models on the CSP
                setSelectedWpTaxonomies(
                    [
                        settings["tagging_wp_taxonomy"],
                        settings["categorize_wp_taxonomy"]
                    ]
                )

                const taxonomyIsTag = props.slug === settings["tagging_wp_taxonomy"]
                setIsTag(taxonomyIsTag);

                CapabilitiesService
                    .isCurrentUserAllowedTo("csp_plugin.semantic_platform." + (taxonomyIsTag ? "tags" : "category"))
                    .then((isUserAllowed) => setIsUserAllowed(isUserAllowed));
            });
        }, []);

        return (
            <>
                {isUserAllowed && shouldAddButton && (
                    <RecommendedCategoryButton settings={settings} asTags={isTag} {...props}/>
                )}
                <OriginalCategorySelector {...props}/>
            </>
        )
    }
}

export default addRecommendedCategoryAndTagsButtons;