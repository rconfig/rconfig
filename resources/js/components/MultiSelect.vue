<template>
  <div class="pf-c-form__group">
    <div class="pf-c-form__group-label">
      <label class="pf-c-form__label" for="form-demo-basic-name">
        <span class="pf-c-form__label-text">Select {{ fieldType }}</span>
        <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
      </label>
    </div>
    <div
      class="pf-c-select pf-m-expanded"
      :class="errors ? 'pf-m-invalid' : ''"
    >
      <span
        id="select-multi-typeahead-label"
        v-if="showMultiSelect ? 'hidden' : ''"
        ref="clickOutsideMultiSelect"
      ></span>
      <div class="pf-c-select__toggle pf-m-typeahead" style="cursor: default">
        <div class="pf-c-select__toggle-wrapper">
          <div class="pf-c-chip-group">
            <div class="pf-c-chip-group__main">
              <ul
                class="pf-c-chip-group__list"
                role="list"
                aria-label="Chip group list"
              >
                <li
                  class="pf-c-chip-group__list-item"
                  v-for="selectedOption in selectedOptions"
                  :key="selectedOption"
                >
                  <div class="pf-c-chip">
                    <span class="pf-c-chip__text">
                      <!-- {{ options[selectedOption][msLabel] }} -->
                      {{ selectedOption[msLabel] }}
                    </span>
                    <button
                      class="pf-c-button pf-m-plain"
                      type="button"
                      aria-labelledby="remove_select-multi-typeahead-expanded_chip_three select-multi-typeahead-expanded-chip_three"
                      aria-label="Remove"
                      @click.prevent="
                        toggleSelectedOptions(selectedOption, true)
                      "
                    >
                      <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          <input
            class="pf-c-form-control pf-c-select__toggle-typeahead"
            type="text"
            id="select-multi-typeahead-expanded-typeahead"
            aria-label="Type to filter"
            :placeholder="'Choose ' + fieldType"
            @input="getInput($event)"
            autocomplete="off"
          />
        </div>
        <button
          tabindex="-1"
          class="pf-c-button pf-m-plain pf-c-select__toggle-clear"
          type="button"
          aria-label="Clear all"
          @click="clearText"
        >
          <i class="fas fa-times-circle" aria-hidden="true"></i>
        </button>
        <button
          class="pf-c-button pf-m-plain pf-c-select__toggle-button"
          type="button"
          id="select-multi-typeahead-toggle"
          aria-haspopup="true"
          aria-expanded="false"
          aria-labelledby="select-multi-typeahead-label select-multi-typeahead-toggle"
          aria-label="Select"
          @click="toggleMultiSelect"
        >
          <i
            class="fas fa-caret-down pf-c-select__toggle-arrow"
            aria-hidden="true"
          ></i>
        </button>
      </div>
      <ul
        class="pf-c-select__menu"
        aria-labelledby="select-multi-typeahead-label multi-select-dropdown-overflow"
        role="listbox"
        v-if="showMultiSelect ? 'hidden' : ''"
      >
        <li
          role="presentation"
          v-for="(option, index) in filteredOptions"
          :key="index"
        >
          <button
            class="pf-c-select__menu-item"
            role="option"
            @click.prevent="toggleSelectedOptions(option)"
          >
            {{ option[msLabel] }}
            <span
              v-if="selectedOptions.includes(option)"
              class="pf-c-select__menu-item-icon"
            >
              <i class="fas fa-check" aria-hidden="true"></i>
            </span>
          </button>
        </li>
      </ul>
    </div>
    <div class="pf-c-form__helper-text">
      <slot name="multi-select-subtext"></slot>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from "vue";
import { onClickOutside } from "@vueuse/core";

export default {
  props: {
    modelOptions: {
      type: Object,
    },
    options: {
      type: Object,
      required: true,
    },
    msLabel: {
      type: String,
      required: true,
    },
    msValue: {
      type: String,
      required: true,
    },
    fieldType: {
      type: String,
      required: true,
    },
    errors: false,
    keepOpenOnSelect: false,
  },

  setup(props, { emit }) {
    const showMultiSelect = ref(false);
    const filteredOptions = ref();
    const selectedOptions = reactive([]);
    const selectedOptionSet = reactive([]);
    const clickOutsideMultiSelect = ref(null);

    filteredOptions.value = props.options;

    function toggleMultiSelect() {
      showMultiSelect.value = !showMultiSelect.value;
    }

    function toggleSelectedOptions(option, isChips = false) {
      const index = selectedOptions.indexOf(option);
      if (index > -1) {
        selectedOptions.splice(index, 1);
        selectedOptionSet.splice(index, 1);
      } else {
        selectedOptions.push(option);
        selectedOptionSet.push(option);
      }
      if (props.keepOpenOnSelect && !isChips) {
        showMultiSelect.value = true;
      }
      emit("optionsUpdated", selectedOptions);
    }

    function clearSelected() {
      selectedOptions.splice(0, selectedOptions.length);
    }
    function clearText() {
      filteredOptions.value = props.options;
      document.getElementById(
        "select-multi-typeahead-expanded-typeahead"
      ).value = "";
    }

    function getInput(e) {
      showMultiSelect.value = true;
      // filter options based on input
      filteredOptions.value = [];
      Object.keys(props.options).forEach((key) => {
        if (
          props.options[key][props.msLabel]
            .toLowerCase()
            .includes(e.target.value.toLowerCase())
        ) {
          filteredOptions.value.push(props.options[key]);
        }
      });
    }

    onClickOutside(
      clickOutsideMultiSelect,
      (event) => (showMultiSelect.value = false)
    );

    if (props.modelOptions) {
      props.modelOptions.forEach((option) => {
        selectedOptions.push(option);
        selectedOptionSet.push(option.id);
      });
    }

    return {
      clearSelected,
      clearText,
      clickOutsideMultiSelect,
      filteredOptions,
      getInput,
      selectedOptions,
      showMultiSelect,
      toggleMultiSelect,
      toggleSelectedOptions,
    };
  },
};
</script>
