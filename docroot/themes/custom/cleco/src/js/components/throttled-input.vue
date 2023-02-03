<template>
    <input v-model="typed" @keyup.enter.prevent="handleEnter" @blur="handleBlur" @focus="propagate" @input.stop>
</template>
<script>
    export default {
        props: ['value'],
        data() {
            return {
                typed: this.value,
                saved: this.value,
            }
        },
        methods: {
            handleEnter() {
                this.saved = this.typed;
                this.$el.blur();
            },
            handleBlur(e) {
                this.saved = this.typed;
                this.propagate(e);
            },
            propagate(e) {
                this.$emit(e.type, e);
            }
        },
        watch: {
            saved(value) {
                this.$emit('input', value);
            },
            value(newValue) {
                this.typed = newValue;
                this.saved = newValue;
            }
        }
    }
</script>
