<template>
    <transition>
        <div :id="id" class="carousel-item" v-if="active">
            <slot></slot>
        </div>
    </transition>
</template>
<script>
    export default {
        props: ['id', 'thumb'],
        data() {
            return {
                active: false,
            }
        }
    }
</script>
