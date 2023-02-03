<template>
    <div class="transparent-image" :class="classes">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" :width="width" :height="height">
            <defs>
                <filter :id="filterId">
                    <feFlood result="bg" x="0" y="0" width="100%" height="100%" :flood-color="backgroundColor" flood-opacity="1"/>
                    <feBlend in="SourceGraphic" in2="bg" mode="multiply"/>
                </filter>
            </defs>

            <template v-if="bootstrapped">
                <image v-bind="{'xlink:href': src}" x="0" y="0" width="100%" height="100%" :filter="filterUrl" @error="handleError" @load="handleLoad" />
            </template>
        </svg>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                exists: true,
                bootstrapped: false,
            }
        },
        props: {
            src: {
                type: String,
                required: true,
            },
            width: {
                default: 300,
            },
            height: {
                default: 200,
            },
            backgroundColor: {
                type: String,
                default: '#f7f7f7',
            },
        },
        computed: {
            filterId() {
                return 'transparent-' + this._uid;
            },
            filterUrl() {
                return this.bootstrapped ? `url('#${this.filterId}')` : null;
            },
            classes() {
                return {
                    'status--found': this.exists,
                    'status--not-found': !this.exists,
                }
            }
        },
        methods: {
            handleError(e) {
                this.exists = false;
            },
            handleLoad(e) {
                this.exists = true;
            },
        },
        mounted() {
            this.bootstrapped = true;
        }
    }
</script>
