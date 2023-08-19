<template>
  <div class="pf-c-panel pf-m-raised" style="margin-top: 10px">
    <div class="pf-c-panel__header">Email Settings</div>
    <hr class="pf-c-divider" />
    <div class="pf-c-panel__main">
      <div class="pf-c-panel__main-body">
        <form novalidate="" class="pf-c-form pf-m-horizontal">
          <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label" tabindex="-1">
                <span class="pf-c-form__label-text">Mail Host</span>
                <span class="pf-c-form__label-required" aria-hidden="true"
                  >*</span
                >
              </label>
            </div>
            <div class="pf-c-form__group-control">
              <input
                class="pf-c-form-control"
                type="text"
                id="mail_host"
                name="mail_host"
                required=""
                spellcheck="false"
                data-ms-editor="true"
                v-model="settings.mail_host"
                autocomplete="off"
                tabindex="1"
              />
              <p
                v-if="emailSettings.errors.mail_host"
                class="pf-c-form__helper-text pf-m-error"
                id="form-help-text-address-helper"
                aria-live="polite"
              >
                {{ emailSettings.errors.mail_host[0] }}
              </p>
            </div>
          </div>
          <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">SMTP Port</span>
              </label>
              <span class="pf-c-form__label-required" aria-hidden="true"
                >*</span
              >
            </div>
            <div class="pf-c-form__group-control">
              <input
                class="pf-c-form-control"
                type="number"
                id="mail_port"
                name="mail_port"
                required=""
                spellcheck="false"
                data-ms-editor="true"
                v-model="settings.mail_port"
                autocomplete="off"
                tabindex="1"
              />
              <p
                v-if="emailSettings.errors.mail_port"
                class="pf-c-form__helper-text pf-m-error"
                id="form-help-text-address-helper"
                aria-live="polite"
              >
                {{ emailSettings.errors.mail_port[0] }}
              </p>
            </div>
          </div>
          <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">From Address</span>
                <span class="pf-c-form__label-required" aria-hidden="true"
                  >*</span
                >
              </label>
            </div>
            <div class="pf-c-form__group-control">
              <input
                class="pf-c-form-control"
                type="email"
                id="mail_from_email"
                name="mail_from_email"
                required=""
                spellcheck="false"
                data-ms-editor="true"
                v-model="settings.mail_from_email"
                autocomplete="off"
                tabindex="2"
              />
              <p
                v-if="emailSettings.errors.mail_from_email"
                class="pf-c-form__helper-text pf-m-error"
                id="form-help-text-address-helper"
                aria-live="polite"
              >
                {{ emailSettings.errors.mail_from_email[0] }}
              </p>
            </div>
          </div>

          <div class="pf-c-form__group">
            <div class="pf-c-form__group-label" style="position: relative">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">Recipients</span>
              </label>

              <button
                class="pf-c-form__group-label-help"
                @mouseover="mailToTooltip = true"
                @mouseleave="mailToTooltip = false"
                tabindex="-1"
              >
                <i class="pficon pf-icon-help" aria-hidden="true"></i>
              </button>
              <div
                class="pf-c-tooltip pf-m-bottom"
                role="tooltip"
                v-if="mailToTooltip"
                style="position: absolute"
              >
                <div class="pf-c-tooltip__arrow"></div>
                <div class="pf-c-tooltip__content" id="tooltip-top-content">
                  Enter multiple emails separated by a semi-colon (;)
                </div>
              </div>
            </div>
            <div class="pf-c-form__group-control">
              <input
                class="pf-c-form-control"
                type="text"
                id="mail_to_email"
                name="mail_to_email"
                required=""
                spellcheck="false"
                data-ms-editor="true"
                v-model="settings.mail_to_email"
                autocomplete="off"
                tabindex="3"
              />
              <p
                v-if="emailSettings.errors.mail_to_email"
                class="pf-c-form__helper-text pf-m-error"
                id="form-help-text-address-helper"
                aria-live="polite"
              >
                {{ emailSettings.errors.mail_to_email[0] }}
              </p>
            </div>
          </div>
          <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">Authentication</span>
                <span class="pf-c-form__label-required" aria-hidden="true"
                  >*</span
                >
              </label>
            </div>
            <div class="pf-c-form__group-control">
              <label class="pf-c-switch" style="margin-top: 20px">
                <input
                  class="pf-c-switch__input"
                  type="checkbox"
                  id="mail_authcheck"
                  aria-labelledby="mail_authcheck-on"
                  name="switchExample1"
                  :checked="settings.mail_authcheck"
                  v-model="settings.mail_authcheck"
                  @change="scrollToBottom"
                  tabindex="4"
                />
                <p
                  v-if="emailSettings.errors.mail_authcheck"
                  class="pf-c-form__helper-text pf-m-error"
                  id="form-help-text-address-helper"
                  aria-live="polite"
                >
                  {{ emailSettings.errors.mail_authcheck[0] }}
                </p>

                <span class="pf-c-switch__toggle"></span>

                <span
                  class="pf-c-switch__label pf-m-on"
                  id="mail_authcheck-on"
                  aria-hidden="true"
                  >Authentication enabled</span
                >

                <span
                  class="pf-c-switch__label pf-m-off"
                  id="mail_authcheck-off"
                  aria-hidden="true"
                  >Authentication disabled</span
                >
              </label>
            </div>
          </div>
          <div class="pf-c-form__group" v-if="settings.mail_authcheck">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">Encryption</span>
              </label>
              <button
                class="pf-c-form__group-label-help"
                aria-label="More info"
                tabindex="-1"
              ></button>
            </div>
            <div class="pf-c-form__group-control">
              <div class="pf-c-select pf-m-expanded">
                <span hidden>Choose an option</span>
                <button
                  class="pf-c-select__toggle"
                  type="button"
                  @click="showEncrytionOptions = !showEncrytionOptions"
                  tabindex="5"
                >
                  <div class="pf-c-select__toggle-wrapper">
                    <span
                      class="pf-c-select__toggle-text"
                      v-text="
                        settings.mail_encryption
                          ? settings.mail_encryption.toUpperCase()
                          : 'Choose an option'
                      "
                    ></span>
                  </div>
                  <span class="pf-c-select__toggle-arrow">
                    <i class="fas fa-caret-down" aria-hidden="true"></i>
                  </span>
                </button>
                <div v-if="showEncrytionOptions ? 'hidden' : ''">
                  <ul
                    class="pf-c-select__menu multi-select-dropdown-overflow"
                    role="listbox"
                    aria-labelledby="select-single-expanded-label"
                    tabindex="6"
                  >
                    <li role="presentation">
                      <button
                        class="pf-c-select__menu-item"
                        role="option"
                        @click="selectEncryption('tls')"
                      >
                        TLS
                        <span
                          class="pf-c-select__menu-item-icon"
                          v-if="settings.mail_encryption === 'tls'"
                        >
                          <i class="fas fa-check" aria-hidden="true"></i>
                        </span>
                      </button>
                    </li>
                    <li role="presentation">
                      <button
                        class="pf-c-select__menu-item"
                        role="option"
                        @click="selectEncryption('ssl')"
                      >
                        SSL
                        <span
                          class="pf-c-select__menu-item-icon"
                          v-if="settings.mail_encryption === 'ssl'"
                        >
                          <i class="fas fa-check" aria-hidden="true"></i>
                        </span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="pf-c-form__group" v-if="settings.mail_authcheck">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">SMTP Username</span>
              </label>
            </div>
            <div class="pf-c-form__group-control">
              <input
                class="pf-c-form-control"
                type="text"
                id="mail_username"
                name="mail_username"
                required=""
                spellcheck="false"
                data-ms-editor="true"
                v-model="settings.mail_username"
                autocomplete="off"
                tabindex="7"
              />
              <p
                v-if="emailSettings.errors.mail_username"
                class="pf-c-form__helper-text pf-m-error"
                id="form-help-text-address-helper"
                aria-live="polite"
              >
                {{ emailSettings.errors.mail_username[0] }}
              </p>
            </div>
          </div>
          <div class="pf-c-form__group" v-if="settings.mail_authcheck">
            <div class="pf-c-form__group-label">
              <label class="pf-c-form__label">
                <span class="pf-c-form__label-text">SMTP Password</span>
              </label>
            </div>
            <div class="pf-c-form__group-control">
              <input
                class="pf-c-form-control"
                type="password"
                id="mail_password"
                name="mail_password"
                required=""
                spellcheck="false"
                data-ms-editor="true"
                v-model="settings.mail_password"
                autocomplete="off"
                tabindex="8"
              />
              <p
                v-if="emailSettings.errors.mail_password"
                class="pf-c-form__helper-text pf-m-error"
                id="form-help-text-address-helper"
                aria-live="polite"
              >
                {{ emailSettings.errors.mail_password[0] }}
              </p>
            </div>
          </div>
          <div class="pf-c-form__group pf-m-action">
            <div class="pf-c-form__group-control">
              <div class="pf-c-form__actions">
                <button
                  class="pf-c-button pf-m-primary"
                  type="submit"
                  @click.prevent="updateEmail"
                  tabindex="9"
                >
                  Save
                </button>
                <button
                  class="pf-c-button pf-m-progress pf-m-secondary"
                  :class="emailSettings.test1Loading ? 'pf-m-in-progress' : ''"
                  type="button"
                  @click.prevent="testEmail('email')"
                  tabindex="10"
                >
                  <span
                    class="pf-c-button__progress"
                    v-if="emailSettings.test1Loading"
                  >
                    <span
                      class="pf-c-spinner pf-m-md"
                      role="progressbar"
                      aria-label="Loading..."
                    >
                      <span class="pf-c-spinner__clipper"></span>
                      <span class="pf-c-spinner__lead-ball"></span>
                      <span class="pf-c-spinner__tail-ball"></span>
                    </span> </span
                  >Test Email
                </button>

                <button
                  class="pf-c-button pf-m-progress pf-m-secondary"
                  :class="emailSettings.test2Loading ? 'pf-m-in-progress' : ''"
                  type="button"
                  @click.prevent="testEmail('notification')"
                  tabindex="11"
                >
                  <span
                    class="pf-c-button__progress"
                    v-if="emailSettings.test2Loading"
                  >
                    <span
                      class="pf-c-spinner pf-m-md"
                      role="progressbar"
                      aria-label="Loading..."
                    >
                      <span class="pf-c-spinner__clipper"></span>
                      <span class="pf-c-spinner__lead-ball"></span>
                      <span class="pf-c-spinner__tail-ball"></span>
                    </span>
                  </span>
                  Test Notifications
                </button>
              </div>

              <helper-success-text
                :show="emailSettings.isSuccess"
                :message="emailSettings.successMsg"
                style="margin-top: 10px"
              ></helper-success-text>
              <helper-error-text
                :show="emailSettings.isError"
                :message="emailSettings.errorMsg"
                style="margin-top: 10px"
              ></helper-error-text>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import HelperErrorText from "../../../components/HelperErrorText.vue";
