import EditNamedEntitiesBlock from "./named-entities-block";
import {registerBlockType} from '@wordpress/blocks';

const BlocksManager = {
    register: () => {
        registerBlockType('csp-plugin/ner-meta-block', {
            title: 'NER',
            category: 'csp',
            attributes: {
                named_entities: {type: 'array', default: []},
            },
            edit: EditNamedEntitiesBlock,
            save: () => {
                return null;
            },
        });
    }
}
export default BlocksManager;
