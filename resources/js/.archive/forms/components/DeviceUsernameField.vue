<template>
  <div class="pf-c-form__group">
    <div class="pf-c-form__group-label">
      <label class="pf-c-form__label" for="device_username">
        <span class="pf-c-form__label-text">Username</span>
        <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
      </label>
    </div>
    <div class="pf-c-input-group">
      <input
        class="pf-c-form-control"
        :type="fieldtype"
        :id="fieldname"
        :name="fieldname"
        :value="modelValue"
        :alt="btnHelperTxt"
        :title="btnHelperTxt"
        @change="$emit('update:modelValue', $event.target.value)"
        spellcheck="false"
        data-ms-editor="true"
        aria-label="Device Username"
        :aria-invalid="errors[fieldname] ? true : false"
      />
    </div>
    <p
      v-if="errors.device_username"
      class="pf-c-form__helper-text pf-m-error"
      id="device_username_error"
      aria-live="polite"
    >
      {{ errors.device_username[0] }}
    </p>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from "vue";
import { onClickOutside } from "@vueuse/core";

export default {
  props: {
    fieldlabel: {
      type: String,
      required: true,
    },
    fieldname: {
      type: String,
      required: true,
    },
    fieldtype: {
      type: String,
      required: true,
    },
    btnHelperTxt: {
      type: String,
      default: "",
    },
    cred_id: {
      type: Number,
      default: 0,
    },
    errors: "",
    modelValue: "",
  },

  emit: ["update:modelValue", "setCreds"],

  setup(props, { emit }) {
    const isLoadingCreds = ref(false);
    const showCredsSelect = ref(false);
    const creds = ref({});
    const clickOutsidetargetCreds = ref(null);

    onClickOutside(clickOutsidetargetCreds, (event) => close());

    const model = reactive({
      device_username: "",
      device_username: "",
    });

    function toggleCreds() {
      isLoadingCreds.value = true;
      axios
        .get("/api/settings/credentials?page=1&perPage=100")
        .then((response) => {
          creds.value = response.data.data;
          // Object.assign(Creds, response.data);
          isLoadingCreds.value = false;
        });
      showCredsSelect.value = !showCredsSelect.value;
    }

    function setCreds(cred) {
      // console.log(cred);
      // model.device_username = creds.value.cred_name;
      emit("setCreds", cred);
      close();
    }

    function close() {
      showCredsSelect.value = false;
    }

    return {
      clickOutsidetargetCreds,
      creds,
      isLoadingCreds,
      model,
      setCreds,
      showCredsSelect,
      toggleCreds,
    };
  },
};
</script>
