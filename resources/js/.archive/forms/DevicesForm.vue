<template>
  <input
    id="editid"
    name="editid"
    type="hidden"
    :value="viewstate.editid"
    autocomplete="off" />

  <div class="pf-l-grid pf-m-gutter">
    <span
      class="pf-c-spinner pf-m-xl"
      role="progressbar"
      aria-label="Loading items"
      v-if="isLoading"
      style="margin: 0 auto; display: table">
      <span class="pf-c-spinner__clipper"></span>
      <span class="pf-c-spinner__lead-ball"></span>
      <span class="pf-c-spinner__tail-ball"></span>
    </span>
    <div class="pf-l-grid__item pf-m-12-col pf-m-6-col-on-md">
      <!-- DEVICE INFO -->
      <form
        novalidate
        class="pf-c-form"
        v-if="!isLoading">
        <div class="pf-u-color-300">Device Information</div>

        <!-- DEVICE_NAME -->
        <reusable-input-field
          tabindex="1"
          v-model="model.device_name"
          :fieldlabel="'Device Name'"
          :fieldname="'device_name'"
          :fieldtype="'text'"
          :btnHelperTxt="'Device Name'"
          :errors="errors"
          :key="'device_name'"
          :required="true"></reusable-input-field>
        <!-- DEVICE_NAME -->

        <div class="pf-l-grid pf-m-all-6-col-on-md pf-m-gutter">
          <!-- DEVICE_IP -->
          <reusable-input-field
            tabindex="1"
            v-model="model.device_ip"
            :fieldlabel="'Hostname (or IP Address)'"
            :fieldname="'device_ip'"
            :fieldtype="'text'"
            :btnHelperTxt="'Hostname (or IP Address)'"
            :errors="errors"
            :key="'device_ip'"
            :required="true"></reusable-input-field>
          <!-- DEVICE_IP -->

          <!-- DEVICE_PORT_OVERRIDE -->
          <reusable-input-field
            tabindex="1"
            v-model="model.device_port_override"
            :fieldlabel="'Default Port Override'"
            :fieldname="'device_port_override'"
            :fieldtype="'number'"
            :btnHelperTxt="'Default Port Override'"
            :errors="errors"
            :key="'device_port_override'"
            :tooltip="true">
            <template v-slot:tooltip-text>Set the connection port specific to this device. it overrides the value set in the connection template. Leave empty otherwise.</template>
          </reusable-input-field>
          <!-- device_port_override -->
        </div>

        <!-- VENDOR FIELD -->
        <device-vendor-field
          v-model="model"
          v-model:updateValue="model.device_vendor"
          :errors="errors"></device-vendor-field>

        <!-- VENDOR FIELD -->
        <device-model-field
          v-model="model.device_model"
          :errors="errors"></device-model-field>

        <!-- CATEGORY FIELD -->
        <device-category-field
          v-model="model"
          v-model:updateValue="model.device_category_id"
          :errors="errors"></device-category-field>

        <!-- TAG FIELD -->
        <device-tag-field
          v-model="model"
          :fieldname="'device_tag'"
          v-model:updateValue="model.device_tags"
          :errors="errors"></device-tag-field>
      </form>
    </div>
    <!-- DEVICE INFO -->
    <!-- CONNECTION INFO -->
    <div class="pf-l-grid__item pf-m-12-col pf-m-6-col-on-md">
      <form
        novalidate
        class="pf-c-form"
        v-if="!isLoading">
        <div class="pf-u-color-300">Connection Information</div>

        <device-username-field
          v-model="model.device_username"
          @setCreds="setCreds($event)"
          :fieldlabel="'Username'"
          :fieldname="'device_username'"
          :fieldtype="'text'"
          :cred_id="model.device_cred_id"
          :btnHelperTxt="'Username'"
          :errors="errors"
          :key="'device_username'"></device-username-field>

        <reusable-input-field
          tabindex="1"
          v-model="model.device_password"
          :fieldlabel="'Password'"
          :fieldname="'device_password'"
          :fieldtype="passwordFieldType[1]"
          :btnHelperTxt="'Main Password'"
          :errors="errors"
          :key="'device_password'">
          <template
            v-slot:btnIcon
            v-if="$userName === 'admin'">
            <button
              tabindex="-1"
              class="pf-c-button pf-m-control"
              type="button"
              @click="switchVisibility(1)">
              <i
                class="fas fa-eye"
                aria-hidden="true"></i>
            </button>
          </template>
        </reusable-input-field>

        <reusable-input-field
          tabindex="1"
          v-model="model.device_enable_password"
          :fieldlabel="'Enable Password'"
          :fieldname="'device_enable_password'"
          :fieldtype="passwordFieldType[2]"
          :btnHelperTxt="'Enable Password'"
          :errors="errors"
          :key="'device_enable_password'">
          <template
            v-slot:btnIcon
            v-if="$userName === 'admin'">
            <button
              tabindex="-1"
              class="pf-c-button pf-m-control"
              type="button"
              @click="switchVisibility(2)">
              <i
                class="fas fa-eye"
                aria-hidden="true"></i>
            </button>
          </template>
        </reusable-input-field>

        <!-- TEMPLATE FIELD -->
        <device-template-field
          v-model="model"
          v-model:updateValue="model.device_template"
          :errors="errors"></device-template-field>

        <reusable-input-field
          tabindex="1"
          v-model="model.device_main_prompt"
          :fieldlabel="'Main Prompt'"
          :fieldname="'device_main_prompt'"
          :fieldtype="'text'"
          :btnHelperTxt="'Main Prompt'"
          :errors="errors"
          :key="'device_main_prompt'"
          :tooltip="true">
          <template v-slot:tooltip-text>This is the 'Privileged EXEC' prompt. You will run show commands from this prompt and you can access configure mode. Usually 'router1#'</template>
        </reusable-input-field>

        <reusable-input-field
          tabindex="1"
          v-model="model.device_enable_prompt"
          :fieldlabel="'Enable Prompt'"
          :fieldname="'device_enable_prompt'"
          :fieldtype="'text'"
          :btnHelperTxt="'Enable Prompt'"
          :errors="errors"
          :key="'device_enable_prompt'"
          :tooltip="true">
          <template v-slot:tooltip-text>This is the 'User EXEC' prompt. The first level of access prompt. Usually 'router1>'</template>
          <template v-slot:helper-text>
            <button
              class="pf-c-button pf-m-link pf-m-inline"
              type="button"
              @click="generatePrompts()">
              Auto generate prompts from device name
            </button>
          </template>
        </reusable-input-field>
      </form>
    </div>
  </div>
  <!-- CONNECTION INFO -->
  <!-- ACTIONS -->
  <div
    class="pf-c-form__group pf-m-action"
    v-if="!isLoading">
    <div class="pf-c-form__group-control">
      <div class="pf-c-form__actions">
        <button
          class="pf-c-button pf-m-primary"
          type="submit"
          @click.prevent="saveModels">
          Save
        </button>
        <button
          class="pf-c-button pf-m-link"
          type="button"
          @click="close">
          Cancel
        </button>
      </div>
    </div>
  </div>
  <!-- ACTIONS -->
