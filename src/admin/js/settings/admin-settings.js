import apiFetch from '@wordpress/api-fetch';

const {__} = wp.i18n;
const $ = jQuery;

const AdminSettings = {
    bindEvents: () => {
        const button = document.querySelector('.csp-plugin-start-transaction-button');
        if (button) {
            button.addEventListener('click', AdminSettings.clickHandle)
        }

        // Making sure that related-posts is enabled before following the synchronization
        const progressHiddenInput = document.querySelector('#csp-plugin_sync_progress');
        if (progressHiddenInput) {
            setInterval(() => {
                AdminSettings.followSynchronization();
            }, 10000);
        }
    },
    followSynchronization: () => {
        apiFetch({
            path: '/csp-plugin/v1/related-posts/nb-already-synced',
            method: 'GET',
        }).then((response) => {
            const total = document.querySelector('#csp-plugin_sync_total').getAttribute("value");
            if (total === response.nbPostSynced) {
                AdminSettings.markSyncAsDone();
            }
        }).catch((err) => {
            console.error(err);
        });
    },
    markSyncAsDone: () => {
        const doneText = __('Done', 'csp-plugin');
        $('#csp-plugin-start-sync-button')
            .html(doneText)
            .attr('disabled', true)
            .attr('style', 'background-color: #4CAF50 !important; color: white !important');
    },
    clickHandle: (e) => {
        const button = e.target;
        e.preventDefault();

        const ajaxUrl = button.getAttribute('data-ajaxurl');
        const data = {
            action: button.getAttribute('data-action'),
            nonce: button.getAttribute('data-nonce')
        }

        // prefill the hidden field with sync last date in the format Y-m-d H:i:s
        $('#csp-plugin_setting_related_posts_sync_last_sync_date').val(new Date().toISOString().slice(0, 19).replace('T', ' '))

        fetch(ajaxUrl, {
            method: 'POST', headers: {
                'Content-Type': 'application/x-www-form-urlencoded', 'Cache-Control': 'no-cache',
            }, body: new URLSearchParams(data),
        })
            .then(response => response.json())
            .then(response => {
                if (!response.success) {
                    alert(__('An error occurred and the synchronization could not start', 'csp-plugin'))
                    throw new Error(`Response content : ${response.data}`);
                }

                return response.data;
            })
            .then(() => {
                const total = document.querySelector('#csp-plugin_sync_total').getAttribute("value");
                document.querySelector('#csp-plugin-sync-info').parentNode.removeChild(document.querySelector('#csp-plugin-sync-info'));
                const ongoingText = __('Loading...', 'csp-plugin');
                $('#csp-plugin-start-sync-button')
                    .html(ongoingText)
                    .attr('disabled', true);

                // We estimate that the synchronization will take 2 seconds per post
                setTimeout(() => {
                    AdminSettings.markSyncAsDone();
                }, (total * 2000));
            })
            .catch(error => console.error(error));
    }
};

export default AdminSettings;
