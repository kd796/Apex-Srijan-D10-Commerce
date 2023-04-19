<template>
    <div class="carousel">
        <ul class="carousel-nav" v-if="items.length > 1">
            <li class="carousel-nav-item" v-for="(item, index) in items" :class="{'carousel-nav-item--active': isActive(item.id)}">
                <a :href="`#${item.id}`" class="carousel-nav-link" @click.prevent="selectItem(item.id)">
                    <img :src="item.thumb" :alt="index" class="carousel-nav-thumb">
                </a>
            </li>
        </ul>
        <div class="carousel-items">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    import ActiveItemsMixin from '../mixins/active-items-mixin';

    export default {
        mixins: [ActiveItemsMixin],
    }
</script>
