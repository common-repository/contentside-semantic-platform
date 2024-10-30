import {
    Spinner,
    Button
} from '@wordpress/components';
import {toggleFormat} from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';

export const RelatedPostResultTable = ({
                                           isLoading,
                                           posts,
                                           value,
                                           onChange,
                                           setOpen,
                                           permalinks
                                       }) => {

    const handleSelection = (post) => {
        setOpen(false);
        onChange(
            toggleFormat(
                value, {
                    type: 'csp-plugin/link-to-related-post',
                    attributes: {
                        href: permalinks[post.ID]
                    }
                }
            )
        );
    }

    return (
        <div className={"csp-plugin-related-posts-modal-related-post-result-table-container"}>
            <table className={"wp-list-table widefat fixed pages sortable "}>
                {isLoading && <tr>
                    <td>
                        <Spinner/>
                    </td>
                </tr>
                }

                {(!isLoading && posts.length > 0) ? posts.map((post) => (
                    <tr>
                        <td>
                            <strong>{post.post_title}</strong>
                            <Button className={"float-right"} onClick={() => handleSelection(post)}
                                    variant="primary">{__("Select", "csp-plugin")}</Button>
                        </td>
                    </tr>
                )) : (!isLoading ? <tr>
                    <td><span>{__("No result", "csp-plugin")}</span></td>
                </tr> : <></>)
                }
            </table>
        </div>
    );
}