<template>
    <div class="video-thumb">
        <div class="video-thumb-image">
            <slot name="image"></slot>
        </div>
        <div class="video-thumb-details">
            <h3 class="video-thumb-title">{{ title }}</h3>
            <slot name="description"></slot>
        </div>
        <a :href="url" class="video-thumb-play" @click.prevent="play">Play</a>
        <video-player :url="url" :visible.sync="showPlayer"></video-player>
    </div>
</template>
<script>
    export default {
        props: {
            url: {
                type: String,
                required: true,
            },
            title: {
                type: String,
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
