import apiFetch from '@wordpress/api-fetch';

let allowedFeatures = [];
let isValid = null;

export const SettingsClient = {
    getSettings() {
        return apiFetch({
            path: '/csp-plugin/v1/settings',
            method: 'GET',
        });
    },

    isApiKeyValid() {
        if (isValid !== null) {
            return Promise.resolve(isValid);
        }

        return apiFetch({
            path: '/csp-plugin/v1/settings',
            method: 'GET',
        }).then((settings) => {
            isValid = settings && settings.is_api_key_valid && settings.is_api_key_valid === "true";
            allowedFeatures = settings["allowed_features"];

            return SettingsClient;
        });
    },

    isFeatureAllowed(featureName) {
        if (allowedFeatures.length > 0) {
            return Promise.resolve(allowedFeatures.includes(featureName));
        }

        return apiFetch({
            path: '/csp-plugin/v1/settings',
            method: 'GET',
        }).then((settings) => {
            allowedFeatures = settings["allowed_features"];
            return allowedFeatures.includes(featureName);
        });
    }
}