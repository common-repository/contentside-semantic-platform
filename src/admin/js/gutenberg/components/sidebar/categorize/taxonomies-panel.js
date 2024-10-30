import {useState} from '@wordpress/element';
import {useEffect, forwardRef} from "react";
import {TaxonomyClient} from "../../../clients/taxonomy-client";
import {CategoriesResultTable} from "./categories-result-table";
import {useSelect, dispatch} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';
import {PanelBody, PanelRow} from '@wordpress/components';
import {__} from "@wordpress/i18n";

export const TaxonomiesPanel = ({asTags, currentPost, tags, setTags, categories, setCategories}) => {
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

    const [currentTaxonomyElements, setCurrentTaxonomyElements] = (asTags ? [tags, setTags] : [categories, setCategories]);

    useEffect(() => {
        setCurrentTaxonomyElements([...new Set(currentTaxonomyElements.concat(initialTaxonomyElements))]);
    }, [initialTaxonomyElements]);

    useEffect(() => {
        const data = {
            postId: wp.data.select(editorStore).getCurrentPostId(),
            content: currentPost.content,
            introduction: (currentPost.excerpt !== "" ? currentPost.excerpt : currentPost.title),
            title: currentPost.title
        };

        TaxonomyClient.fetchRecommendedTaxonomy(data, asTags)
            .then((response) => {
                setTaxonomyElements(response.taxonomyElements);
                setIsLoading(false);
            })
            .catch((err) => {
                console.error(err);
                setIsLoading(false);
            });
    }, []);

    return (
        <PanelBody
            title={__((asTags ? "Tag post" : "Categorize post"), "csp-plugin")}
            initialOpen={true}
        >
            <PanelRow>
                <CategoriesResultTable
                    asTags={asTags}
                    isLoading={isLoading}
                    taxonomyElements={taxonomyElements}
                    currentPost={currentPost}
                    setCurrentTaxonomyElements={setCurrentTaxonomyElements}
                    currentTaxonomyElements={currentTaxonomyElements}
                />
            </PanelRow>
        </PanelBody>
    );
};
