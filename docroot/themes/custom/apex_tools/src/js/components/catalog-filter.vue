<template>
    <accordion :title="title" :active="active" v-if="hasOptions">
        <p v-if="hasValue">
            <button type="button" class="a a--small" @click="clearSelection">{{ clearLabel }}</button>
        </p>
        <template v-if="type == 'checkboxes'">
            <ul v-if="options.length">
                <li v-for="option in sortOptions">
                    <label class="checkbox">
                        <input type="checkbox" class="checkbox-input" v-model="selected" :value="option.value.replace(/,/g, '%2C')">
                        <span class="checkbox-label">{{ option.label }}</span>
                    </label>
                </li>
            </ul>
        </template>
        <template v-if="type == 'range'">
            <div class="form-bar">
                <div class="form-bar-item form-bar-item--grow">
                    <float-label label="Min" type="number" v-model="min" element="throttled-input"></float-label>
                </div>
                <div class="form-bar-item form-bar-item--grow">
                    <float-label label="Max" type="number" v-model="max" element="throttled-input"></float-label>
                </div>
            </div>
        </template>
    </accordion>
</template>
<script>
    import NumberParser from "../helpers/number-parser";

    export default {
        props: {
            title: {
                type: String,
                default: '',
            },
            slug: {
                type: String,
                required: true,
            },
            type: {
                type: String,
                default: 'checkboxes',
            },
            range: {
                type: Array,
                default: () => { return [null, null] }
            },
            expanded: {
                type: Boolean,
                default: null,
            },
            options: {
                type: Array,
                default: () => {
                    return []
                },
            },
            clearLabel: {
                type: String,
                default: 'Clear'
            },
            value: {}
        },
        data() {
            return {
                selected: [],
                min: null,
                max: null,
            }
        },
        computed: {
            active() {
                let active = this.expanded;
                if (this.expanded === null) {
                    if (this.type === 'checkboxes') {
                        active = this.options.length > 0;
                    } else if (this.type === 'range') {
                        active = this.min !== null || this.max !== null;
                    } else {
                        active = true;
                    }
                }

                return active;
            },
            hasOptions() {
                return (this.type === 'range' && this.options.length > 0) || this.options.length > 0;
            },
            hasValue() {
                return this.computedValue !== null && this.computedValue.length > 0;
            },
            computedValue() {
                if (this.type === 'checkboxes') {
                    return this.selected;
                } else {
                    return this.min || this.max ? [this.min, this.max].join(',') : null;
                }
            },
            sortOptions() {
                return this.options.sort((a,b) => {
                    return NumberParser.sort(a.value, b.value);
                });
            }
        },
        methods: {
            clearSelection() {
                this.selected = [];
                this.min = null;
                this.max = null;
            },
            updateValue(newValue) {
                if (this.type === 'checkboxes') {
                    this.selected = newValue;
                } else {
                    if (newValue === null) {
                        this.min = null;
                        this.max = null;
                    } else {
                        let values = Array.isArray(newValue) ? newValue : newValue.split(',');

                        this.min = values[0] ? Number(values[0]) : this.range[0];
                        this.max = values[1] ? Number(values[1]) : this.range[1];
                    }
                }
            }

        },
        watch: {
            computedValue(newValue) {
                this.$emit('input', newValue);
            },
            value(newValue) {
                this.updateValue(newValue);
            }
        }
    }
</script>
