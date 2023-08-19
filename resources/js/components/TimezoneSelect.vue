<template>
  <div class="pf-c-select pf-m-expanded">
    <span
      id="select-single-expanded-label"
      v-if="showSelect ? 'hidden' : ''"
      ref="clickOutsideSelect"
      >Choose one</span
    >

    <button class="pf-c-select__toggle" type="button" @click="toggleSelect">
      <div class="pf-c-select__toggle-wrapper">
        <span class="pf-c-select__toggle-text">{{ currentTimezone }}</span>
      </div>
      <span class="pf-c-select__toggle-arrow">
        <i class="fas fa-caret-down" aria-hidden="true"></i>
      </span>
    </button>

    <ul
      class="pf-c-select__menu multi-select-dropdown-overflow"
      role="listbox"
      aria-labelledby="select-single-expanded-label"
      v-if="showSelect ? 'hidden' : ''"
      style="z-index: 1000; position: relative"
    >
      <li
        role="presentation"
        v-for="(HumanTimeZone, timezone) in timezones"
        :key="HumanTimeZone"
      >
        <button
          class="pf-c-select__menu-item"
          role="option"
          @click="changeTimezone(timezone)"
        >
          {{ HumanTimeZone }}
          <span
            v-if="timezone === currentTimezone"
            class="pf-c-select__menu-item-icon"
          >
            <i class="fas fa-check" aria-hidden="true"></i>
          </span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from "vue";
import { onClickOutside } from "@vueuse/core";

export default {
  props: { currentTimezone: { type: String, required: true } },

  setup(props, { emit }) {
    const showSelect = ref(false);
    const timezones = reactive({});
    const clickOutsideSelect = ref(null);

    onMounted(() => {
      getTimezonelist();
    });

    function toggleSelect() {
      showSelect.value = !showSelect.value;
    }

    function changeTimezone(timezone) {
      axios
        .patch("/api/settings/timezone/1", {
          timezone: timezone,
        })
        .then((response) => {
          // handle success
          var msg = "Timezone offset set to " + timezone;
          emit("timezoneSetSuccess", { msg: msg, timezone: timezone });
        })
        .catch((error) => {
          // handle error
          console.log(error);
          this.$emit("timezoneSetError", error);
        });
    }

    function getTimezonelist() {
      axios
        .get("/api/settings/get-timezone-list")
        .then((response) => {
          Object.assign(timezones, response.data);
        })
        .catch((error) => {
          // this.isBusy = false;
          // Returning an empty array, allows table to correctly handle
          // internal busy state in case of error
          return [];
        });
    }

    onClickOutside(clickOutsideSelect, (event) => (showSelect.value = false));

    return {
      changeTimezone,
      clickOutsideSelect,
      timezones,
      showSelect,
      toggleSelect,
    };
  },
};
</script>
