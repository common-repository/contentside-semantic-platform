import {useEffect} from "react";
import {NerClient} from "../../../clients/ner-client";
import {useState} from '@wordpress/element';
import {PanelBody, PanelRow} from '@wordpress/components';
import {NamedEntitiesTable} from "./named-entities-table";
import {__} from "@wordpress/i18n";
import {CapabilitiesService} from "../../../services/capabilities-service";
import {useSelect} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';

const getUniqueAndCountedEntities = (entities, areCandidates = false, sort = false) => {
    let filteredEntities = entities
        .filter((entity) => entity.is_candidate === areCandidates)

    if (sort) {
        filteredEntities.sort((a, b) => b.score - a.score)
    }

    const countedEntities = filteredEntities
        // We want to count how many times an entity is found
        .map((entity) => {
            entity.count = filteredEntities.filter((e) => e.entity === entity.entity).length
            return entity;
        })

    return countedEntities
        // We want only one instance of entity based on entity.entity
        .filter((entity, index, self) => self.findIndex((e) => e.entity === entity.entity) === index);
}

const getEntityData = (setLoading, setError, setEmpty, setCandidates, setKnownEntities, postContent) => {
    setLoading(true);
    setError(null);
    // We first fetch with basic and smart at the same time, then we merge the results
    Promise.all([
        NerClient.fetchNamedEntities({
            content: postContent,
            matcher: "csp_smart_matcher"
        }),
        NerClient.fetchNamedEntities({
            content: postContent,
            matcher: "csp_basic_matcher"
        })
    ])
        .then(([
                   {
                       entities: entities1,
                       overlap: overlap,
                       article: article
                   }, {entities: entities2}
               ]) => {

            const uniqueEntities = [];
            // We add all the entities from the smart matcher
            Object.values(entities1).forEach((entity) => uniqueEntities.push(entity));
            // We add all the entities from the basic matcher if they are not already present in the smart matcher
            Object.values(entities2).forEach((entity) => {
                if (Object.values(entities1).filter((e) => e.id === entity.id).length === 0) {
                    uniqueEntities.push(entity);
                }
            });

            return ({
                entities: Object.values(uniqueEntities),
                overlap: overlap,
                article: article
            })
        })
        .then(({entities, overlap, article}) => {
            if (entities.length === 0) {
                setEmpty(true);
                return;
            } else {
                setEmpty(false);
            }

            setCandidates(getUniqueAndCountedEntities(entities, true, true));
            setKnownEntities(getUniqueAndCountedEntities(entities, false));

            setLoading(false);
        })
        .catch((error) => {
            console.error(error);
            setError(error);
            setLoading(false);
        });
}

export const NamedEntitiesPanels = ({postContent}) => {
    const [loading, setLoading] = useState(true);
    const [empty, setEmpty] = useState(false);
    const [error, setError] = useState(null);
    const [isUserAllowedToSeeCandidates, setIsUserAllowedToSeeCandidates] = useState(false);

    const [candidates, setCandidates] = useState([]);
    const [knownEntities, setKnownEntities] = useState([]);

    useEffect(() => {
        CapabilitiesService
            .isCurrentUserAllowedTo("csp_plugin.semantic_platform.entities.add_suggested")
            .then((isAllowed) => {
                setIsUserAllowedToSeeCandidates(isAllowed);
            });

        getEntityData(setLoading, setError, setEmpty, setCandidates, setKnownEntities, postContent);
    }, []);

    return (
        <>
            {isUserAllowedToSeeCandidates === true &&
                <PanelBody
                    title={__("Named entities candidates", "csp-plugin")}
                    initialOpen={true}
                >
                    <PanelRow>
                        <NamedEntitiesTable
                            entities={candidates}
                            withType={true}
                            loading={loading}
                            error={error}
                            empty={empty}
                        />
                    </PanelRow>
                </PanelBody>
            }

            <PanelBody
                title={__("Known named entities", "csp-plugin")}
                initialOpen={true}
            >
                <PanelRow>
                    <NamedEntitiesTable
                        entities={knownEntities}
                        withAction={false}
                        loading={loading}
                        error={error}
                        empty={empty}
                    />
                </PanelRow>
            </PanelBody>
        </>
    );
}