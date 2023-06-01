import axios from "axios";
import URI from "urijs";
import qs from "qs";

let serialize = require("can-param");
let unserialize = require("can-deparam");

export const NetworkedComponentMixin = {
    props: {
        api: String,
        filters: {
            default: function () {
                return [];
            },
        },
        requiredFilters: {
            type: Array,
            default: () => []
        },
    },
    data() {
        return {
            bootstrapped: false,
            initialState: {},
            state: this.transformInitialState(),
            data: [],
            error: null,
            currentQuery: null,
        }
    },
    computed: {
        querying() {
            return this.currentQuery !== null;
        },
        isEmpty() {
            return !this.error && !this.data.length;
        },
        modifiedState() {
            let modified = Object.assign({}, this.state);
            for (let key in modified) {
                if (modified.hasOwnProperty(key)) {
                    if (!modified[key] || !this.initialState.hasOwnProperty(key) || modified[key] == this.initialState[key]) {
                        delete modified[key];
                    }
                }
            }

            return modified;
        },
    },
    methods: {
        query() {
            if (!this.validateQuery()) {
                this.handleQueryValidationError();
            }

            this.error = null;
            let params = this.transformRequestParams(this.state, this);
            this.currentQuery = axios.get(this.api, {
                params: params,
                paramsSerializer: (params) => {
                    return qs.stringify(params, {arrayFormat: 'brackets'})
                }
                })
                .then(this.handleQueryResponse)
                .catch(this.handleQueryError)
                .finally(() => {
                    this.currentQuery = null;
                })
            this.updateUrl();
        },
        validateQuery() {
            return true;
        },
        updateUrl() {
            this.$nextTick(() => {
                let urlState = this.transformModifiedUrlState(this.modifiedState);
                history.pushState(urlState, null, URI().query("?" + serialize(urlState)).toString());
            });
        },
        handleQueryResponse(response) {
            this.data = this.transformResponseData(response.data);
            this.facets = this.transformResponseFacets(response.data);
            this.total = this.transformResponseTotal(response.data);

            if (this.bootstrapped) {
                this.$scrollTo(this.$refs.main);
            }

            this.bootstrapped = true;
        },
        handleQueryError(error) {
            console.error(error);
            this.error = error.response;
        },
        handleQueryValidationError() {

        },
        handleStateChange(state) {
            this.state = Object.assign(this.state, this.transformState(state));
        },
        transformRequestParams(params, vm) {
            return params;
        },
        transformResponseData(response) {
            if (response.included) {
                this.injectIncludedItems(response);
            }

            return response.data;
        },
        transformResponseFacets(data) {
            return data;
        },
        transformResponseTotal(data) {
            return data;
        },
        transformFilterValue(filter, value) {
            return value;
        },
        transformInitialState() {
            return this.transformInitialFilters();
        },
        transformState(state) {
            return state;
        },
        transformUrlState(queryString) {
            return unserialize(queryString.replace(/%2C/g, ',').replace(/%252C/g, ','));
        },
        transformModifiedUrlState(modifiedState) {
            return modifiedState;
        },
        setState(newState) {
            this.state = Object.assign({}, newState);
        },
        injectIncludedItems(response) {
            response.data.forEach((item) => {
                Object.keys(item.relationships).forEach((relationshipKey) => {
                    let relationship = item.relationships[relationshipKey];
                    if (relationship.data) {
                        let relationshipData = (Array.isArray(relationship.data)) ? relationship.data : [relationship.data];
                        relationshipData.forEach((relatedItem) => {
                            let includedItem = response.included.find((include) => {
                                return include.type === relatedItem.type && include.id === relatedItem.id;
                            });
                            if (includedItem) {
                                relatedItem.attributes = includedItem.attributes;
                            }
                        });
                    }
                });
            });
        },
        transformInitialFilters() {
            let initialFilters = {};
            if (Array.isArray(this.filters)) {
                this.filters.forEach(filter => {
                    if (filter.endsWith("[]") || filter.endsWith("%5B%5D")) {
                        initialFilters[filter.slice(0, -2)] = [];
                    }
                    else {
                        initialFilters[filter] = null;
                    }
                });
            }
            else {
                for (let key in this.filters) {
                    if (this.filters.hasOwnProperty(key)) {
                        initialFilters[key] = this.filters[key];
                    }
                }
            }

            return initialFilters
        }
    },
    watch: {
        state: {
            deep: true,
            handler() {
                this.query();
            },
        }
    },
    mounted() {
        this.initialState = Object.assign({}, this.state);

        let queryString = URI().search();
        if (queryString && queryString.length > 1) {
            let urlState = this.transformUrlState(queryString.slice(1));
            this.handleStateChange(urlState);
            if (Object.keys(this.modifiedState).length === 0) {
                this.query();
            }
        } else {
            if ((this.initialState.hasOwnProperty('page') && this.initialState.hasOwnProperty('perPage')) || (this.initialState.hasOwnProperty('radius') && this.initialState.q)) {
                this.query();
            }
        }

        window.addEventListener("popstate", (e) => {
            // Sends user back to previous page, not the filtered view
            history.back();
            // Pssible start solution to using history back/forward to update filtered view
            // if (e.state != null) {
            //     this.handleStateChange(e.state);
            // }else{
            //     history.back();
            // }
        });
    },
};

export default NetworkedComponentMixin;
