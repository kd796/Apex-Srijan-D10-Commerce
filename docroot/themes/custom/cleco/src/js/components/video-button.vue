<template>
    <div class="video-thumb">
        <a :href="url" class="play-button" @click.prevent="play">Play</a>
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
