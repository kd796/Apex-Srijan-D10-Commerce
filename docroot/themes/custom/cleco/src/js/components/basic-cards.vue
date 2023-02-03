<template>
    <div class="basic-cards-slider" :class="{'basic-cards-slider--init': mounted}">
        <button type="button" :class="getNavClasses('prev')" :disabled="!hasPrevious" @click="$refs.slider.prev()">&laquo;</button>
        <siema ref="slider" :current.sync="currentSlide" :options="options" class="basic-cards">
            <slot></slot>
        </siema>
        <button type="button" :class="getNavClasses('next')" :disabled="!hasNext" @click="$refs.slider.next()">&raquo;</button>
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
                        perPage: {
                          800: 2,
                          1000: 3,
                          1200: 4
                        }
                    }
                },
                required: false
            }
        },
        methods: {
            getNavClasses(slug) {
                return {
                    'basic-cards-nav': true,
                    ['basic-cards-nav--' + slug]: true,
                }
            }
        },
    }
</script>
