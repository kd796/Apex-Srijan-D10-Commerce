<script>
    import Catalog from "./catalog";
    import ElasticSearchCatalogMixin from "../mixins/elasticsearch-catalog-mixin";

    let unserialize = require('can-deparam');

    export default {
        extends: Catalog,
        mixins: [ElasticSearchCatalogMixin],
        methods: {
            transformResponseData(data) {
                return data.hits.hits.map((item) => {
                    let result = item._source;
                    let body = null;
                    let thumb = null;
                    let url = null;

                    switch (item._type) {
                        case "downloads" :
                            let asset =  result.assets['3d_model_igs'] || result.assets['3d_model'] || result.assets.original_source_file;

                            url = process.env.MIX_STEP_DIR + asset;
                            thumb = process.env.MIX_STEP_DIR + 'styles/thumb/' + result.assets.pro_tools_jpg_of_pdf;
                            break;

                        case "products" :
                            url = this.locale.path + this.alias + result.slug;
                            body = result.values.sku_overview || null;
                            thumb = result.assets.reduce(function (thumb, asset) {
                                if (asset.type === 'Primary Image') {
                                    return process.env.MIX_STEP_DIR + 'styles/thumb/' + asset.source_to_jpg;
                                }

                                return thumb;
                            }, thumb);
                            break;

                        case "nodes" :
                            url = result.slug;
                            if (item.highlight) {
                                let firstHighlightKey = Object.keys(item.highlight)[0];
                                body = item.highlight[firstHighlightKey].join('…');
                            }
                            break;
                    }

                    return {
                        id: item._id,
                        title: result.values.coupon_headline || result.name,
                        type: this.translate(result.type),
                        body: body,
                        url: url,
                        image: thumb
                    }
                });
            },
            handleQueryError(error) {
                console.error(error);
                this.error = error.response.data;
            },
            transformUrlState(queryString) {
                let state = unserialize(queryString);
                for(let key in state) {
                    if (state.hasOwnProperty(key)) {
                        if (state[key] && key !== "q") {
                            state[key] = state[key].split(',').map((val) => {
                                return val.replace(/%2C/g, ',').replace(/%252C/g, ',');
                            });
                        }
                    }
                }

                return state;
            },
            transformModifiedUrlState(modifiedState) {
                modifiedState.q = this.initialState.q;

                return modifiedState;
            }
        },
        created() {
            this.protectedStateKeys.push("q");
        }
    }
</script>
