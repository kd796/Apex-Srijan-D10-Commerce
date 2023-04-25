<template>
    <div :class="classes">
        <header class="accordion-header">
            <h4 class="accordion-title" v-html="title"></h4>
            <button type="button" class="accordion-toggle" @click="handleToggleClick">Toggle</button>
        </header>
        <div :class="bodyClasses">
            <slot v-bind="{active, title, toggle}"></slot>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            active: false,
            title: String,
            capped: {
                type: Boolean,
                default: true
            },
        },
        data() {
            return {
                state: this.active,
            };
        },
        computed: {
            classes() {
                return {
                    'accordion': true,
                    'accordion--active': this.state,
                }
            },
            bodyClasses() {
                return {
                    'accordion-body': true,
                    'accordion-body--capped': this.capped,
                }
            }
        },
        methods: {
            toggle(newState) {
                this.state = typeof newState === 'undefined' ? ! this.state : newState;
                this.$emit('update:active', this.state);
                this.$emit('toggle', this.state);
            },
            handleToggleClick(e) {
                e.preventDefault();
                this.toggle();
            }
        },
        watch: {
            active(newState) {
                this.toggle(newState);
            }
        }
    }
</script>
