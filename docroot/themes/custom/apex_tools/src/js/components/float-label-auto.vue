<script>
    export default {
        data() {
            return {
                focused: false,
                typed: '',
            }
        },
        methods: {
            isFocused() {
                return this.focused;
            },
            isFloated() {
                return !!(this.focused || this.typed);
            },
            getInput() {
                return this.$el.querySelector("input, textarea");
            },
            getLabel() {
                return this.$el.querySelector("label");
            },
            addListeners() {
                let $input = this.getInput();
                if ($input) {
                    $input.addEventListener('focus', (e) => {
                        this.focused = true;
                    });
                    $input.addEventListener('blur', (e) => {
                        this.focused = false;
                    });
                    $input.addEventListener('input', (e) => {
                        this.typed = e.target.value;
                    });
                } else {
                    console.warn("No input found");
                }
            },
            toggleClasses() {
                this.$el.classList.toggle('float-label--focused', this.isFocused());
                this.$el.classList.toggle('float-label--floated', this.isFloated());
            },
            addDefaultClasses() {
                let $input = this.getInput();
                if ($input) {
                    $input.classList.add("float-label-input");
                }

                let $label = this.getLabel();
                if ($label) {
                    $label.classList.add("float-label-label");
                }

                this.$el.classList.add("float-label");
                this.$el.classList.toggle("float-label--textarea", ($input && $input.tagName === 'TEXTAREA'));
            },
            syncTypedValue() {
                let $input = this.getInput();
                if ($input) {
                    this.typed = $input.value;
                }
            }
        },
        mounted() {
            this.syncTypedValue();
            this.addListeners();
            this.addDefaultClasses();
            this.toggleClasses();
        },
        watch: {
            typed() {
                this.toggleClasses();
            },
            focused() {
                this.toggleClasses();
            }
        }
    }
</script>
