<template>
    <div class="contain">
        <div v-if="otherProducts.length" class="other-products">
            <ul>
                <li v-for="(product, index) in otherProducts" :key="index" :class="{ 'active': product.id == productId }">
                    <a :href="`${product.url}#section--product-features`" :title="product.title">
                        <img :src="product.thumb" :alt="product.title">
                    </a>
                </li>
            </ul>
        </div>
        <div class="product-features-display feature-container explore-360">
            <div class="cd-product-viewer-wrapper" :data-frames="productImageFrames" data-friction="0.33" data-visible-frame="0" ref="cdProductViewerWrapper">
                <div class="rotate-point-holder">
                    <div v-for="(hotspot, index) in hotspots" :key="`A${index}`" :class="{ 'rotate-point': true, 'rotate-point-trigger': true, 'no-video': hotspot.media.bundle != 'video' }" :style="`top: ${hotspot.position.y}%; left: ${hotspot.position.x}%;`" :data-point="`${hotspot.type}-${index}`" :data-frame="hotspot.frame"></div>

                    <div v-for="(hotspot, index) in hotspots" :key="`B${index}`" :class="{ 'rotate-point-inner': true, 'no-video': hotspot.media.bundle != 'video' }" :id="`rotate-point-${hotspot.type}-${index}`">
                        <span class="close">x</span>
                        <div class="text">
                            <h4>{{ hotspot.text }}</h4>
                        </div>
                        <div class="video">
                            <video v-if="hotspot.media.bundle == 'video'" width="100%" loop>
                                <source :src="hotspot.media.src" :type="`video/${hotspot.media.ext}`">
                            </video>
                        </div>
                    </div>
                </div>
                <div>
                    <figure class="product-viewer">
                        <img class="loading-image" :src="images.loading" alt="Product Preview">
                        <div class="product-sprite" :data-image="images.product" ref="imageSprite"></div>
                    </figure>
                    <div class="cd-product-viewer-handle-wrapper">
                        <div class="cd-product-viewer-handle">
                            <span class="fill"></span>
                            <span class="handle">Handle</span>
                        </div>
                    </div>
                </div>
                <div id="curve"></div>
            </div>
        </div>

        <div class="product-features-copy">
            <h2>{{ productName }}</h2>
            <slot v-html="true"></slot>
            <h3>{{ translate('Explore the Features') }}</h3>
            <ul class="features-nav">
                <li v-for="(feature, index) in productFeatures" :key="index">
                    <button @click="setActiveFeature(index)" type="button" :class="{ 'active': activeFeature === index }" v-html="feature.icon.src"></button>
                </li>
            </ul>
            <p v-if="activeFeature !== false" class="feature-description">
                <strong>{{ productFeatures[activeFeature].title }}:</strong> {{ productFeatures[activeFeature].copy }}
            </p>
        </div>
    </div>
</template>

<script>
    import ElasticSearchCatalogMixin from "../mixins/elasticsearch-catalog-mixin";

    export default {
        mixins: [ElasticSearchCatalogMixin],
        props: {
            locale: {
                type: Object,
                required: true,
            },
            translations: {
                type: Object,
                required: false,
            },
            assets: {
                type: Array,
                default: [],
            },
            productName: {
                type: String,
                required: false,
            },
            productId: {
                type: String,
                required: false,
            },
            productFeatures: {
                type: Array,
                default: [],
            },
            hotspots: {
                type: Array,
                default: [],
            },
            images: {
                type: Object,
                default: () => ({}),
            },
            productImageFrames: {
                type: [String, Number],
                required: false,
                default: 24,
            },
            loadingImage: {
                type: String,
                required: false,
            },
            otherProducts: {
                type: Array,
                default: [],
            },
        },
        data() {
            return {
                activeFeature: false,
                showProductFeatures: false,
            }
        },
        methods: {
            spriteWidth() {
                const spriteWidth = this.productImageFrames * 100;
                this.$refs.imageSprite.style.width = `${spriteWidth}%`;
            },
            setActiveFeature(index) {
                if (this.activeFeature === index) {
                    this.activeFeature = false;
                    this.showProductFeatures = false;
                } else {
                    this.activeFeature = index;
                    this.showProductFeatures = true;
                }
            }
        },
        mounted() {
            this.spriteWidth();
        },
    }
</script>
