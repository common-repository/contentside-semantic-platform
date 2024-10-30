import {useEffect} from "react";
import {useState} from '@wordpress/element';
import {RelatedPostsClient} from "../../../clients/related-posts-client";
import {RelatedPostsResultTable} from "./related-posts-result-table";
import {PanelBody, PanelRow} from '@wordpress/components';
import {__} from "@wordpress/i18n";

export const RelatedPostsPanel = ({currentPost, relatedPosts, setRelatedPosts}) => {
    const [isLoading, setIsLoading] = useState(true);
    const [permalinks, setPermalinks] = useState([]);

    useEffect(() => {
        const data = {
            postId: currentPost.id,
            content: currentPost.content,
            introduction: (currentPost.excerpt !== "" ? currentPost.excerpt : currentPost.title),
            title: currentPost.title
        };

        RelatedPostsClient.fetchRelatedPosts(data)
            .then((response) => {
                setRelatedPosts(response.posts);
                setPermalinks(response.permalinks);
                setIsLoading(false);
            })
            .catch((err) => {
                console.error(err);
                setIsLoading(false);
            });
    }, []);

    return (
        <PanelBody
            title={__("Related posts", "csp-plugin")}
            initialOpen={true}
        >
            <PanelRow>
                <RelatedPostsResultTable
                    isLoading={isLoading}
                    posts={relatedPosts}
                    permalinks={permalinks}
                    setPosts={setRelatedPosts}
                />
            </PanelRow>
        </PanelBody>
    )
}