import HelperSuccessText from "../../../components/HelperSuccessText.vue";
import axios from "axios";
import useScrollToBottom from "../../../composables/scrollToBottom";
import { reactive, onMounted, ref } from "vue";

export default {
  props: {
    settings: { type: Object },
  },

  components: {
    HelperSuccessText,
    HelperErrorText,
  },

  setup(props) {
    const showEncrytionOptions = ref(false);
    const emailSettingsDefault = reactive({
      isSuccess: false,
      isError: false,
      isDefault: true,
      successMsg: "",
      errorMsg: "",
      errors: {},
      test1Loading: false,
      test2Loading: false,
    });
    const emailSettings = reactive({
      isSuccess: false,
      isError: false,
      isDefault: true,
      successMsg: "",
      errorMsg: "",
      errors: {},
      test1Loading: false,
      test2Loading: false,
    });
    const mailToTooltip = ref(false);
    const { scrollToBottom } = useScrollToBottom();

    onMounted(() => {
      // console.log(props.settings);
    });

    function selectEncryption(value) {
      props.settings.mail_encryption = value;
      showEncrytionOptions.value = false;
    }

    function updateEmail() {
      Object.assign(emailSettings, emailSettingsDefault);

      axios
        .patch("/api/settings/email/1", {
          mail_driver: "smtp",
          mail_host: props.settings.mail_host,
          mail_port: props.settings.mail_port,
          mail_username: props.settings.mail_username,
          mail_password: props.settings.mail_password,
          mail_from_email: props.settings.mail_from_email,
          mail_to_email: props.settings.mail_to_email,
          mail_authcheck: props.settings.mail_authcheck,
          mail_encryption: props.settings.mail_encryption,
        })
        .then((response) => {
          emailSettings.isSuccess = true;
          emailSettings.successMsg = "Email settings saved successfully";
          scrollToBottom();
          setTimeout(() => {
            Object.assign(emailSettings, emailSettingsDefault);
          }, 10000);
        })
        .catch((error) => {
          emailSettings.isError = true;
          emailSettings.errorMsg = error.response.data.message;
          scrollToBottom();
          setTimeout(() => {
            Object.assign(emailSettings, emailSettingsDefault);
          }, 5000);
        });
    }

    function testEmail(type) {
      Object.assign(emailSettings, emailSettingsDefault);
      switch (type) {
        case "email":
          emailSettings.test1Loading = true;
          break;
        case "notification":
          emailSettings.test2Loading = true;
          break;
      }
      axios
        .get("/api/settings/test-" + type)
        .then((response) => {
          // handle success
          emailSettings.test1Loading = false;
          emailSettings.test2Loading = false;
          emailSettings.isSuccess = true;
          emailSettings.successMsg = response.data.message;
          scrollToBottom();
          setTimeout(() => {
            Object.assign(emailSettings, emailSettingsDefault);
          }, 5000);
        })
        .catch((error) => {
          // handle error
          emailSettings.test1Loading = false;
          emailSettings.test2Loading = false;
          emailSettings.isError = true;
          emailSettings.errorMsg = error.response.data.message;
          scrollToBottom();
          setTimeout(() => {
            Object.assign(emailSettings, emailSettingsDefault);
          }, 5000);
          // Object.assign(emailSettings.errors, error.response.data.message);
        });
    }

    return {
      emailSettings,
      scrollToBottom,
      showEncrytionOptions,
      selectEncryption,
      testEmail,
      updateEmail,
      mailToTooltip,
    };
  },
};
</script>
