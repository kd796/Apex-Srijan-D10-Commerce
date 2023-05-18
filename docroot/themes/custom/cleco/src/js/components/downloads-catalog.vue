<script>
    import Catalog from './catalog';
    import ElasticSearchCatalogMixin from "../mixins/elasticsearch-catalog-mixin";

    export default {
        extends: Catalog,
        mixins: [ElasticSearchCatalogMixin],
        methods: {
            transformResponseData(data) {
                return data.hits.hits.map((item) => {
                    let download = item._source;
                    let url = this.getUrl(download);
                    let filepath = (download.assets.source_to_jpg != undefined) ? download.assets.source_to_jpg : download.assets.pro_tools_jpg_of_pdf;
                    let image = filepath ? filepath : '';

                    return {
                        id: download._id,
                        title: download.name,
                        image: image,
                        body: download.values.sku_overview || '',
                        meta: download.product_category.join(', ')  || '',
                        url: url,
                    }
                });
            },
            transformFilterValue(filter, value) {
                if (filter === 'q') {
                    value = this.translate('Search for “@query”').replace('@query', value);
                }

                return this.translate(value);
            },
            getUrl(download) {
                const id             = download.id;
                const type           = download.type;
                const assets         = download.assets;
                const values         = download.values;

                const filename       = values.asset_filename || assets.original_source_file || assets.pro_tools_pdf;
                const imgSrcPrimeOne = assets.imagesourceprime1;
                const ext            = filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
                let   source         = undefined;

                switch (type.toLowerCase()) {
                    case '3d model':
                        source = values['asset_filename'] || assets['3d_model_igs'] || assets['3d_model'] || filename;
                    break;
                    default:
                        source = (ext.toLowerCase() === 'tif') ? imgSrcPrimeOne : filename;
                    break;
                }

                return source;
            }
        }
    }
</script>
