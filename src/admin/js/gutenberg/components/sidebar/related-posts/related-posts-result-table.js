import {
    Spinner,
    Button
} from '@wordpress/components';
import {__} from '@wordpress/i18n';

export const RelatedPostsResultTable = ({isLoading, posts, permalinks, setPosts}) => {
    const handleDelete = (postId) => {
        setPosts(posts.filter((post) => post.ID !== postId));
    }

    return (
        <div className={"csp-plugin-modal-result-table-container"}>
            <table className={"wp-list-table widefat fixed pages sortable "}>
                {isLoading && <tr>
                    <td style={{textAlign: "center"}}><Spinner/></td>
                </tr>
                }

                {(!isLoading && posts.length > 0) ? posts.map((post) => (
                    <tr>
                        <td>
                            <strong><a href={permalinks[post.ID]} target='_blank'>{post.post_title}</a></strong>
                            <Button className={"float-right"} onClick={() => handleDelete(post.ID)}
                                    variant="primary">{__("Delete", "csp-plugin")}</Button>
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