import {useEffect} from "react";
import {NerClient} from "../../services/ner-client";
import {dispatch, useSelect} from '@wordpress/data';
import {useState} from '@wordpress/element';
import {Button, Modal, Panel, PanelBody, PanelRow} from '@wordpress/components';
import {NamedEntitiesTable} from "./named-entities-table";
import {__} from "@wordpress/i18n";

export const NamedEntitiesModal = ({currentPost, pluginSettings}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [modalView, setModalView] = useState(null);

    const [candidates, setCandidates] = useState([]);
    const [knownEntities, setKnownEntities] = useState([]);

    const postContent = useSelect(
        (select) => select('core/editor').getEditedPostContent('postType', currentPost.type, currentPost.id),
        []
    )
    const initialTags = useSelect((select) => (select('core/editor').getEditedPostAttribute("tags")));

    useEffect(() => {
        if (isOpen) {
            setLoading(true);
            setError(null);
            NerClient.fetchNamedEntities({
                content: postContent,
                matcher: "csp_smart_matcher"
            }).then(({entities, overlap, article}) => {
                const candidates = Object
                    .values(entities)
                    .filter((entity) => entity.is_candidate)
                    .sort((a, b) => b.score - a.score)
                    // We want only one instance of entity based on entity.entity
                    .filter((entity, index, self) => self.findIndex((e) => e.entity === entity.entity) === index);
                setCandidates(candidates);
                setKnownEntities(Object.values(entities).filter((entity) => !entity.is_candidate));
                setLoading(false);
            }).catch((error) => {
                console.error(error);
                setError(error);
                setLoading(false);
            });
        }
    }, [isOpen]);

    const onValidate = () => {
        setLoading(true);

        NerClient.fetchNamedEntities({
            content: postContent,
            matcher: "csp_basic_matcher"
        }).then(({entities, overlap, article}) => {
            if (pluginSettings && !pluginSettings.ner_only_add_as_tag) {
                // Update the post content with the extracted entities and their links
                dispatch('core/editor').resetBlocks(
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

            // TODO what to do with overlap ?

            setLoading(false);
            setIsOpen(false);
        });
    }

    const modalViews = {
        loading: () => (<div style={{textAlign: "center"}}><span className={"spinner csp-plugin-spinner"}/></div>),
        error: () => (<div>{__("An error occurred while fetching the named entities", "csp-plugin")}</div>),
        result: () => (
            <Panel>
                <PanelBody
                    title={__("Named entities candidates", "csp-plugin")}
                    initialOpen={false}
                >
                    <PanelRow>
                        <NamedEntitiesTable entities={candidates}/>
                    </PanelRow>
                </PanelBody>
                <PanelBody
                    title={__("Named entities", "csp-plugin")}
                    initialOpen={true}
                >
                    <PanelRow>
                        <NamedEntitiesTable entities={knownEntities}/>
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
        setModalView(modalViews[currentView]())
    }, [modalView, loading, error]);

    return (
        <div>
            <button
                className="button button-primary csp-plugin-meta-box-action-button"
                onClick={() => setIsOpen(true)}
            >
                {__('Extract named entities', 'csp-plugin')}
            </button>
            {isOpen && (
                <Modal
                    title={__('Named entities', 'csp-plugin')}
                    onRequestClose={() => setIsOpen(false)}
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
                            onClick={onValidate}
                            disabled={loading || error}
                        >
                            {__('Validate', 'csp-plugin')}
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}