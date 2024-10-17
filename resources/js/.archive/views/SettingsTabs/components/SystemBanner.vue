<template>
  <div class="pf-c-panel pf-m-raised">
    <div class="pf-c-panel__header">Login Banner</div>
    <hr class="pf-c-divider" />
    <div class="pf-c-panel__main">
      <div class="pf-c-panel__main-body">
        <form
          novalidate=""
          class="pf-c-form pf-m-horizontal">
          <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
              <label
                class="pf-c-form__label"
                for="form-horizontal-info">
                <span class="pf-c-form__label-text">Banner</span>
              </label>
              <button
                class="pf-c-form__group-label-help"
                aria-label="More info"
                tabindex="-1">
                <i
                  class="pficon pf-icon-help"
                  aria-hidden="true"></i>
              </button>
            </div>
            <div class="pf-c-form__group-control">
              <textarea
                class="pf-c-form-control"
                type="text"
                id="login_banner"
                name="login_banner"
                aria-label="System Banner"
                spellcheck="false"
                data-ms-editor="true"
                v-model="banner"
                rows="3"
                autocomplete="off"></textarea>
              <helper-success-text
                :show="bannerSuccess.isSuccess"
                :message="bannerSuccess.successMsg"
                :key="1"
                style="margin-top: 10px"></helper-success-text>
            </div>
          </div>

          <div class="pf-c-form__group pf-m-action">
            <div class="pf-c-form__group-control">
              <div class="pf-c-form__actions">
                <button
                  class="pf-c-button pf-m-primary"
                  type="submit"
                  @click.prevent="saveBanner">
                  Save
                </button>
                <button
                  class="pf-c-button pf-m-tertiary"
                  type="button"
                  @click.prevent="resetBanner">
                  Reset
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import HelperSuccessText from '../../../components/HelperSuccessText.vue';

export default {
  props: {},

  components: {
    HelperSuccessText
  },

  setup(props) {
    const bannerSuccess = reactive({
      isSuccess: false,
      successMsg: ''
    });

    const banner = ref('');

    onMounted(() => {
      getLoginBanner();
    });

    function getLoginBanner() {
      axios
        .get('/api/settings/banner/1')
        .then(response => {
          // handle success
          banner.value = response.data.login_banner;
        })
        .catch(error => {
          // handle error
          console.log(error.response);
        });
    }

    function saveBanner() {
      axios
        .patch('/api/settings/banner/1', {
          login_banner: banner.value
        })
        .then(response => {
          // handle success
          bannerSuccess.isSuccess = true;
          bannerSuccess.successMsg = 'Banner saved successfully';
          setTimeout(() => {
            bannerSuccess.isSuccess = false;
          }, 3000);
        })
        .catch(error => {
          // handle error
          console.log(error);
        });
    }

    return {
      banner,
      bannerSuccess,
      saveBanner
    };
  }
};
</script>
