<template>
    <div class="form-selector">
        <div class="form-selector-step">
            <h2 class="form-selector-step-title" v-if="selectorHeading">{{ selectorHeading }}</h2>

            <div class="checkable-cards">
                <label v-for="(form, index) in forms" :key="index" class="checkable-cards-item">
                    <input type="radio" v-model="selected" class="checkable-card-input" :value="form" @click="openLink(form)">
                    <span class="checkable-card">
                        <span class="checkable-card-icon">
                            <slot :name="form + '-icon'"></slot>
                        </span>
                        <span class="checkable-card-label" :data-selected-text="selectedLabel">
                            <slot :name="form + '-label'"></slot>
                        </span>
                    </span>
                </label>
            </div>
        </div>
        <div class="form-selector-step" v-if="selected">
            <h2 class="form-selector-step-title" v-if="formHeading">{{ formHeading }}</h2>

            <div class="form-selector-form" v-for="form in forms" :key="form" v-if="form === selected">
                <slot :name="form"></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import VueScrollTo from 'vue-scrollto';
    export default {
        props: {
            forms: {
                type: Array,
                default: () => []
            },
            formLinks: {
                type: [Array, Object],
                default: () => ({})
            },
            selectorHeading: {
                type: String,
                default: null,
            },
            formHeading: {
                type: String,
                default: null,
            },
            selectedLabel: {
                type: String,
                default: 'Selected',
            }
        },
        data() {
            return {
                selected: null,
            }
        },
        mounted() {
            this.$nextTick(function () {
                if (location.hash) {
                    const hash = location.hash.replace(/[^0-9a-z_]/g, '');
                    if (this.forms.includes(hash)) {
                        this.selected = hash;
                    }
                }
            });
        },
        methods: {
            openLink(form) {
                if (this.formLinks[form]) {
                    this.selected = null;
                    window.location = this.formLinks[form];
                }
            }
        },
        watch: {
            selected(value) {
                if ( value !== null && !this.formLinks.hasOwnProperty(value) ) {
                    setTimeout(() => {
                      this.$scrollTo('.form-selector-form', 500, { offset: -100, easing: 'linear' });

                      var captchaContainer = document.querySelector('.g-recaptcha');
                      grecaptcha.render(captchaContainer, {
                          'sitekey': captchaContainer.getAttribute('data-sitekey')
                      });

                      // re-bind Drupal behaviors
                      Drupal.behaviors.states.attach(document, drupalSettings);
                    }, 50);
                }
            }
        }
    }
</script>
