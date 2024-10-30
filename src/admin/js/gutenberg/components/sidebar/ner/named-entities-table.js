import {NamedEntityRow} from "./named-entity-row";
import {__} from "@wordpress/i18n";
import {Spinner} from '@wordpress/components';

export const NamedEntitiesTable = ({
                                       entities,
                                       loading,
                                       error,
                                       empty,
                                       withType = false,
                                       withAction = true
                                   }) => {
    return (
        <table className={"wp-list-table widefat fixed pages sortable "}>
            {loading && <tr>
                <td>
                    <div style={{textAlign: "center"}}><Spinner/></div>
                </td>
            </tr>}
            {error && <tr>
                <td>
                    <div>{__("An error occurred while fetching the named entities", "csp-plugin")}</div>
                </td>
            </tr>}
            {empty && <tr>
                <td>
                    <div>{__("No named entities found", "csp-plugin")}</div>
                </td>
            </tr>}
            {!loading && !error && !empty &&
                <>
                    <thead>
                    <tr>
                        <td style={{width: "120px"}}>{__("Entity", "csp-plugin")}</td>
                        {withType && (<td style={{width: "120px"}}>{__("Type", "csp-plugin")}</td>)}
                        <td style={{width: "50px"}}>{__("Score", "csp-plugin")}</td>
                        <td style={{width: "50px"}}>{__("Number of occurrences", "csp-plugin")}</td>
                        {withAction && (<td style={{width: "60px"}}>{__("Actions", "csp-plugin")}</td>)}

                    </tr>
                    </thead>
                    <tbody>
                    {entities.map((entity) => (
                        <NamedEntityRow
                            entity={entity}
                            withType={withType}
                            withAction={withAction}
                        />
                    ))}
                    </tbody>
                </>
            }

        </table>
    );
}