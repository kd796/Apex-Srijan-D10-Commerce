<template>
    <form method="GET" action="" @submit.prevent="handleSubmit">
        <slot v-bind="{state}"></slot>
    </form>
</template>
<script>
    export default {
        props: {
            value: {},
            filters: {},
        },
        data() {
            return {
                state: Object.assign({}, this.value),
            }
        },
        methods: {
            handleSubmit() {
                this.$emit("input", Object.assign({}, this.state));
            }
        },
        watch: {
            state: {
                deep: true,
                handler(newFilters) {
                }
            },
            value: {
                deep: true,
                handler(newValue) {
                    this.state = Object.assign({}, newValue);
                }
            }
        }
    }
</script>
