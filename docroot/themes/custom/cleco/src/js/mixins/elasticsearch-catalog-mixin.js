let unserialize = require('can-deparam');

export default {
    methods: {
        transformInitialState() {
            let initialState = {};
            if (Array.isArray(this.filters)) {
                this.filters.forEach((filter) => {
                    initialState[filter] = (filter === "q") ? null : [];
                });
            } else {
                initialState = Object.assign({}, this.filters);
            }

            return initialState;
        },
        transformResponseTotal(data) {
            return data.hits.total;
        },
        transformResponseFacets(data) {
            let facets = {};
            for (let key in data.aggregations) {
                if (data.aggregations.hasOwnProperty(key)) {
                    facets[key] = [];
                    if (data.aggregations[key].buckets) {
                        data.aggregations[key].buckets.forEach((value) => {
                            let translatedValue = this.translate(value.key);
                            facets[key].push({
                                label: `${translatedValue} (${value.doc_count})`,
                                value: value.key,
                                count: value.doc_count
                            });
                        });
                    }
                }
            }

            return facets;
        },
        transformUrlState(queryString) {

            let state = unserialize(queryString);

            //let key = decodeURIComponent(key);
            for(let key in state) {
                if (state.hasOwnProperty(key)) {
                    if (state[key] && key !== "q") {
                        state[key] = state[key].split('_').map((val) => {
                            return val;//val.replace(/%2C/g, ',').replace(/%252C/g, ',');
                        });
                    }
                }
            }

            return state;
        },
        handleQueryError(error) {
            console.error(error);
            this.error = error.response.data;
        },
        translate(str) {
            // console.log('elasticsearch-catalog-mixin methods translate()', { str });
            let value = str;

            if (this.locale.code != 'en' && this.translations && this.translations.hasOwnProperty(str)) {
                value = this.translations[str][this.locale.code];
                // console.log('elasticsearch-catalog-mixin methods translate() translated', { str, lang: this.locale.code, value });
            }

            return value;
        },
    }
}
