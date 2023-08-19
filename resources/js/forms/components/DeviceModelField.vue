<template>
  <div class="pf-c-form__group">
    <div class="pf-c-form__group-label">
      <label class="pf-c-form__label" for="device_category">
        <span class="pf-c-form__label-text">Model</span>
        <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
      </label>
    </div>
    <div
      class="pf-c-select pf-m-expanded"
      :class="errors.device_model ? 'pf-m-invalid' : ''"
      ref="clickOutsidetarget"
    >
      <span id="select-single-typeahead-expanded-label" hidden>{{
        searchTerm ? searchTerm : "Select model"
      }}</span>

      <div class="pf-c-select__toggle pf-m-typeahead">
        <div class="pf-c-select__toggle-wrapper">
          <input
            class="pf-c-form-control pf-c-select__toggle-typeahead"
            type="text"
            id="select-single-typeahead-expanded-typeahead"
            aria-label="Type to filter"
            placeholder="Select a model or type to create a new one..."
            v-model="searchTerm"
            @input="onChange"
            autocomplete="off"
          />
        </div>
        <button
          tabindex="-1"
          class="pf-c-button pf-m-plain pf-c-select__toggle-clear"
          type="button"
          aria-label="Clear all"
          @click.prevent="clearall"
        >
          <i class="fas fa-times-circle" aria-hidden="true"></i>
        </button>
        <button
          tabindex="-1"
          class="pf-c-button pf-m-plain pf-c-select__toggle-button"
          type="button"
          id="select-single-typeahead-expanded-toggle"
          aria-haspopup="true"
          aria-expanded="true"
          aria-labelledby="select-single-typeahead-expanded-label select-single-typeahead-expanded-toggle"
          aria-label="Select"
          @click.prevent="toggleSelect"
          v-on:keydown.esc="onEsc"
        >
          <i
            class="fas fa-caret-down pf-c-select__toggle-arrow"
            aria-hidden="true"
          ></i>
        </button>
      </div>
      <ul
        class="pf-c-select__menu"
        aria-labelledby="select-single-typeahead-expanded-label multi-select-dropdown-overflow"
        role="listbox"
        v-if="showSelect ? 'hidden' : ''"
      >
        <div v-if="!isLoading">
          <li role="presentation" v-for="item in searchModels" :key="item.id">
            <button
              class="pf-c-select__menu-item"
              role="option"
              @click.prevent="makeSelection(item)"
            >
              {{ item }}
              <span
                v-if="item === searchTerm"
                class="pf-c-select__menu-item-icon"
              >
                <i class="fas fa-check" aria-hidden="true"></i>
              </span>
            </button>
          </li>
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

    <p
      v-if="errors.device_model"
      class="pf-c-form__helper-text pf-m-error"
      id="device_model_error"
      aria-live="polite"
    >
      {{ errors.device_model[0] }}
    </p>
  </div>
</template>

<script>
import { ref, watch, onMounted, computed } from "vue";
import { onClickOutside } from "@vueuse/core";

export default {
  props: {
    modelValue: {
      type: String,
    },
    errors: "",
  },

  setup(props, { emit }) {
    const showSelect = ref(false);
    const clickOutsidetarget = ref(null);
    const searchTerm = ref("");
    const models = ref([]);
    const isLoading = ref(false);
    const results = ref([]);
    const selected = ref("");

    onClickOutside(clickOutsidetarget, (event) => close());

    onMounted(() => {
      getModels();
    });

    const searchModels = computed(() => {
      if (searchTerm.value === "") {
        return models.value;
      }

      let matches = 0;

      return models.value.filter((item) => {
        if (
          item.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
          matches < 10
        ) {
          matches++;
          return item;
        }
      });
    });

    function onChange() {
      makeSelection(searchTerm.value);
      showSelect.value = true;
    }

    function getModels() {
      isLoading.value = true;
      axios
        .get("/api/get-device-models")
        .then((response) => {
          models.value = response.data.data;
          results.value = models;
          isLoading.value = false;
        })
        .catch((error) => {
          console.log(error.response.data.errors);
        });
    }

    function makeSelection(model) {
      searchTerm.value = model;
      emit("update:modelValue", model);
      close();
    }

    function toggleSelect() {
      if (showSelect.value === false) {
        getModels();
      }
      showSelect.value = !showSelect.value;
    }

    function clearall() {
      searchTerm.value = "";
      showSelect.value = false;
    }

    function onEsc() {
      close();
    }

    function close() {
      showSelect.value = false;
    }

    if (props.modelValue) {
      searchTerm.value = props.modelValue;
    } else {
      searchTerm.value = "";
    }

    return {
      searchTerm,
      clickOutsidetarget,
      selected,
      makeSelection,
      toggleSelect,
      showSelect,
      models,
      results,
      searchModels,
      onChange,
      clearall,
      isLoading,
      onEsc,
    };
  },
};
</script>
