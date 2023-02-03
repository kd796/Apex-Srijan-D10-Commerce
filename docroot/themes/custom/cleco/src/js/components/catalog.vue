<template>
    <div :class="classes">
        <div class="catalog-sidebar" @click="handleSidebarClick" ref="sidebar">
            <div class="catalog-filters-container" @click.stop>
                <button type="button" class="a a--small a--close" @click="this.handleSidebarClick">Close</button>
                <slot name="sidebar" v-bind="scope"></slot>
            </div>
        </div>
        <div class="catalog-main" ref="main">
            <slot name="main" v-bind="scope"></slot>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import URI from 'urijs';
    import NetworkedComponentMixin from "../mixins/networked-component-mixin";

    let serialize = require('can-param');
    let unserialize = require('can-deparam');

    export default {
        mixins: [NetworkedComponentMixin],
        props: {
            page: {
                type: Number,
                default: 1,
            },
            perPage: Number,
            locale: {
                type: Object,
                required: true
            },
            // Translated alias 
            alias: {
                type: String,
                required: true
            },
            aggs: {
                type: Array,
                required: false
            },
            cmsTermFilterOrder: {
                type: String,
                required: false
            },
            translations: {
                type: Object,
                required: false
            }
        },
        data() {
            return {
                isFiltering: false,
                facets: {},
                protectedStateKeys: ['sort', 'page', 'perPage'],
                total: 0
            }
        },
        computed: {
            scope() {
                return {
                    bootstrapped: this.bootstrapped,
                    data: this.data,
                    sort: this.sort,
                    error: this.error,
                    appliedFilters: this.appliedFilters.filters,
                    isEmpty: this.isEmpty,
                    isFiltering: this.isFiltering,
                    toggleSidebar: this.toggleSidebar,
                    clearFilters: this.clearFilters,
                    currentQuery: this.currentQuery,
                    state: this.state,
                    total: this.total,
                    facets: this.facets,
                    query: this.query,
                    aggs: this.aggs ? this.aggs : [],
                    // Template uses Vue loop or Twig's loop for ordering filters
                    filtersOrder: this.filtersOrder ? this.filtersOrder : (this.aggs) ? this.aggs : null,
                }
            },
            classes() {
                return {
                    'catalog': true,
                    'catalog--filtering': this.isFiltering,
                }
            },
            appliedFilters() {
                let labels  = [];
                let filters = [];
                for (let key in this.state) {
                    if (this.state.hasOwnProperty(key) && this.state[key] !== null && !this.protectedStateKeys.includes(key)) {
                        let values = Array.isArray(this.state[key]) ? this.state[key] : [this.state[key]];
                        values.forEach((value, index) => {
                            if (value) {
                                labels.push(value);
                                filters.push({
                                    label: this.transformFilterValue(key, value),
                                    remove: () => {
                                        if (Array.isArray(this.state[key])) {
                                            this.state[key].splice(index, 1);
                                        }
                                        else {
                                            this.state[key] = null;
                                        }
                                    }
                                })
                            }
                        })
                    }
                }

                return { filters: filters, labels: labels };
            },
            availableFilters() {
                return this.$children.filter((child) => child.$options._componentTag === 'catalog-filter');
            },
            // Dynamic sort order based on active Product Categories
            filtersOrder() {

                if (!this.cmsTermFilterOrder) {
                    return false;
                }
                // CMS Product Categories term sort order
                let cmsTermFilterOrder = JSON.parse(this.cmsTermFilterOrder);

                // The order we'll use to sort filters based on active Product Categories
                let filterOrder = [];
                // Current active Product Categories
                let activeProductCategories = this.appliedFilters.labels;
                // // Get the CMS order for matching active Product Categories
                for (let category in activeProductCategories) {
                    if (activeProductCategories.hasOwnProperty(category)) {
                        let name = activeProductCategories[category];
                        if (cmsTermFilterOrder.hasOwnProperty(name)) {
                            // Unique array of filter values from all active Product Categories
                            filterOrder = Array.from(new Set(filterOrder.concat(cmsTermFilterOrder[name])));
                        }
                    }
                }

                let activeSort  = [];
                let defaultSort = [];

                // Sort filters if we have active filters in Product Category
                if (filterOrder.length > 0) {
                    // Create two arrays
                    // 1. Array we'll sort based on the active Product Category filters in "filterOrder", which is contolled at the term level in the CMS.
                    // 2. Array of the default order. The default order is controlled in the CMS at the Vocabulary level
                    for (let filter in this.aggs) {
                        if (this.aggs.hasOwnProperty(filter)) {
                            let index = filterOrder.findIndex(key => key.toLowerCase() == this.aggs[filter].name.toLowerCase()) 
                            if( index !== -1 ) {
                                activeSort.push(this.aggs[filter]);
                            }else{
                                defaultSort.push(this.aggs[filter]);
                            }
                        }
                    }

                    activeSort.sort((a, b) => {
                        
                        // @todo Check multi-lingual functionality
                        let x = filterOrder.findIndex(key => key.toLowerCase() == a.name.toLowerCase());
                        let y = filterOrder.findIndex(key => key.toLowerCase() == b.name.toLowerCase());

                        // Sort by index value in filterOrder
                        // If highter index, move it down
                        // If lower index, move it up
                        if (x > y) return 1;
                        if (x < y) return -1;

                    });


                    return activeSort.concat(defaultSort);
                }
                
                return false;
            }
        },
        methods: {
            updateUrl() {

                this.$nextTick(() => {
                    let urlState = this.transformModifiedUrlState(this.modifiedState);

                    let uri = new URI();

                    // Prevent duplicate params with multiple selections
                    uri._parts.query = null;    

                    // Cleaner params without "[]"
                    for (let state in urlState) {
                        let filterGroup = urlState[state];
                        if( Array.isArray(filterGroup) && filterGroup.length > 0) {
                            uri.addQuery(state, this.encodeFilters(filterGroup));
                        }else{
                           uri.addQuery(state, this.encodeFilters(filterGroup));
                        }
                    }

                    uri.addQuery('page', this.state.page);
                    uri.addQuery('perPage', this.state.perPage);
                    //uri.normalizeQuery();

                    if( uri._parts.query !== null ) {
                        // Prevent encoding commas for cleaner urls
                        //uri._parts.query = uri._parts.query.replace(/%2C/g, ',');
                        // Prevent double encoding
                        uri._parts.query = uri._parts.query.replace(/%252C/g, '%2C');
                    }

                    history.pushState(uri.query(true), null, uri);
                });
            },
            encodeFilters(filterGroup) {

                let newFilterGroup;

                if (Array.isArray(filterGroup)) {
                    // Encode commas in each filter value so we can use commas for multiple values
                    // @note we also encode the input value in catalog-filter.vue
                    // @note we decode %2C in catalog.html.twig
                    newFilterGroup = Object.keys(filterGroup).forEach((key) => {
                        filterGroup[key] = filterGroup[key].replace(/,/g, '%2C');
                    });
                    // Delimiter for multiple values
                    // Underscore seems to be the only safe value at this point in respect to the filter values
                    newFilterGroup = filterGroup.join('_');

                    return newFilterGroup; // Doesn't encode commas
                    //return newFilterGroup.replace(/,/g, '%2C'); // Encode commas
                }

                if (typeof filterGroup === 'string') {
                    return filterGroup.replace(/,/g, '%2C');
                }

                return filterGroup
            },
            toggleSidebar() {
                this.isFiltering = !this.isFiltering;
            },
            clearFilters() {
                this.state = Object.assign({}, this.initialState);
            },
            getFilter(slug) {
                let filters = this.availableFilters.filter((filter) => {
                    return filter.slug === slug;
                });

                if (filters.length > 0) {
                  return filters[0];
                }

                return null;
            },
            handleSidebarClick(e) {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            },
            transformState(state) {
                state.page = Number(state.page) || this.initialState.page;
                state.perPage = Number(state.perPage) || this.initialState.perPage;

                return state;
            },
        },
        created() {
            this.state.page = this.page;
            this.state.perPage = this.perPage;
        },
    }
</script>
