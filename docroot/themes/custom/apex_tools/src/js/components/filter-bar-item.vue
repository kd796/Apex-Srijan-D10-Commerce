<template>
    <div class="filter-bar-item" v-on="$listeners">
        <template v-if="element === 'action'">
            <div class="filter-bar-action">
                <slot></slot>
            </div>
        </template>
        <template v-else>
            <div class="filter">
                <label :for="inputId" class="filter-label">{{ label }}</label>
                <template v-if="element == 'select'">
                    <select :id="inputId" v-bind="inputAttributes" v-model="typed" class="filter-input">
                        <option :value="option.value" v-for="option in selectOptions">{{ option.label }}</option>
                    </select>
                </template>
                <template v-else>
                    <input v-bind="inputAttributes" v-model="typed" class="filter-input">
                </template>
            </div>
        </template>
    </div>
</template>
<script>
  export default {
    props: {
      label: {
        type: String,
      },
      value: {},
      element: {
        type: String,
        default: "input",
      },
      type: {
        type: String,
        default: "text",
      },
      attributes: {},
      options: {
        default: null,
      },
    },
    data() {
      return {
        typed: this.value,
      }
    },
    computed: {
      inputId() {
        return this._uid;
      },
      inputAttributes() {
        return Object.assign({
          type: (this.element === "input") ? this.type : null,
          value: this.value,
        }, this.attributes);
      },
      selectOptions() {
        let options = [];
        if (Array.isArray(this.options)) {
          this.options.forEach((option) => {
            if (typeof option === "object") {
              options.push(option);
            }
            else {
              options.push({label: option, value: option});
            }
          });
        }
        else if (typeof this.options === "object") {
          for (let key in this.options) {
            if (this.options.hasOwnProperty(key)) {
              options.push({
                label: this.options[key],
                value: key,
              });
            }
          }
        }

        return options;
      }
    },
    watch: {
      typed(newValue) {
      try {
          if(typeof(newValue) !== "undefined" && typeof(newValue.target) !== "undefined") {
            this.$emit("input", newValue.target.value);
          } else if (!this.value && (this.label == 'Distributor Level' || this.label == 'Products Offered')) {
            this.typed = null;
          }
        } catch(err) {
        }
      },
      value(newValue) {
        this.typed = newValue;
      }
    }
  }
</script>
