import {registerFormatType} from '@wordpress/rich-text';
import {ToolbarCustomButton} from "./components/toolbar-custom-button";

const Toolbar = {
    register: () => {
        registerFormatType('csp-plugin/link-to-related-post', {
            title: 'CSP Related post',
            tagName: 'a',
            className: 'csp-plugin-related-post-link',
            edit: ToolbarCustomButton
        });
    }
}

export default Toolbar;


