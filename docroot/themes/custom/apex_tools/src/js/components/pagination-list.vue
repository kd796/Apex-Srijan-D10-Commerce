<template>
    <ul class="pagination-list">
        <li class="pagination-list-item" v-for="page in pageNumbers">
            <button type="button" :class="getLinkClasses(page)" v-if="page !== null" @click="setPage(page)">{{ page }}</button>
            <span class="pagination-break" v-if="page === null">&hellip;</span>
        </li>
    </ul>
</template>
<script>
    export default {
        props: {
            value: Number,
            totalPages: Number,
            visibleAdjacent: {
                type: Number,
                default: 2,
            },
        },
        data() {
            return {
                page: this.value,
            }
        },
        computed: {
            pageNumbers() {
                let pageNumbers = [];
                for (let number = 1; number <= this.totalPages; number++) {
                    // Show the first, last, and visible adjacent
                    if (number === 1 || number === this.totalPages || Math.abs(number - this.page) <= this.visibleAdjacent) {
                        pageNumbers.push(number);
                    }

                    // Add the dots on either side of the visible adjacent
                    if (Math.abs(number - this.page) === this.visibleAdjacent + 1 && (number > 1 && number < this.totalPages)) {
                        pageNumbers.push(null);
                    }
                }

                return pageNumbers;
            }
        },
        methods: {
            getLinkClasses(page) {
                return {
                    'pagination-link': true,
                    'pagination-link--active': this.page === page,
                }
            },
            setPage(page) {
                this.page = page;
            }
        },
        watch: {
            page(newValue) {
                this.$emit('input', newValue);
            },
            value(newValue) {
                this.page = newValue;
            }
        }
    }
</script>
