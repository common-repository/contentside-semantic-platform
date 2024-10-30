import {useEffect} from "react";
import {NerClient} from "../../../clients/ner-client";
import {dispatch, useSelect} from '@wordpress/data';
import {useState} from '@wordpress/element';
import {Button, Modal, Panel, PanelBody, PanelRow} from '@wordpress/components';
import {NamedEntitiesTable} from "./named-entities-table";
import {__} from "@wordpress/i18n";
import {store as editorStore} from '@wordpress/editor';
import {CapabilitiesService} from "../../../services/capabilities-service";

const getUniqueAndCountedEntities = (entities, areCandidates = false, sort = false) => {
    let filteredEntities = entities
        .filter((entity) => entity.is_candidate === areCandidates)

    if (sort) {
        filteredEntities.sort((a, b) => b.score - a.score)
    }

    const countedEntities = filteredEntities
        // We want to count how many times an entity is found
        .map((entity) => {
            entity.count = filteredEntities.filter((e) => e.entity === entity.entity).length
            return entity;
        })

    return countedEntities
        // We want only one instance of entity based on entity.entity
        .filter((entity, index, self) => self.findIndex((e) => e.entity === entity.entity) === index);
}

const getEntityData = (setLoading, setError, setEmpty, setCandidates, setKnownEntities, postContent) => {
    setLoading(true);
    setError(null);
    // We first fetch with basic and smart at the same time, then we merge the results
    Promise.all([
        NerClient.fetchNamedEntities({
            content: postContent,
            matcher: "csp_smart_matcher"
        }),
        NerClient.fetchNamedEntities({
            content: postContent,
            matcher: "csp_basic_matcher"
        })
    ])
        .then(([
                   {
                       entities: entities1,
                       overlap: overlap,
                       article: article
                   }, {entities: entities2}
               ]) => {

            const uniqueEntities = [];
            // We add all the entities from the smart matcher
            Object.values(entities1).forEach((entity) => uniqueEntities.push(entity));
            // We add all the entities from the basic matcher if they are not already present in the smart matcher
            Object.values(entities2).forEach((entity) => {
                if (Object.values(entities1).filter((e) => e.id === entity.id).length === 0) {
                    uniqueEntities.push(entity);
                }
            });

            return ({
                entities: Object.values(uniqueEntities),
                overlap: overlap,
                article: article
            })
        })
        .then(({entities, overlap, article}) => {
            if (entities.length === 0) {
                setEmpty(true);
                return;
            } else {
                setEmpty(false);
            }

            setCandidates(getUniqueAndCountedEntities(entities, true, true));
            setKnownEntities(getUniqueAndCountedEntities(entities, false));

            setLoading(false);
        }).catch((error) => {
        console.error(error);
        setError(error);
        setLoading(false);
    });
}

export const NamedEntitiesModal = ({currentPost, pluginSettings}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(true);
    const [empty, setEmpty] = useState(false);
    const [error, setError] = useState(null);
    const [modalView, setModalView] = useState(null);
    const [isPluginCorrectlyConfigured, setIsPluginCorrectlyConfigured] = useState(false);
    const [isUserAllowedToSeeCandidates, setIsUserAllowedToSeeCandidates] = useState(false)

    const [candidates, setCandidates] = useState([]);
    const [knownEntities, setKnownEntities] = useState([]);

    const postContent = useSelect(
        (select) => select(editorStore).getEditedPostContent('postType', currentPost.type, currentPost.id),
        []
    )

    const initialTags = useSelect((select) => (select(editorStore).getEditedPostAttribute("tags")));
    useEffect(() => {
        if (isOpen) {
            getEntityData(setLoading, setError, setEmpty, setCandidates, setKnownEntities, postContent);
        }
    }, [isOpen]);

    useEffect(() => {
        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.entities.add_suggested")
            .then((isAllowed) => {
                setIsUserAllowedToSeeCandidates(isAllowed);
            });
    }, []);

    const onValidate = (e) => {
        e.preventDefault();
        setLoading(true);
        // We create an artificial wait time to avoid the Trie not being fully updated
        setTimeout(() => {
            NerClient.fetchNamedEntities({
                content: postContent,
                matcher: "csp_basic_matcher"
            }).then(({entities, overlap, article}) => {
                if (pluginSettings && !pluginSettings.ner_only_add_as_tag) {
                    // Update the post content with the extracted entities and their links
                    dispatch(editorStore).resetBlocks(
                        wp.blocks.parse(article.text)
                    );
                }

                const entityIds = Object.values(entities).map((entity) => entity.id);
                const newTags = [...initialTags, ...entityIds]
                    .filter((value, index, self) => self.indexOf(value) === index);
                const edits = {tags: newTags};
                dispatch('core').editEntityRecord(
                    'postType',
                    currentPost.type,
                    currentPost.id,
                    edits
                )

                setLoading(false);
                setIsOpen(false);
            });
        }, 3000);
    }

    const modalViews = {
        loading: () => (<div style={{textAlign: "center"}}><span className={"spinner csp-plugin-spinner"}/></div>),
        error: () => (<div>{__("An error occurred while fetching the named entities", "csp-plugin")}</div>),
        empty: () => (<div>{__("No named entities found", "csp-plugin")}</div>),
        result: () => (
            <Panel>
                {isUserAllowedToSeeCandidates === true &&
                    <PanelBody
                        title={__("Named entities candidates", "csp-plugin")}
                        initialOpen={false}
                    >
                        <PanelRow>
                            <NamedEntitiesTable entities={candidates} withType={true}/>
                        </PanelRow>
                    </PanelBody>
                }

                <PanelBody
                    title={__("Known named entities", "csp-plugin")}
                    initialOpen={true}
                >
                    <PanelRow>
                        <NamedEntitiesTable entities={knownEntities} withAction={false}/>
                    </PanelRow>
                </PanelBody>
            </Panel>
        )
    }

    useEffect(() => {
        let currentView = "result";
        if (error) {
            currentView = "error";
        }
        if (loading) {
            currentView = "loading";
        }
        if (empty) {
            currentView = "empty";
        }
        setModalView(modalViews[currentView]())
    }, [modalView, loading, error, empty]);

    useEffect(() => {
        if (pluginSettings !== undefined) {
            setIsPluginCorrectlyConfigured(
                pluginSettings.is_api_key_valid === "true"
                && pluginSettings.allowed_features.includes("NER")
                && pluginSettings.ner_dictionary
            )
        }
    }, [pluginSettings]);

    const onClick = (e) => {
        if (!isPluginCorrectlyConfigured) {
            e.preventDefault();
            alert(__("No dictionary is configured, please contact your administrator.", "csp-plugin"))
            return;
        }

        setIsOpen(true)
    }

    return (
        <div>
            <button
                className="button button-primary csp-plugin-meta-box-action-button"
                onClick={onClick}
            >
                {__('Extract named entities', 'csp-plugin')}
            </button>
            {isOpen && (
                <Modal
                    title={__('CSP Named entities', 'csp-plugin')}
                    onRequestClose={(e) => {
                        if (e.target.classList.contains("csp-plugin-add-button")) {
                            return;
                        }
                        setIsOpen(false)
                    }}
                    style={{minWidth: "90%", paddingBottom: "64px"}}
                >
                    {modalView}

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
                            {__('Add', 'csp-plugin')}
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}