<template>
    <transition>
        <div class="video-player" v-if="visible" @click.prevent="close">
            <button type="button" @click="close" class="video-player-close">&times;</button>
            <div class="video-player-content">
                <div class="video-player-branding"></div>
                <div class="video-embed">
                    <iframe :src="embedUrl" frameBorder="0" allowFullScreen="allowFullScreen"></iframe>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
    export default {
        props: {
            url: {
                type: String,
                required: true,
            },
            visible: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            show() {
                return this.visible;
            },
            embedUrl() {
                return `https://www.youtube.com/embed/${this.getVideoId(this.url)}?rel=0&autoplay=1`;
            },
        },
        methods: {
            getVideoId(url) {
                var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
                var match = url.match(regExp);

                return (match && match[7].length == 11) ? match[7] : false;
            },
            close() {
                this.$emit('update:visible', false);
                this.$emit('close');
            },
        },
    }
</script>
