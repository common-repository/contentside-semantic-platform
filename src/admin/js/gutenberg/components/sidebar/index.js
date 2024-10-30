import CspPluginPanel from "./csp-plugin/csp-plugin-panel";

const {registerPlugin} = wp.plugins;

export const CspPluginPanelController = {
    register: () => {
        // Register the NER settings panel
        registerPlugin('csp-plugin-panel', {render: CspPluginPanel});
    }
}

