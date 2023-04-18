<template>
    <div class="draggable"
         @touchstart.prevent.stop="pin"
         @touchend.prevent.stop="release"
         @touchmove.prevent.stop="move"
         @mousedown.prevent.stop="pin"
         @mouseup.prevent.stop="release">
        <slot></slot>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                shiftX: 0,
                shiftY: 0,
                left: 0,
                top: 0,
            }
        },
        methods: {
            pin(e) {
                this.shiftX = e.pageX ? e.pageX - this.$el.offsetLeft : e.changedTouches[0].pageX - this.$el.offsetLeft;
                this.shiftY = e.pageY ? e.pageY - this.$el.offsetTop : e.changedTouches[0].pageY - this.$el.offsetTop;

                this.$el.addEventListener('touchmove', this.move);
                this.$el.addEventListener('mousemove', this.move);
                this.$el.addEventListener('mouseleave', this.release);
            },
            move(e) {
                e.preventDefault();

                const x = e.pageX || e.changedTouches[0].pageX;
                const y = e.pageY || e.changedTouches[0].pageY;
                let newLeft = x - this.shiftX;
                let newTop = y - this.shiftY;

                this.left = newLeft;
                this.$el.style.left = `${newLeft}px`;
                this.top = newTop;
                this.$el.style.top = `${newTop}px`;
            },
            release(e) {
                this.$el.removeEventListener('touchmove', this.move);
                this.$el.removeEventListener('mousemove', this.move);
                this.$el.removeEventListener('mouseleave', this.release);

                this.$el.onmouseup = null;
                this.$el.ontouchend = null;
            },
        }
    }
</script>
