import {PluginDocumentSettingPanel} from "@wordpress/edit-post";
import {__} from "@wordpress/i18n";
import CspPluginModal from "./csp-plugin-modal";

const CspPluginPanel = () => {
    return (
        <>
            <PluginDocumentSettingPanel title={__("ContentSide Semantic Platform", "csp-plugin")}>
                <CspPluginModal/>
            </PluginDocumentSettingPanel>
        </>
    );
}

export default CspPluginPanel