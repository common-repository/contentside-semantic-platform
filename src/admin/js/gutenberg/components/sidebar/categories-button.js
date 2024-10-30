import {useSelect} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import {CategoriesModal} from "./categories-modal";
import {useState} from '@wordpress/element';
import {useEffect} from "react";
import {SettingsClient} from "../../services/settings-client";

const RecommendedCategoryButton = ({asTags = false, ...props}) => {
    const currentPost = useSelect((select) => (select('core/editor').getCurrentPost()));
    let initialTaxonomyElements = undefined;
    if (!asTags) {
        initialTaxonomyElements = useSelect((select) => (select('core/editor').getEditedPostAttribute("categories")));
    } else {
        initialTaxonomyElements = useSelect((select) => (select('core/editor').getEditedPostAttribute("tags")));
    }

    const [isModalOpen, setModalOpen] = useState(false);

    const buttonTitle = asTags ? __('Tag post', 'csp-plugin') : __('Categorize post', 'csp-plugin');
    const onClick = (e) => setModalOpen(true)

    return (
        <>
            <button
                className={"button button-primary csp-plugin-meta-box-action-button csp-plugin-categorize-meta-box-action-button"}
                onClick={onClick}
            >
                {buttonTitle}
            </button>

            {initialTaxonomyElements !== undefined && (
                <CategoriesModal
                    asTags={asTags}
                    open={isModalOpen}
                    setOpen={setModalOpen}
                    currentPost={currentPost}
                    initialTaxonomyElements={initialTaxonomyElements}
                    {...props}
                />
            )}
        </>
    )
}

const addRecommendedCategoryButton = (OriginalCategorySelector) => {
    // const [isAllowed, setIsAllowed] = useState(false);
    // TODO continue that as the slug defines the feature
    //csp-plugin_allowed_features
    // useEffect(() => {
    //     SettingsClient.getSettings().then((settings) => {
    //         if (settings !== undefined) {
    //             setIsAllowed(settings.allowed_features.includes("categorize"));
    //         }
    //     });
    // }, []);
    return (props) => {
        return (
            <>
                {props.slug === 'category' && (
                    <RecommendedCategoryButton {...props}/>
                )}
                {props.slug === 'post_tag' && (
                    <RecommendedCategoryButton asTags={true} {...props}/>
                )}
                <OriginalCategorySelector {...props}/>
            </>
        )
    }
}

export default addRecommendedCategoryButton;