<template>
    <div class="pagination">
        <select class="pagination-per-page" v-model="per">
            <option :value="option" v-for="option in perPageOptions">{{ option }}</option>
        </select>

        <nav class="paginator" v-if="totalPages > 1">
            <button type="button" :class="previousClasses" @click="handlePreviousClick">&laquo;</button>

            <pagination-list v-model="page" :total-pages="totalPages"></pagination-list>

            <button type="button" :class="nextClasses" @click="handleNextClick">&raquo;</button>
        </nav>
    </div>
</template>
<script>
    export default {
        props: {
            value: Number,
            totalResults: Number,
            perPage: Number,
            perPageOptions: Array,
        },
        data() {
            return {
                page: this.value,
                per: this.perPage,
            }
        },
        computed: {
            totalPages() {
                return Math.ceil(this.totalResults / this.perPage);
            },
            hasPrevious() {
                return this.page > 1;
            },
            hasNext() {
                return this.page < this.totalPages;
            },
            previousClasses() {
                return this.getLinkClasses('previous', !this.hasPrevious);
            },
            nextClasses() {
                return this.getLinkClasses('next', !this.hasNext);
            },
        },
        methods: {
            getLinkClasses(type, disabled) {
                return {
                    'pagination-link': true,
                    [`pagination-link--${type}`]: true,
                    'pagination-link--disabled': disabled,
                }
            },
            handleNextClick() {
                if (this.hasNext) {
                    this.page = this.page + 1;
                }
            },
            handlePreviousClick() {
                if (this.hasPrevious) {
                    this.page = this.page - 1;
                }
            }
        },
        watch: {
            page(newValue) {
                this.$emit('input', this.page);
            },
            totalPages(newValue) {
                // If the total pages change, make sure we're on a page that's in range
                this.page = Math.max(1, Math.min(newValue, this.page));
            },
            value(newValue) {
                this.page = newValue;
            },
            per(newValue) {
                this.$emit('update:perPage', newValue);
                this.$emit('input', this.page);
            }
        }
    }
</script>
