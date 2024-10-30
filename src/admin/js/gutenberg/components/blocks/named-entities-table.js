import {NamedEntityRow} from "./named-entity-row";
import {__} from "@wordpress/i18n";

export const NamedEntitiesTable = ({entities}) => {
    return (
        <table className={"wp-list-table widefat fixed pages sortable "}>
            <thead>
            <tr>
                <td style={{width: "120px"}}>{__("Entity", "csp-plugin")}</td>
                <td style={{width: "50px"}}>{__("Score", "csp-plugin")}</td>
                <td style={{width: "60px"}}>{__("Actions", "csp-plugin")}</td>
            </tr>
            </thead>
            <tbody>
            {entities.map((entity) => (
                <NamedEntityRow
                    entity={entity}
                />
            ))}
            </tbody>
        </table>
    );
}