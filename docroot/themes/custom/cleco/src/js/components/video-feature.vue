<template>
    <figure class="media-feature">
        <a :href="url" class="media-feature-link media-feature-link--video" @click.prevent="play">
            <slot name="image"></slot>
        </a>
        <figcaption class="media-feature-caption">
            <slot name="description"></slot>
        </figcaption>
        <video-player :url="url" :visible.sync="showPlayer"></video-player>
    </figure>
</template>
<script>
    export default {
        props: {
            url: {
                type: String,
                required: true,
            },
        },
        data() {
            return {
                showPlayer: false,
            }
        },
        methods: {
            play() {
                this.showPlayer = true;
            }
        }
    }
</script>
