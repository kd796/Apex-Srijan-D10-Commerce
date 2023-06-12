<template>
    <dropdown @toggle="handleToggle">
        <template slot="toggle">
            <slot></slot>
        </template>
        <template slot="menu">
            <div class="share-buttons-menu">
                <template v-if="error">
                    <a :href="fallbackUrl" class="share-button" target="_blank">Share This Page</a>
                </template>
                <template v-else>
                    <div class="addthis_toolbox" v-if="loaded">
                        <a class="share-button addthis_button_facebook">Facebook</a>
                        <a class="share-button addthis_button_twitter">Twitter</a>
                        <a class="share-button addthis_button_linkedin">LinkedIn</a>
                        <a class="share-button addthis_button_more">More…</a>
                    </div>
                    <span class="share-buttons-loading" v-else><span class="loader">Loading…</span></span>
                </template>
            </div>
        </template>
    </dropdown>
</template>
<script>
  export default {
    props: {
      script: {
        type: String,
        required: true,
      },
      fallbackUrl: {
        type: String,
        default: 'https://www.addthis.com/bookmark.php',
      }
    },
    data() {
      return {
        loaded: this.isLoaded(),
        error: null,
      }
    },
    methods: {
      handleToggle(visible) {
        if (visible) {
          this.load().catch(() => {
            this.error = true;
          });
        }
      },
      isLoaded() {
        return typeof window.addthis !== "undefined"
      },
      load() {
        return new Promise((resolve, reject) => {
          if (!this.isLoaded()) {
            this.injectScriptTag(this.url).then(() => {
              this.init();
              resolve();
            }, (error) => {
              reject(error)
            });
          }
          else {
            resolve();
          }
        })
      },
      injectScriptTag() {
        return new Promise((resolve, reject) => {
          let script = document.createElement("script");
          script.src = this.script;
          script.onreadystatechange = () => {
            if (script.readyState === "loaded" || script.readyState === "complete") {
              script.onreadystatechange = null;
              resolve();
            }
          };
          script.onload = () => {
            resolve()
          };
          script.onerror = () => {
            reject();
          };

          document.body.appendChild(script);
        })
      },
      init() {
        this.loaded = true;
      }
    }
  }
</script>
