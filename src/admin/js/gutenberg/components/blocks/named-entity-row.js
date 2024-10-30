import {NerClient} from "../../services/ner-client";
import {useState} from '@wordpress/element';
import {Button, Icon} from '@wordpress/components';
import {__} from "@wordpress/i18n";

export const NamedEntityRow = ({entity}) => {
    const [status, setStatus] = useState(entity.is_candidate ? 'candidate' : 'created');
    const [loading, setLoading] = useState(false);

    const addAsTag = async (entity) => {
        setLoading(true);
        entity.entity = NerClient.decodeEntities(entity.entity)
        await NerClient.createNewTagForEntity(entity)
            .catch(async (error) => {
                setLoading(false);
                if (error.code && error.code === 'term_exists') {
                    // The tag exists in WP but not in CSP dictionary
                    await NerClient.addTagToCSPDictionary(error.data.term_id);

                    return await NerClient.getTagById(error.data.term_id);
                }

                // Another error occurred
                console.error(error);
            })
            .then(() => {
                setStatus('created')
                setLoading(false);
            });
    }

    return (
        <tr>
            <td>{NerClient.decodeEntities(entity.entity)}</td>
            <td>{entity.score}</td>
            <td>
                {status === 'candidate' && (
                    <Button
                        onClick={() => addAsTag(entity)}
                        icon={<Icon icon="tag"/>}
                        label={__('Add as tag', 'csp-plugin')}
                        isSmall
                        isPrimary
                        disabled={entity.selectedTag}
                    ></Button>
                )}
                {status === 'created' && (
                    <Icon sx={{color: "green"}} icon="yes-alt"/>
                )}
                {loading && (
                    <span className={"spinner csp-plugin-spinner"}/>
                )}
            </td>
        </tr>
    );
}