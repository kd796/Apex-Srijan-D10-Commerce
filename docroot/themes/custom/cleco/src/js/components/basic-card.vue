<template>
    <div :class="{ 'basic-cards-item': true, 'active': active }">
        <slot></slot>
    </div>
</template>
<script>
    export default {
      props: {
        active: {
          type: Boolean,
          default: false,
          required: false,
        }
      }
    }
</script>
