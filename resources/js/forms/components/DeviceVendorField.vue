<template>
  <div class="pf-c-form__group">
    <div class="pf-c-form__group-label">
      <label class="pf-c-form__label" for="device_vendor">
        <span class="pf-c-form__label-text">Vendor</span>

        <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
      </label>
    </div>
    <div class="pf-c-input-group">
      <div
        class="pf-c-select"
        :class="errors.device_vendor ? 'pf-m-invalid' : ''"
        ref="clickOutsidetarget"
      >
        <button
          class="pf-c-select__toggle"
          type="button"
          id="device_vendor-toggle"
          aria-haspopup="true"
          aria-expanded="false"
          aria-labelledby="device_vendor-label device_vendor-toggle"
          @click.prevent="toggleSelect"
          v-on:keydown.esc="onEsc"
        >
          <div class="pf-c-select__toggle-wrapper">
            <span class="pf-c-select__toggle-text">{{
              selected.vendorName
            }}</span>
          </div>
          <span class="pf-c-select__toggle-arrow">
            <i class="fas fa-caret-down" aria-hidden="true"></i>
          </span>
        </button>

        <ul
          class="pf-c-select__menu multi-select-dropdown-overflow"
          role="listbox"
          aria-labelledby="device_vendor-label"
          v-if="showSelect ? 'hidden' : ''"
          style="width: auto"
        >
          <div
            class="pf-c-select__menu-group-title"
            aria-hidden="true"
            id="select-checkbox-expanded-selected-group-vendor"
          >
            Select Vendor
          </div>
          <div v-if="!isLoading">
            <li role="presentation" v-for="item in models.data" :key="item.id">
              <button
                class="pf-c-select__menu-item"
                role="option"
                @click.prevent="makeSelection(item.id)"
              >
                {{ item.vendorName }}
                <span
                  v-if="item.id === selected.id"
                  class="pf-c-select__menu-item-icon"
                >
                  <i class="fas fa-check" aria-hidden="true"></i>
                </span>
              </button>
            </li>

            <!-- <li role="presentation">
              <button class="pf-c-select__menu-item pf-m-load" role="option">
                create new
              </button>
            </li> -->
          </div>

          <li
            role="presentation"
            class="pf-c-select__list-item pf-m-loading"
            v-if="isLoading"
          >
            <span
              class="pf-c-spinner pf-m-lg"
              role="progressbar"
              aria-label="Loading item"
            >
              <span class="pf-c-spinner__clipper"></span>
              <span class="pf-c-spinner__lead-ball"></span>
              <span class="pf-c-spinner__tail-ball"></span>
            </span>
          </li>
        </ul>
      </div>
    </div>
    <p
      v-if="errors.device_vendor"
      class="pf-c-form__helper-text pf-m-error"
      id="device_vendor_error"
      aria-live="polite"
    >
      {{ errors.device_vendor[0] }}
    </p>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from "vue";
import { onClickOutside } from "@vueuse/core";
import useViewFunctions from "../../composables/ViewFunctions";

export default {
  props: {
    modelValue: {
      type: Object,
    },
    errors: "",
  },

  setup(props, { emit }) {
    const showSelect = ref(false);
    const clickOutsidetarget = ref(null);
    onClickOutside(clickOutsidetarget, (event) => close());

    const selected = reactive({
      id: "",
      vendorName: "",
    });

    const viewstate = reactive({
      modelName: "vendors",
      pageOptionsState: {
        page: 1,
        per_page: 10000,
      },
      modelObject: {
        templateName: "",
      },
    });

    const { models, isLoading, dataTablePageChanged } = useViewFunctions(
      viewstate,
      viewstate.modelName,
      viewstate.modelObject
    );

    function makeSelection(id) {
      Object.assign(
        selected,
        models.data.find((item) => item.id === id)
      );
      emit("update:updateValue", selected.id);
      close();
    }

    function toggleSelect() {
      if (showSelect.value === false) {
        // i am opening the select
        dataTablePageChanged(viewstate.pageOptionsState);
      }
      showSelect.value = !showSelect.value;
    }

    function onEsc() {
      close();
    }

    function close() {
      showSelect.value = false;
    }

    if ("vendor" in props.modelValue) {
      selected.id = props.modelValue.vendor[0].id;
      selected.vendorName = props.modelValue.vendor[0].vendorName;
      emit("update:updateValue", selected.id);
    } else {
      selected.id = "";
      selected.vendorName = "Select a vendor";
    }

    return {
      clickOutsidetarget,
      selected,
      makeSelection,
      toggleSelect,
      showSelect,
      models,
      isLoading,
      onEsc,
    };
  },
};
</script>
