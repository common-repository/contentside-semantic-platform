import apiFetch from '@wordpress/api-fetch';

export const CapabilitiesClient = {
    getCurrentUserCapabilities() {
        return apiFetch({
            path: '/csp-plugin/v1/capabilities/current-user',
            method: 'GET',
        });
    }
}
