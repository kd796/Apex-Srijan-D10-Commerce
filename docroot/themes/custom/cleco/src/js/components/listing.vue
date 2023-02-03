<template>
    <component :is="tag" :class="classes" :href="url" :target="target">
        <span class="listing-flag" v-if="flag !== null">{{ flag }}</span>
        <div class="listing-image">
            <transparent-image :src="image" class="listing-image-img" width="300" height="400" v-if="image"></transparent-image>
            <svg class="listing-image-img" v-else="!image"></svg>
        </div>
        <div class="listing-content">
            <h3 class="listing-title" v-if="title" v-bind="titleAttributes" v-html="title"></h3>
            <div class="listing-body" v-if="(hasBody) && ! peek">
                <p v-if="body" v-html="body"></p>
                <p class="listing-meta" v-if="meta">{{ meta }}</p>
                <a :href="url" :target="target" class="listing-action" v-if="url && action">{{ action }}</a>
            </div>

            <div class="listing-overlay" v-if="peek">
                <h3 class="listing-title" v-if="title" v-html="title"></h3>
                <div class="listing-body" v-if="hasBody">
                    <p v-if="body" v-html="body"></p>
                    <p class="listing-meta" v-if="meta">{{ meta }}</p>
                    <a :href="url" :target="target" class="listing-action" v-if="url && action">{{ action }}</a>
                </div>
            </div>
        </div>
    </component>
</template>
<script>
    export default {
        props: {
            size: {
                type: String,
                default: null,
            },
            order: {
                type: String
            },
            url: {
                type: String,
                default: null,
            },
            target: {
                type: String,
                default: '_top',
            },
            title: {
                type: String,
                default: null,
            },
            body: {
                type: String,
                default: null,
            },
            meta: {
                type: String,
                default: null,
            },
            action: {
                type: String,
                default: null,
            },
            peek: false,
            flag: {
                type: String,
                default: null,
            },
            image: {
                type: String,
                default: null,
            },
        },
        computed: {
            tag() {
                return this.url && ! this.action ? 'a' : 'div';
            },
            classes() {
                return {
                    listing: true,
                    [`listing--${this.size}`]: this.size !== null,
                    [`listing--peek`]: this.peek === true,
                }
            },
            titleAttributes() {
                let attrs = {};
                if (this.peek === true) {
                    attrs['aria-hidden'] = 'true';
                    attrs['role'] = 'presentation';
                }

                return attrs;
            },
            hasBody() {
                return this.body || this.meta || this.action;
            }
        }
    }
</script>
