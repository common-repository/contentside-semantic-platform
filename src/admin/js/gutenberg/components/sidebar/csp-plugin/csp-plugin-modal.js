import {__} from "@wordpress/i18n";
import {useState} from '@wordpress/element';
import {store as editorStore} from '@wordpress/editor';
import {useEffect, useReducer} from "react";
import {dispatch, useSelect} from '@wordpress/data';
import {Spinner, Button, Modal, Panel} from '@wordpress/components';
import CspPluginButton from "./csp-plugin-find-links-button";
import {NamedEntitiesPanels} from "../ner/named-entities-panels";
import {TaxonomiesPanel} from "../categorize/taxonomies-panel";
import {CapabilitiesService} from "../../../services/capabilities-service";
import {RelatedPostsPanel} from "../related-posts/related-posts-panel";
import {SettingsClient} from "../../../clients/settings-client";
import {RelatedPostsClient} from "../../../clients/related-posts-client";
import RelatedPostsMetaBox from "../../../../meta-box/related-posts/meta-box";
import {NerClient} from "../../../clients/ner-client";

const CspPluginModal = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(false);

    const currentPost = useSelect((select) => select(editorStore).getCurrentPost());
    const postContent = useSelect(
        (select) => select(editorStore).getEditedPostContent('postType', currentPost.type, currentPost.id),
        []
    );
    const {getEditedPostAttribute} = useSelect((select) => (select(editorStore)), []);

    const [settings, setSettings] = useState(null);
    const [configuredTaxonomies, setConfiguredTaxonomies] = useState({
        tagging_taxonomy: null,
        categorize_taxonomy: null
    });

    const [categories, setCategories] = useState([]);
    const [tags, setTags] = useState([]);
    const [relatedPosts, setRelatedPosts] = useState([]);

    // Authorization
    const [isUserAllowedToCategorize, setIsUserAllowedToCategorize] = useState(false);
    const [isUserAllowedToTag, setIsUserAllowedToTag] = useState(false);
    const [isUserAllowedToSearchEntities, setIsUserAllowedToSearchEntities] = useState(false);
    const [isUserAllowedToLookAlike, setIsUserAllowedToLookAlike] = useState(false);

    const refresh = () => {
        setLoading(true);
        setTimeout(() => {
            setLoading(false);
        }, 1000);
    }

    // Initial requests
    useEffect(() => {
        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.category")
            .then((isAllowed) => {
                setIsUserAllowedToCategorize(isAllowed);
            });

        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.tags")
            .then((isAllowed) => {
                setIsUserAllowedToTag(isAllowed);
            });

        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.entities")
            .then((isAllowed) => {
                setIsUserAllowedToSearchEntities(isAllowed);
            });

        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.entities")
            .then((isAllowed) => {
                setIsUserAllowedToLookAlike(isAllowed);
            });

        SettingsClient.getSettings().then((settings) => {
            setSettings(settings);
            // We currently only support categories and tags
            // So we only add the extension on the category and post_tag taxonomies
            //
            // Later on it will be possible to add x number of taxonomies if
            // there's a need for it and the corresponding models on the CSP
            setConfiguredTaxonomies(
                {
                    tagging_taxonomy: settings["tagging_wp_taxonomy"],
                    categorize_taxonomy: settings["categorize_wp_taxonomy"]
                }
            )
        });
    }, []);

    const saveTaxonomyElements = (taxonomyElements, asTags) => {
        const edits = {};
        // Ugly fix but WP doesn't update tags and categories through their slugs
        let postAttributeToUpdate = (asTags ? configuredTaxonomies.tagging_taxonomy : configuredTaxonomies.categorize_taxonomy)
        if (postAttributeToUpdate === "post_tag") {
            postAttributeToUpdate = "tags"
        } else if (postAttributeToUpdate === "category") {
            postAttributeToUpdate = "categories"
        }
        edits[postAttributeToUpdate] = taxonomyElements;

        dispatch('core').editEntityRecord(
            'postType',
            currentPost.type,
            currentPost.id,
            edits
        )

        return Promise.resolve();
    }

    const saveRelatedPosts = (relatedPosts) => {
        return RelatedPostsClient.saveRelatedPosts({
            postId: currentPost.id,
            posts: relatedPosts.map((post) => post.ID)
        }).then(relatedPostsResponse => {
            RelatedPostsMetaBox.replaceRelatedPostsTableContent(relatedPostsResponse)
        })
    }

    const saveNamedEntities = () => {
        return NerClient.fetchNamedEntities({
            content: postContent,
            matcher: "csp_basic_matcher"
        }).then(({entities, overlap, article}) => {
            if (settings && !settings.ner_only_add_as_tag) {
                // Update the post content with the extracted entities and their links
                dispatch(editorStore).resetBlocks(
                    wp.blocks.parse(article.text)
                );
            }

            const entityIds = Object.values(entities).map((entity) => entity.id);
            const initialTags = getEditedPostAttribute("tags");
            const newTags = [...initialTags, ...entityIds]
                .filter((value, index, self) => self.indexOf(value) === index);
            const edits = {tags: newTags};
            dispatch('core').editEntityRecord(
                'postType',
                currentPost.type,
                currentPost.id,
                edits
            )
        })
    }

    const onValidate = () => {
        setLoading(true);

        Promise.all([
            saveTaxonomyElements(categories, false),
            saveTaxonomyElements(tags, true),
            saveRelatedPosts(relatedPosts)
        ])
            .then(() => saveNamedEntities())
            .then(() => {
                setIsOpen(false);
                setLoading(false);
            })
            .catch((error) => {
                console.error(error);
                setLoading(false);

                setError(true);
            });
    }

    return (
        <>
            <CspPluginButton
                setModalOpen={setIsOpen}
            />

            {isOpen && (
                <Modal
                    title={__('Find links', 'csp-plugin')}
                    onRequestClose={(e) => {
                        if (e.target.classList.contains("csp-plugin-add-button")) {
                            return;
                        }
                        setIsOpen(false)
                    }}
                    style={{
                        width: "98%",
                        paddingBottom: "64px",
                        minHeight: "95%"
                    }}
                >
                    {error && <div style={{
                        display: "flex",
                        width: "100%",
                        height: "90vh",
                        justifyContent: "center",
                        alignItems: "center"
                    }}>
                        <h2>{__('An error occurred.', 'csp-plugin')}</h2>
                    </div>}
                    {loading && !error &&
                        <div style={{
                            display: "flex",
                            width: "100%",
                            height: "90vh",
                            justifyContent: "center",
                            alignItems: "center"
                        }}>
                            <Spinner
                                style={{
                                    height: 'calc(4px * 10)',
                                    width: 'calc(4px * 10)'
                                }}
                            />
                        </div>}
                    {!loading && !error &&
                        <Panel>
                            {/* Classification (classification multi-class) */}
                            {isUserAllowedToCategorize === true &&
                                <TaxonomiesPanel
                                    asTags={false}
                                    currentPost={currentPost}
                                    categories={categories}
                                    setCategories={setCategories}
                                    configuredTaxonomies={configuredTaxonomies}
                                />
                            }

                            {/* Tagging (classification multi-label) */}
                            {isUserAllowedToTag === true &&
                                <TaxonomiesPanel
                                    asTags={true}
                                    currentPost={currentPost}
                                    tags={tags}
                                    setTags={setTags}
                                    configuredTaxonomies={configuredTaxonomies}
                                />
                            }

                            {isUserAllowedToLookAlike === true &&
                                <RelatedPostsPanel
                                    currentPost={currentPost}
                                    relatedPosts={relatedPosts}
                                    setRelatedPosts={setRelatedPosts}
                                />
                            }

                            {/* Contains both NER panels */}
                            {isUserAllowedToSearchEntities === true &&
                                <NamedEntitiesPanels
                                    postContent={postContent}
                                    currentPost={currentPost}
                                    settings={settings}
                                />
                            }
                        </Panel>
                    }

                    <div style={{
                        position: "fixed",
                        bottom: "10px",
                        left: "10px",
                        backgroundColor: "white",
                    }}>
                        <Button
                            style={{marginRight: "1em"}}
                            variant={"secondary"}
                            onClick={refresh}
                        >
                            {__('Refresh', 'csp-plugin')}
                        </Button>
                    </div>

                    <div style={{
                        position: "fixed",
                        bottom: "10px",
                        right: "10px",
                        backgroundColor: "white",
                    }}>
                        <Button
                            style={{marginRight: "1em"}}
                            variant={"secondary"}
                            onClick={() => setIsOpen(false)}
                        >
                            {__('Cancel', 'csp-plugin')}
                        </Button>

                        <Button
                            variant={"primary"}
                            className={"csp-plugin-add-button"}
                            onClick={(e) => onValidate(e)}
                            disabled={loading || error}
                        >
                            {__('Create', 'csp-plugin')}
                        </Button>
                    </div>

                </Modal>
            )}
        </>
    )
}

export default CspPluginModal