</template>

<script type="text/javascript">
import DeviceCategoryField from './components/DeviceCategoryField.vue';
import DeviceModelField from './components/DeviceModelField.vue';
import DeviceTagField from './components/DeviceTagField.vue';
import DeviceTemplateField from './components/DeviceTemplateField.vue';
import DeviceUsernameField from './components/DeviceUsernameField.vue';
import DeviceVendorField from './components/DeviceVendorField.vue';
import ReusableInputField from './components/ReusableInputField.vue';
import useModels from '../composables/ModelsFactory';
import { ref, onMounted, reactive, watch } from 'vue';
// import func from 'vue-editor-bridge';

export default {
  props: {
    viewstate: {
      type: Object
    }
  },
  emits: ['closeDrawer', 'formsubmitted'],
  components: {
    DeviceVendorField,
    DeviceModelField,
    DeviceCategoryField,
    DeviceTagField,
    DeviceUsernameField,
    DeviceTemplateField,
    ReusableInputField
  },

  setup(props, { emit }) {
    const showRoleOptions = ref(false);
    const passwordFieldType = reactive({
      1: 'password',
      2: 'password'
    });
    if (props.viewstate.isClone) {
      var formtypetext = 'clon';
    } else {
      var formtypetext = props.viewstate.editid === 0 ? 'add' : 'edit';
    }
    const formtype = ref(formtypetext);
    const { errors, model, clearModel, updateModel, getModel, getModelClone, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);

    onMounted(() => {
      if (props.viewstate.isClone) {
        getModelClone(props.viewstate.editid);
      } else {
        getModel(props.viewstate.editid);
      }
    });

    function switchVisibility(id) {
      this.passwordFieldType[id] = this.passwordFieldType[id] === 'password' ? 'text' : 'password';
    }

    const saveModels = async () => {
      if (props.viewstate.editid != 0 && !props.viewstate.isClone) {
        await updateModel(model);
      } else {
        await storeModel(model);
      }

      if (errors.value === '') {
        emit('formsubmitted', props.viewstate.pagenamesingle + ' ' + formtype.value + 'ed!');
        clearModel();
        close();
      }
    };

    function setCreds(cred) {
      model.device_username = cred.cred_username;
      model.device_password = cred.cred_password;
      model.device_enable_password = cred.cred_enable_password;
      model.device_cred_id = cred.id;
    }

    function close() {
      emit('closeDrawer');
    }

    function generatePrompts() {
      model.device_main_prompt = model.device_name + '#';
      model.device_enable_prompt = model.device_name + '>';
    }

    return {
      clearModel,
      close,
      errors,
      generatePrompts,
      isLoading,
      model,
      passwordFieldType,
      saveModels,
      setCreds,
      showRoleOptions,
      switchVisibility
    };
  }
};
</script>
