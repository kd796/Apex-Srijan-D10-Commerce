<template>
    <div :class="classes">
        <slot v-bind="{type}"></slot>
    </div>
</template>
<script>
    export default {
        props: {
            type: {
                type: String,
                default: 'info'
            },
        },
        computed: {
            classes() {
                return {
                    'notice': true,
                    [`notice--${this.type}`]: true,
                }
            }
        }
    }
</script>
