<template>
    <div class="table-header-group">
        <slot></slot>
    </div>
</template>
<script>
    export default {
        props: ['groups'],
        data() {
            return {
                columnsWithoutHeadings: [],
            }
        },
        computed: {
            fragment() {
                let fragment = '<table><tr class="table-header-group">';
                for (let i = 1; i <= this.columnCount; i++) {
                    let spans = this.groups.filter(group => group.start === i);
                    if (spans.length) {
                        spans.forEach(group => {
                            fragment += `<th colspan="${group.span}">${group.name}</th>`;
                            i = i + group.span - 1;
                        })
                    } else {
                        fragment += `<th data-index="${i}"></th>`;
                        this.columnsWithoutHeadings.push(i);
                    }
                }
                fragment += '</tr>';

                return fragment;
            },
            columnCount() {
                let tr = document.querySelector(".table-component__table__body tr")

                return tr.querySelectorAll('td').length;
            },
        },
        mounted() {
            if (this.groups) {
                let self = this;
                this.$nextTick(() => {
                    let groupFragment = document.createRange().createContextualFragment(this.fragment).querySelector("tr");
                    let head = this.$el.querySelector(".table-component__table__head");
                    let headerRow = this.$el.querySelector(".table-component__table__head tr");
                    let groupRow = head.insertBefore(groupFragment, headerRow);

                    headerRow.querySelectorAll('th').forEach((cell, index) => {
                        if (this.columnsWithoutHeadings.includes(index + 1)) {
                            let emptyGroupCell = groupRow.querySelector(`th[data-index='${index + 1}']`);
                            groupRow.replaceChild(cell, emptyGroupCell);
                            cell.setAttribute('rowspan', 2);
                        }
                    })
                });
            }
        }
    }
</script>
