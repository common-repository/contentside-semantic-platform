import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

export const NerClient = {
    fetchNamedEntities: (data) => {
        return apiFetch({
            path: '/csp-plugin/v1/ner/extract',
            method: 'POST',
            data: data
        })
    },
    fetchTagsForEntity: (entity) => {
        const queryParams = {
            page: 1,
            per_page: 10,
            context: "view",
            search: entity.entity
        }
        return apiFetch({
            path: addQueryArgs('/wp/v2/tags', queryParams),
            method: 'GET',
        })
    },
    getTagById: (tagId) => {
        return apiFetch({
            path: `/wp/v2/tags/${tagId}`,
            method: 'GET',
        })
    },
    createNewTagForEntity: async (entity) => {
        const tag = await apiFetch({
            path: '/wp/v2/tags',
            method: 'POST',
            data: {
                name: entity.entity,
            }
        });

        await NerClient.addTagToCSPDictionary(tag.id, entity.type);

        return tag
    },
    addTagToCSPDictionary: (tagId, entityType) => {
        return apiFetch({
            path: '/csp-plugin/v1/dictionary/add-entity',
            method: 'POST',
            data: {
                tagId: tagId,
                entityType: entityType
            }
        })
    },
    getToInsert: (entity) => {
        const toInsert = `<a href="${entity.url}" class="csp-plugin">${entity.entity}</a>`;
        const addedLength = toInsert.length - entity.entity.length;

        return {toInsert: toInsert, addedLength: addedLength};
    },
    insertEntityLink: (entity, postContent, entities) => {
        // Create the link
        const {toInsert, addedLength} = NerClient.getToInsert(entity);

        // Insert the link in the post content
        postContent = postContent.substring(0, entity.start_char + 1) + toInsert + postContent.substring(entity.end_char + 1);

        // Update the entities positions
        const updatedEntities = []
        for (let entityToCheck of entities) {
            if (entityToCheck.start_char > entity.start_char) {
                entityToCheck.start_char += addedLength;
                entityToCheck.end_char += addedLength;
            }
            // We also update the entity that has been selected
            if (entityToCheck.start_char === entity.start_char && entityToCheck.end_char === entity.end_char) {
                entityToCheck.selected = true;
            }
            updatedEntities.push(entityToCheck);
        }

        const orderedEntities = NerClient.orderEntities(updatedEntities);

        return {postContent: postContent, updatedEntities: orderedEntities};
    },
    orderEntities: (entities) => {
        // We reorder the entities by score
        entities.sort((a, b) => {
            const aScore = parseFloat(a.score.split('%')[0]);
            const bScore = parseFloat(b.score.split('%')[0]);
            return bScore - aScore;
        });

        return entities;
    },
    checkAndAdaptEntityPosition: (entity, postContent, step = 15) => {
        const {addedLength} = NerClient.getToInsert(entity);
        step = step + addedLength;
        const entityText = postContent.substring((entity.start_char) + 1, (entity.end_char + 1));

        const isPreciselyInPosition = entityText === entity.entity;
        if (isPreciselyInPosition) {
            return {start_char: entity.start_char, end_char: entity.end_char};
        }

        // We must try to find the entity in the surroundings of the position, as centered as possible
        let surroundings = NerClient.getEntitySurroundings(entity, postContent, step);
        let entityIndex = surroundings.indexOf(entity.entity);
        if (entityIndex === -1) {
            // We try decoding the HTML entities in the entity label returned by the NER
            const newEntity = NerClient.decodeEntities(entity.entity);
            let entityIndex = surroundings.indexOf(newEntity);

            if (entityIndex === -1) {
                console.log("Entity not found in the surroundings")
                return null;
            } else {
                entity.entity = newEntity;
            }
        }

        const start_char = entity.start_char - (step + 1) + entityIndex;
        const end_char = start_char + entity.entity.length;

        return {start_char: start_char, end_char: end_char};
    },
    getEntitySurroundings: (entity, postContent, step = 15) => {
        return postContent.substring((entity.start_char) - step, (entity.end_char + step));
    },
    isEntityLinked: (entity, postContent) => {
        // We get the text surrounding the entity (100 char before and after) and we search all the links in it
        // If we find the entity in the links, it means that it is already linked
        const entitySurroundings = NerClient.getEntitySurroundings(entity, postContent, 100);
        const regex = /(?<=<a href=".*?">)(.*?)(?=<\/a>)/g;
        const matches = entitySurroundings.match(regex);

        if (matches !== null
            && matches.length > 0) {
            for (let match of matches) {
                if (match === entity.entity) {
                    return true;
                }
            }
        }

        return false;
    },
    filterNotFoundEntitiesAndUpdatePositions(entities, postContent) {
        let filteredEntities = [];
        for (let entity of entities) {
            const startAndEnd = NerClient.checkAndAdaptEntityPosition(entity, postContent, 50);
            entity.selected = NerClient.isEntityLinked(entity, postContent);
            if (startAndEnd !== null) {
                filteredEntities.push({...entity, ...startAndEnd});
            }
        }
        return filteredEntities;
    },
    groupEntities(entities) {
        const groupedNamedEntities = {};
        entities.forEach((entity) => {
            if (groupedNamedEntities[entity.type] === undefined) {
                groupedNamedEntities[entity.type] = [];
            }
            groupedNamedEntities[entity.type].push(entity);
        });

        return Object.keys(groupedNamedEntities).sort().reduce(
            (obj, key) => {
                obj[key] = groupedNamedEntities[key];
                return obj;
            },
            {}
        );
    },
    getEntities(meta) {
        const namedEntities = meta['named_entities'];
        if (namedEntities === undefined || namedEntities === null || namedEntities === '') {
            return [];
        }
        return JSON.parse(namedEntities);
    },
    saveEntities(entities, meta, setMeta) {
        setMeta({
            ...meta, "named_entities": JSON.stringify(NerClient.orderEntities(entities))
        });
    },
    saveOneEntity(entity, meta, setMeta, entities = null) {
        if (entities === null) {
            entities = NerClient.getEntities(meta);
        }
        const updatedEntities = [];
        for (let entityToCheck of entities) {
            if (entityToCheck.start_char === entity.start_char && entityToCheck.end_char === entity.end_char) {
                entityToCheck = entity;
            }

            updatedEntities.push(entityToCheck);
        }
        NerClient.saveEntities(updatedEntities, meta, setMeta);
    },
    syncEntitiesWithPostContent(entities, postContent) {
        if (entities.length === 0) {
            return {updatedEntities: [], warning: false};
        }

        const updatedEntities = [];
        const invalidEntities = [];
        // Each time the content is updated, we update the position of the entities
        // and their status (selected or not)
        entities.forEach((entity) => {
            const selected = NerClient.isEntityLinked(entity, postContent);
            let newEntity = {...entity, selected: selected, selectedTag: selected};
            const startAndEnd = NerClient.checkAndAdaptEntityPosition(entity, postContent, 80);

            if (startAndEnd === null) {
                invalidEntities.push(entity);
            }

            if (startAndEnd) {
                newEntity = {...newEntity, ...startAndEnd};
            }
            updatedEntities.push(newEntity);
        });

        // If more than half of the entities are invalid, we must show a warning
        const warning = invalidEntities.length >= (entities.length / 2);

        return {updatedEntities: updatedEntities, warning: warning};
    },
    decodeEntities(str) {
        // this prevents any overhead from creating the object each time
        const element = document.createElement('div');

        if (str && typeof str === 'string') {
            // strip script/html tags
            str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
            str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
            element.innerHTML = str;
            str = element.textContent;
            element.textContent = '';
        }

        return str;
    }
}