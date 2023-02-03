<script>
    import Catalog from './catalog';
    import ElasticSearchCatalogMixin from "../mixins/elasticsearch-catalog-mixin";

    export default {
        extends: Catalog,
        mixins: [ElasticSearchCatalogMixin],
        methods: {
            transformInitialState() {
                let initialState = {};
                this.filters.forEach((filter) => {
                    initialState[filter] = (filter === "q") ? null : [];
                });

                return initialState;
            },
            transformResponseData(data) {
                return data.hits.hits.map((item) => {
                    let product = item._source;
                    let thumb = null;
                    if( product.assets ) {
                        let assets = Array.isArray(product.assets) ? product.assets : Object.keys(product.assets);
                        assets.map( (asset) => {
                            if( asset.type === 'Primary Image') {
                                thumb = process.env.MIX_STEP_DIR + 'styles/thumb/' + asset.id + '.jpg';
                            }
                        });
                    }

                    return {
                        id: item._id,
                        title: product.coupon_headline || product.name,
                        body: product.values.sku_overview || '',
                        url: this.locale.path + this.alias + product.slug,
                        image: thumb,
                        order: product.values.web_display_sort_order
                    }
                });
            },
            transformFilterValue(filter, value) {
                if (filter === 'q') {
                    value = this.translate('Search for “@query”').replace('@query', value);
                }

                let catalogFilter = this.getFilter(filter);
                if (catalogFilter && catalogFilter.type === 'range') {
                    let values = value.split(',');
                    let min = values[0] || null;
                    let max = values[1] || null;

                    value = catalogFilter.title + ': ';
                    if (min) {
                        value += min;
                    } else {
                        if (max) {
                            value += 'Up to '
                        }
                    }

                    if (max) {
                        if (min) {
                            value += '-';
                        }

                        value += max;
                    } else {
                        value += '+';
                    }
                }

                return this.translate(value);
            },
        }
    }
</script>
