import {CapabilitiesClient} from "../clients/capabilities-client";
import {capabilitiesCache} from "../cache/capabilities-cache";

export const CapabilitiesService = {
    isCurrentUserAllowedTo: (capability) => {
        if (capabilitiesCache.currentPromise !== null) {
            // We are already fetching the capabilities
            // Do the request only when the first one is already over and should return the cached value
            return capabilitiesCache.currentPromise.then(() => {
                return CapabilitiesService.isCurrentUserAllowedTo(capability);
            });
        }
        if (capabilitiesCache.cachedCapabilities !== null) {
            return new Promise((resolve) => {
                resolve(Object.keys(capabilitiesCache.cachedCapabilities).includes(capability) && capabilitiesCache.cachedCapabilities[capability] === true);
            });
        }

        const currentPromise = CapabilitiesClient.getCurrentUserCapabilities().then((capabilities) => {
            capabilitiesCache.cachedCapabilities = capabilities;
            capabilitiesCache.resetCurrentPromise();
            return Object.keys(capabilities).includes(capability) && capabilities[capability] === true;
        });

        capabilitiesCache.currentPromise = currentPromise;

        return currentPromise
    }
}