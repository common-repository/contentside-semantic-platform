import {Button, Modal} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {useEffect} from "react";
import {__} from '@wordpress/i18n';
import {TaxonomyClient} from "../../../clients/taxonomy-client";
import {CategoriesResultTable} from "./categories-result-table";
import {dispatch, useSelect} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';

const validateData = (data) => {
    const invalidFields = ["title", "content", "introduction"].filter((field) => {
        return (!data[field] || data[field] === "");
    });

    if (invalidFields.length > 0) {
        return invalidFields;
    }

    return true;
}
export const CategoriesModal = ({
                                    asTags,
                                    open,
                                    setOpen,
                                    currentPost,
                                    taxonomy
                                }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [taxonomyElements, setTaxonomyElements] = useState([]);

    let initialTaxonomyElements;
    if (!asTags) {
        initialTaxonomyElements = useSelect(
            (select) => select(editorStore).getEditedPostAttribute("categories"),
            []
        );
    } else {
        initialTaxonomyElements = useSelect(
            (select) => select(editorStore).getEditedPostAttribute("tags"),
            []
        );
    }

    const [currentTaxonomyElements, setCurrentTaxonomyElements] = useState(initialTaxonomyElements);

    useEffect(() => {
        setCurrentTaxonomyElements([...new Set(currentTaxonomyElements.concat(initialTaxonomyElements))]);
    }, [initialTaxonomyElements]);

    const closeModal = (saveNewTaxonomyElements) => {
        if (saveNewTaxonomyElements) {
            const edits = {};
            // Ugly fix but WP doesn't update tags and categories through their slugs
            let postAttributeToUpdate = taxonomy.slug
            if (taxonomy.slug === "post_tag") {
                postAttributeToUpdate = "tags"
            } else if (taxonomy.slug === "category") {
                postAttributeToUpdate = "categories"
            }
            edits[postAttributeToUpdate] = currentTaxonomyElements;
            dispatch('core').editEntityRecord(
                'postType',
                currentPost.type,
                currentPost.id,
                edits
            )
        }
        setOpen(false);
    };

    useEffect(() => {
        if (open) {
            const currentPost = wp.data.select(editorStore).getCurrentPost();
            const data = {
                postId: wp.data.select(editorStore).getCurrentPostId(),
                content: currentPost.content,
                introduction: (currentPost.excerpt !== "" ? currentPost.excerpt : currentPost.title),
                title: currentPost.title
            };
            if (validateData(data).length > 0) {
                setOpen(false);
                return;
            }

            TaxonomyClient.fetchRecommendedTaxonomy(data, asTags)
                .then((response) => {
                    setTaxonomyElements(response.taxonomyElements);
                    setIsLoading(false);
                })
                .catch((err) => {
                    console.error(err);
                    setIsLoading(false);
                });
        }
    }, [open]);

    const modalTitle = __('See post categories', 'csp-plugin').replace('%s', taxonomy.name);

    return (<>
        {open && (
            <Modal className={"csp-plugin-related-posts-modal"} title={modalTitle}
                   onRequestClose={() => closeModal(false)}>

                <CategoriesResultTable
                    asTags={asTags}
                    isLoading={isLoading}
                    taxonomyElements={taxonomyElements}
                    currentPost={currentPost}
                    setCurrentTaxonomyElements={setCurrentTaxonomyElements}
                    currentTaxonomyElements={currentTaxonomyElements}
                />

                <Button className={"csp-plugin-related-posts-modal-button"} variant="secondary"
                        onClick={() => closeModal(false)}>
                    {__('Cancel', 'csp-plugin')}
                </Button>

                <Button className={"csp-plugin-related-posts-modal-button"} variant="primary"
                        onClick={() => closeModal(true)}>
                    {__('Add', 'csp-plugin')}
                </Button>
            </Modal>)}
    </>);
};
