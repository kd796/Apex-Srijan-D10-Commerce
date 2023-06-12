<template>
    <div class="zoomable">
        <a :href="full" @click.prevent="toggleLightbox" class="zoomable-trigger">
            <img :src="src" :alt="alt" class="zoomable-img">
        </a>
        <transition>
            <div class="zoomable-lightbox" v-if="lightbox">
                <template v-if="full">
                    <draggable class="zoomable-lightbox-image" ref="lightboxImgWrapper">
                        <img :src="full" :alt="alt" class="zoomable-lightbox-img" ref="lightboxImg" :style="lightboxStyle">
                    </draggable>
                </template>
                <template v-else>
                    <slot v-bind="{zoom}"></slot>
                </template>
                <div class="zoomable-close-control">
                    <button type="button" @click="close" class="zoomable-close-button">&times;</button>
                </div>
                <div class="zoomable-scale-control">
                    <button type="button" class="zoomable-scale-button zoomable-scale-button--out" @click="zoomOut" @touchstart.prevent="zoomOut" :disabled="!canZoomOut">-</button>
                    <button type="button" class="zoomable-scale-button zoomable-scale-button--in" @click="zoomIn" @touchstart.prevent="zoomIn" :disabled="!canZoomIn">+</button>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
    import EventBus from '../event-bus';

    export default {
        props: {
            src: {
                type: String,
                required: true,
            },
            full: {
                type: String,
                required: false,
            },
            alt: {
                type: String,
                required: true,
            },
            scale: {
                type: Array,
                default: () => {
                    return [
                        0.5,
                        1,
                        2,
                        3,
                    ];
                }
            }
        },
        data() {
            return {
                lightbox: false,
                zoomIndex: 0,
            }
        },
        computed: {
            zoom() {
                return this.scale[this.zoomIndex];
            },
            lightboxStyle() {
                return `transform: translate(-50%, -50%) scale(${this.zoom})`;
            },
            canZoomIn() {
                return this.zoomIndex < this.scale.length - 1;
            },
            canZoomOut() {
                return this.zoomIndex > 0;
            }
        },
        methods: {
            toggleLightbox() {
                this.lightbox = !this.lightbox;
            },
            zoomIn() {
                this.zoomIndex = this.constrain(this.zoomIndex + 1, 0, this.scale.length - 1);
            },
            zoomOut() {
                this.zoomIndex = this.constrain(this.zoomIndex - 1, 0, this.scale.length - 1);
            },
            constrain(value, min, max) {
                value = Math.max(value, min);
                value = Math.min(value, max);

                return value;
            },
            close() {
                this.lightbox = false;
                this.zoomIndex = 0;
            },
        },
        watch: {
            lightbox(value) {
                EventBus.$emit('captive', value);
            }
        }
    }
</script>
