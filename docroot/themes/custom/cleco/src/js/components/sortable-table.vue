<template>
    <table>
        <slot name="header"></slot>
        <tbody>
        <sortable-table-row v-for="row in data">
            <slot v-bind:row="row"></slot>
        </sortable-table-row>
        </tbody>
        <tfoot>
            <slot name="footer"></slot>
        </tfoot>
    </table>
</template>
<script>
    export default {
        props: {
            data: {
                type: Array,
                default: [],
            }
        }
    }
</script>
