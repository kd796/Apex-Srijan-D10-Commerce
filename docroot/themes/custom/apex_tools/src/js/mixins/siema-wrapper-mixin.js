export default {
    data() {
        return {
            mounted: false,
            currentSlide: 0,
        }
    },
    computed: {
        hasPrevious() {
            return this.mounted ? this.currentSlide > 0 : null;
        },
        hasNext() {
            return this.mounted ? this.currentSlide + this.perPage < this.totalSlides : null;
        },
        perPage() {
            if (this.mounted && typeof this.$refs.slider.siema !== 'undefined') {
                let perPage = this.$refs.slider.siema.perPage;
                if (typeof perPage === 'object') {
                  let windowWidth = window.innerWidth;
                  Object.keys(this.$refs.slider.siema.perPage).sort().forEach(key => {
                    const value = this.$refs.slider.siema.perPage[key];
                    // console.log('siema-wrapper-mixin computed perPage()', { key, value });
                    if (windowWidth <= this.$refs.slider.siema.perPage[key]) perPage = value;
                  });
                }

                return perPage;
            }

            return 0;
        },
        totalSlides() {
            return this.mounted && typeof this.$refs.slider.siema !== 'undefined' ? this.$refs.slider.siema.innerElements.length : 0;
        },
    },
    mounted() {
        this.mounted = true;
    },
}
