import {
    Spinner,
    CheckboxControl,
    Button
} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import {useSelect} from '@wordpress/data';

const ResultLine = ({
                        asTag,
                        taxonomyElement,
                        onChange,
                        currentTaxonomyElements
                    }) => {
    const [checked, setChecked] = useState(currentTaxonomyElements.includes(taxonomyElement.id));

    let parentCategory = {name: ""}
    if (taxonomyElement.parent !== 0) {
        parentCategory = useSelect((select) => (select('core').getEntityRecord('taxonomy', 'category', taxonomyElement.parent)));
    }

    return (
        <>
            {parentCategory !== undefined && (
                <>
                    <CheckboxControl
                        className={"float-left"}
                        checked={checked}
                        onChange={(newCheckStatus) => {
                            setChecked(newCheckStatus)
                            onChange(taxonomyElement, newCheckStatus)
                        }}
                    />
                    <strong>
                        {(!asTag && parentCategory.name !== "") ? parentCategory.name + " > " : ""}
                        {taxonomyElement.label}
                    </strong>
                </>)
            }
        </>
    );
}

export const CategoriesResultTable = ({
                                          asTags,
                                          isLoading,
                                          taxonomyElements,
                                          currentPost,
                                          setCurrentTaxonomyElements,
                                          currentTaxonomyElements
                                      }) => {

    const handleChange = (newTaxonomyElement, checked) => {
        const newTaxonomyElements = [...currentTaxonomyElements];
        if (checked) {
            newTaxonomyElements.push(newTaxonomyElement.id)
        } else {
            newTaxonomyElements.splice(newTaxonomyElements.indexOf(newTaxonomyElement.id), 1);
        }

        setCurrentTaxonomyElements(newTaxonomyElements);
    }

    return (
        <div className={"csp-plugin-modal-result-table-container"}>
            <table className={"wp-list-table widefat fixed pages sortable "}>
                {isLoading && <tr>
                    <td>
                        <div style={{textAlign: "center"}}><Spinner/></div>
                    </td>
                </tr>
                }

                {currentPost && (!isLoading && taxonomyElements.length > 0) ? taxonomyElements.map((taxonomyElement) => (
                    <tr>
                        <td>
                            <ResultLine
                                asTag={asTags}
                                taxonomyElement={taxonomyElement}
                                currentTaxonomyElements={currentTaxonomyElements}
                                onChange={handleChange}
                            />
                        </td>
                    </tr>
                )) : (!isLoading ? <tr>
                    <td><span>{__("No result", "csp-plugin")}</span>
                    </td>
                </tr> : <></>)
                }
            </table>
        </div>
    );
}