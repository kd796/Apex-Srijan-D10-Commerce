<template>
    <div class="comparison-table" @click="handleClick">
        <header class="comparison-table-header">
            <div class="comparison-table-action">
                <button type="button" class="button button--primary button--simple" @click="handleCompare" :disabled="compareButtonDisabled">{{ compareButtonText }}</button>
            </div>
            <div class="comparison-table-units">
                <label for="units" class="comparison-table-units-label">{{ unitsLabel }}</label>
                <select v-model="selectedUnits" id="units" class="comparison-table-units-input">
                    <option v-for="(label, key) in unitsOptions" :value="key">{{ label }}</option>
                </select>
            </div>
        </header>
        <table-header-groups :groups="groups">
            <table-component
                class="comparison-table-table"
                :data="tableData"
                :sort-by="idKey + '.sort'"
                sort-order="asc"
                :show-filter="false"
                :show-caption="false"
                :cache-lifetime="0"
                @rowClick="handleRowClick"
            >
                <table-column :label="compareLabel" :sortable="false" :filterable="false">
                    <template slot-scope="row">
                        <label class="checkbox" :class="{'is-selected': isSelected(row)}">
                            <input type="checkbox" :value="getId(row)" class="checkbox-input" v-model="selected">
                            <span class="checkbox-label"></span>
                        </label>
                    </template>
                </table-column>
                <slot v-bind="{unitsValue}"></slot>
            </table-component>
        </table-header-groups>
    </div>
</template>
<script>
    import NumberParser from "../helpers/number-parser";

    export default {
        props: {
            groups: {
                type: Array,
                default: [],
            },
            data: {
                type: Array,
                default: [],
            },
            idKey: {
                type: String,
                default: null,
            },
            units: {
                type: String,
                default: 'imperial',
            },
            unitsLabel: {
                type: String,
                default: null,
            },
            compareLabel: {
                type: String,
                default: 'Compare',
            },
            compareBtnLabel: {
                type: Object,
                default: {
                    'singular': 'Compare Model',
                    'plural': 'Compare Models',
                    'langCode': 'en'
                }
            },
            unitsOptions: {
                type: Object,
                default: () => {
                    return {
                        imperial: 'Imperial',
                        metric: 'Metric',
                    }
                },
            }
        },
        data() {
            return {
                comparing: false,
                selectedUnits: this.units,
                selected: [],
            }
        },
        computed: {
            tableData() {
                let data = this.data;
                data = this.transformTableData(data);
                data = this.addNumberParserValues(data);
                data = this.mapUnitsValues(data);
                data = this.filterForComparison(data);

                return data;
            },
            compareButtonText() {
                if (!this.comparing) {

                    let label;
                    let increment = this.selected.length ? '(' + this.selected.length + ')' : '';

                    switch(this.compareBtnLabel.langCode) {
                        case 'de':
                            label = `${increment} ${this.compareBtnLabel.singular}`;
                            if (this.selected.length !== 1) {
                                label = `${increment} ${this.compareBtnLabel.plural}`;
                            }
                        break;
                        default:
                            label = `${this.compareBtnLabel.singular} ${increment}`;
                            if (this.selected.length !== 1) {
                                label = `${this.compareBtnLabel.plural} ${increment}`;
                            }
                        break;
                    }

                    return label;
                } else {
                    return 'View All';
                }
            },
            compareButtonDisabled() {
                return ! this.comparing && this.selected.length === 0;
            }
        },
        methods: {
            getId(row) {
                return this.idKey ? row[this.idKey].display : null;
            },
            isSelected(row) {
                return this.selected.includes(this.getId(row));
            },
            filterForComparison(data) {
                if (this.comparing) {
                    return data.filter((row) => {
                        return this.selected.includes(this.getId(row));
                    });
                }

                return data;
            },
            addNumberParserValues(data) {
                return data.map((row) => {
                    for (let key in row) {
                        if (row.hasOwnProperty(key)) {
                            let column = row[key];
                            // console.log('comparison-table methods addNumberParserValues()', { column, row, key });
                            if (Array.isArray(column)) {
                                column = column.join(' ');
                                column = {
                                    display: column,
                                    sort: (new NumberParser(column)).parse(),
                                };
                            } else if (typeof column === 'object') {
                                for (let prop in column) {
                                    if (column.hasOwnProperty(prop)) {
                                        let unitsValue = column[prop];
                                        if (Array.isArray(unitsValue)) {
                                            unitsValue = unitsValue.join(' ');
                                        }

                                        column[prop] = {
                                            display: unitsValue,
                                            sort: (new NumberParser(unitsValue)).parse()
                                        }
                                    }
                                }
                            } else {
                                if (key === 'top_seller' && column === 'Yes') {
                                    column = {
                                        display: '<img src="/themes/cleco/dist/img/top-seller.png">',
                                        sort: (new NumberParser(column)).parse(),
                                    }
                                } else {
                                    column = {
                                        display: column,
                                        sort: (new NumberParser(column)).parse(),
                                    }
                                }
                            }

                            row[key] = column;
                        }
                    }

                    return row;
                });
            },
            mapUnitsValues(data) {
                return data.map((row) => {
                    for (let cell in row) {
                        if (row.hasOwnProperty(cell)) {
                            if (row[cell] !== null && typeof row[cell] === 'object' && row[cell].hasOwnProperty(this.selectedUnits)) {
                                row[cell] = row[cell][this.selectedUnits];
                                //row[cell] = row[cell][this.units] ? row[cell][this.units] : 'n/a';
                            }
                        }
                    }

                    return row;
                });
            },
            transformTableData(data) {
                return JSON.parse(JSON.stringify(data));
            },
            handleRowClick(row) {
                //if (row.data.hasOwnProperty(this.idKey)) {
                //    let id = this.getId(row.data);
                //    if (this.selected.includes(id)) {
                //        this.selected.splice(this.selected.indexOf(id), 1);
                //    } else {
                    //    this.selected.push(id);
                  //  }
                //}
            },
            handleCompare(e) {
                this.comparing = ! this.comparing;
                this.highlightSelected();
            },
            unitsValue(row) {
                if (row.hasOwnProperty(this.selectedUnits)) {
                    return row[this.selectedUnits];
                }

                return row;
            },
            highlightSelected() {
                this.$nextTick(() => {
                    this.$el.querySelectorAll('.table-component__table__body tr').forEach((row) => {
                        row.classList.toggle('is-selected', row.querySelector(".checkbox.is-selected"));
                    });
                });
            },
            handleClick(e) {
                if (e.target.nodeName === 'TH') {
                    this.highlightSelected();
                }
            }
        },
        watch: {
            selected() {
                this.highlightSelected();
            },
        }
    }
</script>
