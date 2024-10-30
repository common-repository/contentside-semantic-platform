import apiFetch from '@wordpress/api-fetch';

export const TaxonomyClient = {
    fetchRecommendedTaxonomy: (data, asTags) => {
        return apiFetch({
            path: (asTags ? '/csp-plugin/v1/tag' : '/csp-plugin/v1/categorize'),
            method: 'POST',
            data: data
        })
    }
}