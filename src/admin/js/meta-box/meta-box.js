const {__} = wp.i18n;

const MetaBox = {
    bindEvents: () => {
        const button = document.querySelector('.csp-plugin-discover');
        if (button) {
            button.addEventListener('click', MetaBox.syncClickHandle)
        }

        const removePostButtons = document.querySelectorAll("[id^='csp-plugin-remove-related-post-button']");
        if (removePostButtons) {
            removePostButtons.forEach(removePostButton => {
                removePostButton.addEventListener('click', MetaBox.removeRelatedPost)
            });
        }
    },
    syncClickHandle: (e) => {
        const button = e.target;

        e.preventDefault();

        const spinnerElement = document.createElement('div');
        spinnerElement.className = 'spinner csp-plugin-spinner';
        button.after(spinnerElement);

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
                    alert(__('An error occurred and the synchronization could not start', 'csp-plugin'))
                    throw new Error(`Response content : ${response.data}`);
                }

                return response.data;
            })
            .then(data => {
                MetaBox.replaceRelatedPostsTableContent(data);
            })
            .catch(error => console.error(error));
    },
    removeRelatedPost: (e) => {
        const button = e.target;

        e.preventDefault();

        const spinnerElement = document.createElement('div');
        spinnerElement.className = 'spinner csp-plugin-spinner';
        button.after(spinnerElement);

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
                MetaBox.replaceRelatedPostsTableContent(data);
            })
            .catch(error => {
                spinnerElement.parentNode.removeChild(spinnerElement);
                alert(__('An error occurred.', 'csp-plugin'))
                console.error(error);
            });
    },
    replaceRelatedPostsTableContent: (data) => {
        document.querySelector('#csp-plugin-related-posts-meta-box > .inside').innerHTML = data.metaBoxContent;
        MetaBox.bindEvents();
    }
};

export default MetaBox;
