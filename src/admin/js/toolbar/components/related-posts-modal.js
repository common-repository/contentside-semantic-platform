import {Button, Modal} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {RelatedPostResultTable} from "./related-post-result-table";
import {useEffect} from "react";
import {useSelect} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import {RelatedPostsClient} from "../services/related-posts-client";

const validateData = (data) => {
    const invalidFields = ["title", "content", "introduction"].filter((field) => {
        return (!data[field] || data[field] === "");
    });

    if (invalidFields.length > 0) {
        return invalidFields;
    }

    return true;
}
export const RelatedPostsModal = ({open, setOpen, value, onChange, selectedText}) => {
    const closeModal = () => setOpen(false);

    const [isLoading, setIsLoading] = useState(true);
    const [posts, setPosts] = useState([]);
    const [permalinks, setPermalinks] = useState([]);

    const selectedBlock = useSelect((select) => {
        return select('core/block-editor').getSelectedBlock();
    }, []);

    useEffect(() => {
        if (open) {
            const currentPost = wp.data.select("core/editor").getCurrentPost();
            const data = {
                postId: wp.data.select("core/editor").getCurrentPostId(),
                content: RelatedPostsClient.getContentFromSelectedBlock(selectedBlock, selectedText),
                introduction: (currentPost.excerpt !== "" ? currentPost.excerpt : currentPost.title),
                title: currentPost.title
            };
            if (validateData(data).length > 0) {
                setOpen(false);
                return;
            }

            RelatedPostsClient.fetchRelatedPosts(data)
                .then((response) => {
                    setPosts(response.posts);
                    setPermalinks(response.permalinks);
                    setIsLoading(false);
                })
                .catch((err) => {
                    console.error(err);
                    setIsLoading(false);
                });
        }
    }, [open]);

    return (<>
        {open && (
            <Modal className={"csp-plugin-related-posts-modal"} title={__('Choose the related post', 'csp-plugin')}
                   onRequestClose={closeModal}>

                <RelatedPostResultTable
                    isLoading={isLoading}
                    posts={posts}
                    value={value}
                    onChange={onChange}
                    setOpen={setOpen}
                    permalinks={permalinks}
                />

                <Button className={"csp-plugin-related-posts-modal-button"} variant="secondary"
                        onClick={closeModal}>
                    {__('Cancel', 'csp-plugin')}
                </Button>

                <Button className={"csp-plugin-related-posts-modal-button"} variant="primary" onClick={closeModal}>
                    {__('Save', 'csp-plugin')}
                </Button>
            </Modal>)}
    </>);
};
