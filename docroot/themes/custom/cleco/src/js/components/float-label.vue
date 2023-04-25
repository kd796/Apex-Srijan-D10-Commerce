<template>
    <div :class="classes" v-on="$listeners">
        <label :for="inputId" class="float-label-label">{{ label }}<span class="required-indicator" v-if="required !== undefined">*</span></label>
        <component :is="element" :value="value" v-bind="inputAttributes" @input="handleInput" @focus="handleFocus" @blur="handleBlur" v-text="typed" :rows="rows" :maxlength="maxlength"></component>
    </div>
</template>
<script>
    export default {
        props: {
            id: {
                type: String,
            },
            type: {
                type: String,
                default: 'text',
            },
            element: {
                type: String,
                default: 'input',
            },
            label: {
                type: String,
            },
            placeholder: {
                type: String,
                default: '',
            },
            required: {},
            value: {},
            rows: {
                default: null
            },
            maxlength: {
                default: null
            }
        },
        data() {
            return {
                focused: false,
                typed: this.value,
            }
        },
        computed: {
            inputId() {
                return this.id || this._uid;
            },
            inputAttributes() {
                return {
                    id: this.inputId,
                    type: this.element == 'textarea' ? null : this.type,
                    placeholder: this.placeholder,
                    class: 'float-label-input',
                    required: (this.required !== undefined),
                    value: this.value,
                }
            },
            classes() {
                return {
                    'float-label': true,
                    'float-label--focused': this.focused,
                    'float-label--textarea': this.element == 'textarea',
                    'float-label--floated': !!(this.focused || this.typed),
                };
            }
        },
        methods: {
            handleFocus() {
                this.focused = true;
            },
            handleBlur() {
                this.focused = false;
            },
            handleInput(e) {
                this.typed = typeof e === Event ? e.target.value : e;
            }
        },
        watch: {
            typed(newValue) {
                this.$emit('input', newValue);
            },
            value(newValue) {
                this.typed = newValue;
            }
        }
    }
</script>
