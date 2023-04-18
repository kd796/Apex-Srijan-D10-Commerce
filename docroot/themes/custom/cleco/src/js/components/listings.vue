<template>
    <ul :class="listingsClasses" v-if="items && items.length">
        <li class="listings-item" v-for="item,index in items" :key="index">
            <listing v-bind="item"></listing>
        </li>
    </ul>
</template>
<script>
    export default {
        props: {
            items: {
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
                    'listings': true,
                    'listings--loading': this.loading,
                }
            }
        }
    }
</script>
