export const ActiveItemsMixin = {
    data() {
        return {
            items: [],
        }
    },
    computed: {
        activeIndex() {
            let activeIndex = 0;
            this.items.forEach((item, index) => {
                if (item.active) {
                    activeIndex = index;
                }
            });

            return activeIndex;
        },
        activeItem() {
            return this.items.find(item => item.active);
        }
    },
    methods: {
        isActive(id) {
            return id === this.activeItem.id;
        },
        selectItem(id) {
            this.items.forEach((item) => {
                item.active = (item.id === id);
            });
        },
        selectIndex(newIndex) {
            if (newIndex < 0) {
                newIndex = this.items.length - 1;
            }

            if (newIndex >= this.items.length) {
                newIndex = 0;
            }

            this.items.forEach((item, index) => {
                item.active = (index === newIndex);
            });
        },
        selectNext() {
            this.selectIndex(this.activeIndex + 1);
        },
        selectPrevious() {
            this.selectIndex(this.activeIndex - 1);
        }
    },
    created() {
        this.items = this.$children;
    },
    mounted() {
        if (this.items) {
            this.selectIndex(0);
        }
    },
}

export const ActiveItemMixin = {
    data() {
        return {
            active: false,
        }
    }
};

export default ActiveItemsMixin;
