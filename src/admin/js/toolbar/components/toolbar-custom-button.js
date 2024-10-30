import {RelatedPostsModal} from "./related-posts-modal";
import { __ } from '@wordpress/i18n';
import {BlockControls} from '@wordpress/block-editor';
import {getTextContent, slice} from '@wordpress/rich-text';
import {ToolbarGroup, ToolbarButton} from '@wordpress/components';
import {useSelect} from '@wordpress/data';
import {useState} from '@wordpress/element';

export const ToolbarCustomButton = ({value, ...props}) => {
    const [isModalOpen, setModalOpen] = useState(false);

    const selectedBlock = useSelect((select) => {
        return select('core/block-editor').getSelectedBlock();
    }, []);

    // We can only update the paragraph blocks
    if (selectedBlock && selectedBlock.name !== 'core/paragraph') {
        return null;
    }

    // We will display the button only if some text is selected
    const selectedText = getTextContent(slice(value));
    if (selectedText === '') {
        return null;
    }

    return (
        <>
            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon="search"
                        title={__('CSP Related posts', 'csp-plugin')}
                        onClick={() => {
                            setModalOpen(true);
                        }}
                    />
                </ToolbarGroup>
            </BlockControls>

            <RelatedPostsModal
                open={isModalOpen}
                setOpen={setModalOpen}
                selectedBlock={selectedBlock}
                selectedText={selectedText}
                value={value}
                {...props}
            />
        </>
    );
}