<template>
    <div class="dropdown">
        <button class="dropdown-toggle" v-bind="toggleAttributes" @click.stop="hidden = ! hidden">
            <template v-if="label">{{ label }}</template>
            <template v-else>
                <slot name="toggle"></slot>
            </template>
        </button>
        <div role="menu" class="dropdown-menu" v-bind="menuAttributes" @click.stop @touchstart.stop>
            <slot name="menu"></slot>
        </div>
    </div>

</template>
<script>
  export default {
    props: {
      label: {
        type: String,
        default: null,
      }
    },
    data() {
      return {
        hidden: true,
      }
    },
    computed: {
      menuId() {
        return "dropdown-" + this._uid;
      },
      toggleAttributes() {
        return {
          "aria-controls": this.menuId,
          "aria-expanded": !this.hidden,
        }
      },
      menuAttributes() {
        return {
          hidden: this.hidden,
          id: this.menuId,
        }
      }
    },
    mounted() {
      document.addEventListener("click", () => {
        if (!this.hidden) {
          this.hidden = true;
        }
      });
      document.addEventListener("touchstart", () => {
        if (!this.hidden) {
          this.hidden = true;
        }
      });
    },
    watch: {
      hidden() {
        this.$emit("toggle", !this.hidden)
      }
    }
  }
</script>
