import apiFetch from '@wordpress/api-fetch';

export const RelatedPostsClient = {
    fetchRelatedPosts: (data) => {
        return apiFetch({
            path: '/csp-plugin/v1/related-posts',
            method: 'POST',
            data: data
        })
    },

    getContentFromSelectedBlock: (selectedBlock, selectedText) => {
        return (selectedBlock.attributes.content !== "" ? selectedBlock.attributes.content : selectedText);
    }
}