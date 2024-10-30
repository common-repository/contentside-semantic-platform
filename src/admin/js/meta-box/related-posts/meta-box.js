const {__} = wp.i18n;

const RelatedPostsMetaBox = {
    bindEvents: () => {
        const actionButtons = document.querySelectorAll('.csp-plugin-related-posts-meta-box-action-button');
        if (actionButtons) {
            actionButtons.forEach(actionButton => {
                actionButton.addEventListener('click', RelatedPostsMetaBox.handleActionButtonClick)
            });
        }

        const removePostButtons = document.querySelectorAll("[id^='csp-plugin-remove-related-post-button']");
        if (removePostButtons) {
            removePostButtons.forEach(removePostButton => {
                removePostButton.addEventListener('click', RelatedPostsMetaBox.removeRelatedPost)
            });
        }
    },
    addSpinner: (button, replaceButton = false) => {
        const spinnerElement = document.createElement('div');
        spinnerElement.className = 'spinner csp-plugin-spinner';
        button.after(spinnerElement);

        if (replaceButton) {
            button.parentElement.removeChild(button)
        }

        return spinnerElement
    },
    handleActionButtonClick: (e) => {
        const button = e.target;

        e.preventDefault();

        const spinnerElement = RelatedPostsMetaBox.addSpinner(button);

        const ajaxUrl = button.getAttribute('data-ajaxurl');
        const data = {
            action: button.getAttribute('data-action'),
            nonce: button.getAttribute('data-nonce'),
            postId: button.getAttribute('data-postId')
        }

        fetch(ajaxUrl, {
            method: 'POST', headers: {
                'Content-Type': 'application/x-www-form-urlencoded', 'Cache-Control': 'no-cache',
            }, body: new URLSearchParams(data),
        })
            .then(response => response.json())
            .then(response => {
                spinnerElement.parentNode.removeChild(spinnerElement);
                if (!response.success) {
                    alert(__('An error occurred.', 'csp-plugin'))
                    throw new Error(`Response content : ${response.data}`);
                }

                return response.data;
            })
            .then(data => {
                RelatedPostsMetaBox.replaceRelatedPostsTableContent(data);
            })
            .catch(error => {
                alert(__('An error occurred.', 'csp-plugin'))
                console.error(error);
                spinnerElement.parentNode.removeChild(spinnerElement);
            });
    },
    removeRelatedPost: (e) => {
        const button = e.target;

        e.preventDefault();

        const spinnerElement = RelatedPostsMetaBox.addSpinner(button, true);

        const ajaxUrl = button.getAttribute('data-ajaxurl');
        const data = {
            action: button.getAttribute('data-action'),
            nonce: button.getAttribute('data-nonce'),
            postId: button.getAttribute('data-postId'),
            relatedPost: button.getAttribute('data-relatedPost'),
        }

        fetch(ajaxUrl, {
            method: 'POST', headers: {
                'Content-Type': 'application/x-www-form-urlencoded', 'Cache-Control': 'no-cache',
            }, body: new URLSearchParams(data),
        })
            .then(response => response.json())
            .then(response => {
                spinnerElement.parentNode.removeChild(spinnerElement);
                if (!response.success) {
                    spinnerElement.parentNode.removeChild(spinnerElement);
                    alert(__('An error occurred.', 'csp-plugin'))
                    throw new Error(`Response content : ${response.data}`);
                }

                return response.data;
            })
            .then(data => {
                RelatedPostsMetaBox.replaceRelatedPostsTableContent(data);
            })
            .catch(error => {
                alert(__('An error occurred.', 'csp-plugin'))
                console.error(error);
                spinnerElement.parentNode.removeChild(spinnerElement);
            });
    },
    replaceRelatedPostsTableContent: (data) => {
        document.querySelector('#csp-plugin-related-posts-meta-box > .inside').innerHTML = data.metaBoxContent;
        RelatedPostsMetaBox.bindEvents();
    }
};

export default RelatedPostsMetaBox;
