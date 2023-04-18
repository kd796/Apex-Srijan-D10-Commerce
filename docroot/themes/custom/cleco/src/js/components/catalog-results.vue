<template>
    <component :is="element" :class="listingsClasses" v-if="data && data.length">
        <template v-for="item,index in data">
            <slot v-bind="item"></slot>
        </template>
    </component>
</template>
<script>
    export default {
        props: {
            classPrefix: {
                type: String,
                default: 'listings',
            },
            element: {
                type: String,
                default: 'ul',
            },
            data: {
                type: Array,
                default: null,
            },
            loading: {
                type: Boolean,
                default: false,
            }
        },
        computed: {
            listingsClasses() {
                return {
                    [this.classPrefix]: true,
                    [this.classPrefix + '--loading']: this.loading,
                }
            }
        }
    }
</script>
