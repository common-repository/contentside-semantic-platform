class CapabilitiesCache {
    constructor() {
        this.cachedCapabilities = null;
        this.currentPromise = null;

        setInterval(() => {
            this.cachedCapabilities = null
        }, 10000);
    }

    resetCurrentPromise() {
        this.currentPromise = null;
    }
}

const capabilitiesCache = new CapabilitiesCache();

export { capabilitiesCache };