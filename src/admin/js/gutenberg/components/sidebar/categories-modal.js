import {Button, Modal} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {useEffect} from "react";
import {__} from '@wordpress/i18n';
import {TaxonomyClient} from "../../services/taxonomy-client";
import {CategoriesResultTable} from "./categories-result-table";
import {dispatch} from '@wordpress/data';

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
                                    initialTaxonomyElements
                                }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [taxonomyElements, setTaxonomyElements] = useState([]);
    const [currentTaxonomyElements, setCurrentTaxonomyElements] = useState([...initialTaxonomyElements]);

    const closeModal = (saveNewTaxonomyElements) => {
        if (saveNewTaxonomyElements) {
            const edits = asTags ? {tags: currentTaxonomyElements} : {categories: currentTaxonomyElements};
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
            const currentPost = wp.data.select("core/editor").getCurrentPost();
            const data = {
                postId: wp.data.select("core/editor").getCurrentPostId(),
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

    const modalTitle = asTags ? __('Tag post', 'csp-plugin') : __('Categorize post', 'csp-plugin');

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
                    {__('Save', 'csp-plugin')}
                </Button>
            </Modal>)}
    </>);
};
