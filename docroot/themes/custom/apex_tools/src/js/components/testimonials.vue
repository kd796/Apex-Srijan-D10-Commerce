<template>
    <div class="testimonials testimonials--brand">
        <siema ref="slider" :current.sync="currentSlide" :options="options" class="testimonials-list">
            <slot></slot>
        </siema>
        <div class="testimonials-nav" v-if="totalSlides > 1 " >
            <button type="button" :class="getNavClasses('prev')" :disabled="!hasPrevious" @click="$refs.slider.prev()">&lt;</button>
            <button type="button" :class="getNavClasses('next')" :disabled="!hasNext" @click="$refs.slider.next()">&gt;</button>
        </div>
    </div>
</template>
<script>
    import SiemaWrapperMixin from "../mixins/siema-wrapper-mixin";

    export default {
        mixins: [SiemaWrapperMixin],
        props: {
            options: {
                type: [Object, Number],
                default: () => {
                    return {
                        perPage: 1
                    }
                },
                required: false
            }
        },
        methods: {
            getNavClasses(slug) {
                return {
                    "testimonials-button": true,
                    ["testimonials-button--" + slug]: true,
                }
            },
        },
    }
</script>
