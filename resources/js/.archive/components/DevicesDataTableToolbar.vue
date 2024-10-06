<template>
  <div class="pf-c-toolbar">
    <div class="pf-c-toolbar__content">
      <div class="pf-c-toolbar__content-section pf-m-nowrap">
        <div class="pf-c-toolbar__group pf-m-toggle-group pf-m-show">
          <div class="pf-c-toolbar__item pf-m-search-filter">
            <div class="pf-c-search-input">
              <div class="pf-c-search-input__bar">
                <span class="pf-c-search-input__text">
                  <span class="pf-c-search-input__icon">
                    <i
                      class="fas fa-search fa-fw"
                      aria-hidden="true"></i>
                  </span>
                  <input
                    class="pf-c-search-input__text-input"
                    type="text"
                    placeholder="Type to Search"
                    aria-label="Type to Search"
                    v-model="searchInput"
                    @input="handleInput"
                    ref="search"
                    autocomplete="off" />
                </span>
                <span class="pf-c-search-input__utilities">
                  <span class="pf-c-search-input__clear">
                    <button
                      class="pf-c-button pf-m-plain"
                      type="button"
                      aria-label="Clear"
                      @click="clear">
                      <i
                        class="fas fa-times fa-fw"
                        aria-hidden="true"></i>
                    </button>
                  </span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <hr class="pf-c-divider pf-m-vertical pf-m-hidden pf-m-visible-on-lg" />
        <div class="pf-c-toolbar__group pf-m-filter-group pf-m-hidden pf-m-visible-on-lg">
          <div class="pf-c-toolbar__item">
            <div
              class="pf-c-select pf-m-expanded"
              ref="clickOutsidetarget1">
              <span hidden>Choose one</span>

              <button
                class="pf-c-select__toggle"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                @click.prevent="showCatFilteroptions = !showCatFilteroptions">
                <div class="pf-c-select__toggle-wrapper">
                  <span
                    class="pf-c-select__toggle-text"
                    v-text="selectedCatName ? selectedCatName : 'Categories'"></span>
                </div>
                <span class="pf-c-select__toggle-arrow">
                  <i
                    class="fas fa-caret-down"
                    aria-hidden="true"></i>
                </span>
              </button>
              <ul
                class="pf-c-select__menu multi-select-dropdown-overflow"
                role="listbox"
                v-if="showCatFilteroptions ? 'hidden' : ''">
                <li
                  role="presentation"
                  v-for="option in cats"
                  :key="option">
                  <button
                    class="pf-c-select__menu-item pf-m-selected"
                    role="option"
                    @click.prevent="setFilter('category', option.id, option.categoryName)">
                    {{ option.categoryName }}
                    <span
                      class="pf-c-select__menu-item-icon"
                      v-if="selectedCatName === option.categoryName">
                      <i
                        class="fas fa-check"
                        aria-hidden="true"></i>
                    </span>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <div class="pf-c-toolbar__item">
            <div
              class="pf-c-select pf-m-expanded"
              ref="clickOutsidetarget2">
              <span hidden>Choose one</span>

              <button
                class="pf-c-select__toggle"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                @click.prevent="showTagFilteroptions = !showTagFilteroptions">
                <div class="pf-c-select__toggle-wrapper">
                  <span
                    class="pf-c-select__toggle-text"
                    v-text="selectedTagname ? selectedTagname : 'Tag'"></span>
                </div>
                <span class="pf-c-select__toggle-arrow">
                  <i
                    class="fas fa-caret-down"
                    aria-hidden="true"></i>
                </span>
              </button>
              <ul
                class="pf-c-select__menu multi-select-dropdown-overflow"
                role="listbox"
                v-if="showTagFilteroptions ? 'hidden' : ''">
                <li
                  role="presentation"
                  v-for="option in tags"
                  :key="option">
                  <button
                    class="pf-c-select__menu-item pf-m-selected"
                    role="option"
                    @click.prevent="setFilter('tag', option.id, option.tagname)">
                    {{ option.tagname }}
                    <span
                      class="pf-c-select__menu-item-icon"
                      v-if="selectedTagname === option.tagname">
                      <i
                        class="fas fa-check"
                        aria-hidden="true"></i>
                    </span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="pf-c-toolbar__item">
            <div
              class="pf-c-select pf-m-expanded"
              ref="clickOutsidetarget3">
              <span hidden>Choose one</span>

              <button
                class="pf-c-select__toggle"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                @click.prevent="showVendorFilteroptions = !showVendorFilteroptions">
                <div class="pf-c-select__toggle-wrapper">
                  <span
                    class="pf-c-select__toggle-text"
                    v-text="selectedVendorname ? selectedVendorname : 'Vendor'"></span>
                </div>
                <span class="pf-c-select__toggle-arrow">
                  <i
                    class="fas fa-caret-down"
                    aria-hidden="true"></i>
                </span>
              </button>
              <ul
                class="pf-c-select__menu multi-select-dropdown-overflow"
                role="listbox"
                v-if="showVendorFilteroptions ? 'hidden' : ''">
                <li
                  role="presentation"
                  v-for="option in vendors"
                  :key="option">
                  <button
                    class="pf-c-select__menu-item pf-m-selected"
                    role="option"
                    @click.prevent="setFilter('vendor', option.id, option.vendorName)">
                    {{ option.vendorName }}
                    <span
                      class="pf-c-select__menu-item-icon"
                      v-if="selectedVendorname === option.vendorName">
                      <i
                        class="fas fa-check"
                        aria-hidden="true"></i>
                    </span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="pf-c-toolbar__item">
            <div
              class="pf-c-select pf-m-expanded"
              ref="clickOutsidetarget4">
              <span hidden>Choose one</span>

              <button
                class="pf-c-select__toggle"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                @click.prevent="showModelFilteroptions = !showModelFilteroptions">
                <div class="pf-c-select__toggle-wrapper">
                  <span
                    class="pf-c-select__toggle-text"
                    v-text="selectedModelname ? selectedModelname : 'Model'"></span>
                </div>
                <span class="pf-c-select__toggle-arrow">
                  <i
                    class="fas fa-caret-down"
                    aria-hidden="true"></i>
                </span>
              </button>
              <ul
                class="pf-c-select__menu multi-select-dropdown-overflow"
                role="listbox"
                v-if="showModelFilteroptions ? 'hidden' : ''">
                <li
                  role="presentation"
                  v-for="option in models"
                  :key="option">
                  <button
                    class="pf-c-select__menu-item pf-m-selected"
                    role="option"
                    @click.prevent="setFilter('device_model', option, option)">
                    {{ option }}
                    <span
                      class="pf-c-select__menu-item-icon"
                      v-if="selectedModelname === option">
                      <i
                        class="fas fa-check"
                        aria-hidden="true"></i>
                    </span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- COLUMN EDITOR -->
        <hr class="pf-c-divider pf-m-vertical" />
        <button
          class="pf-c-button pf-m-control"
          type="button"
          alt="Edit Columns"
          title="Edit Columns"
          @click="showEditColumns()">
          <i
            class="fas fa-columns"
            aria-hidden="true"></i>
          <span class="pf-u-display-none pf-u-display-inline-block-on-lg"></span>
        </button>
        <!-- COLUMN EDITOR -->

        <hr class="pf-c-divider pf-m-vertical" />
        <div class="pf-c-toolbar__group pf-m-icon-button-group">
          <div class="pf-c-toolbar__item">
            <button
              class="pf-c-button pf-m-plain"
              type="button"
              @click.prevent="clear()">
              <i
                class="fas fa-expand-arrows-alt"
                :class="activeStatus === null ? 'statusActive' : 'statusInactive'"
                alt="Show all devices"
                title="Show all devices"></i>
            </button>
          </div>
          <div class="pf-c-toolbar__item">
            <button
              class="pf-c-button pf-m-plain"
              type="button"
              @click.prevent="filterSelect('status', '1')">
              <i
                class="fas fa-check-circle pf-u-success-color-100"
                :class="activeStatus == '1' ? 'statusActive' : 'statusInactive'"
                alt="Show up devices"
                title="Show up devices"></i>
            </button>
          </div>
          <div class="pf-c-toolbar__item">
            <button
              class="pf-c-button pf-m-plain"
              type="button"
              @click.prevent="filterSelect('status', '0')">
              <i
                class="fas fa-exclamation-triangle pf-u-warning-color-100"
                :class="activeStatus == '0' ? 'statusActive' : 'statusInactive'"
                alt="Show down devices"
                title="Show down devices"></i>
            </button>
          </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="pf-c-toolbar__item pf-m-pagination">
          <div
            class="pf-c-overflow-menu"
            id="-overflow-menu">
            <div class="pf-c-overflow-menu__content pf-u-display-none pf-u-display-flex-on-lg">
              <div class="pf-c-overflow-menu__group pf-m-button-group">
                <div class="pf-c-overflow-menu__item">
                  <button
                    class="pf-c-button pf-m-primary"
                    type="button"
                    @click="openDrawer(0)">
                    New {{ pagename }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ACTION BUTTONS -->
      </div>

      <div
        class="pf-c-toolbar__expandable-content pf-m-hidden"
        id="-expandable-content"
        hidden=""></div>
    </div>

    <!-- FILTER CHIPS -->
    <div
      class="pf-c-toolbar__content pf-m-chip-container"
      v-if="selectedCatName || selectedModelname || selectedVendorname || selectedTagname">
      <div class="pf-c-toolbar__group">
        <div class="pf-c-toolbar__item pf-m-chip-group">
          <div class="pf-c-chip-group pf-m-category">
            <div class="pf-c-chip-group__main">
              <span
                class="pf-c-chip-group__label"
                aria-hidden="true">
                Filter
                <span v-if="selectedCatName">category:</span>
                <span v-if="selectedModelname">model:</span>
                <span v-if="selectedVendorname">vendor:</span>
                <span v-if="selectedTagname">tag:</span>
              </span>
              <ul
                class="pf-c-chip-group__list"
                role="list">
                <li
                  class="pf-c-chip-group__list-item"
                  v-if="selectedCatName">
                  <div class="pf-c-chip">
                    <span class="pf-c-chip__text">{{ selectedCatName }}</span>
                    <button
                      class="pf-c-button pf-m-plain"
                      type="button"
                      @click="clear()">
                      <i
                        class="fas fa-times"
                        aria-hidden="true"></i>
                    </button>
                  </div>
                </li>
                <li
                  class="pf-c-chip-group__list-item"
                  v-if="selectedModelname">
                  <div class="pf-c-chip">
                    <span class="pf-c-chip__text">{{ selectedModelname }}</span>
                    <button
                      class="pf-c-button pf-m-plain"
                      type="button"
                      @click="clear()">
                      <i
                        class="fas fa-times"
                        aria-hidden="true"></i>
                    </button>
                  </div>
                </li>
                <li
                  class="pf-c-chip-group__list-item"
                  v-if="selectedVendorname">
                  <div class="pf-c-chip">
                    <span class="pf-c-chip__text">{{ selectedVendorname }}</span>
                    <button
                      class="pf-c-button pf-m-plain"
                      type="button"
                      @click="clear()">
                      <i
                        class="fas fa-times"
                        aria-hidden="true"></i>
                    </button>
                  </div>
                </li>
                <li
                  class="pf-c-chip-group__list-item"
                  v-if="selectedTagname">
                  <div class="pf-c-chip">
                    <span class="pf-c-chip__text">{{ selectedTagname }}</span>
                    <button
                      class="pf-c-button pf-m-plain"
                      type="button"
                      @click="clear()">
                      <i
                        class="fas fa-times"
                        aria-hidden="true"></i>
                    </button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="pf-c-toolbar__item">
        <button
          class="pf-c-button pf-m-link pf-m-inline"
          type="button"
          @click="clear()">
          Clear all filters
        </button>
      </div>
    </div>
    <!-- FILTER CHIPS -->
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import useGetAllModeResults from '../.archive/composables/AllModelResultsFactory';
import { onClickOutside } from '@vueuse/core';

export default {
  props: {
    pagename: {
      type: String,
      default: 'rConfig'
    }
  },

  setup(props, { emit }) {
    const search = ref(null);
    const searchInput = ref('');
    const activeStatus = ref(null);
    const showCatFilteroptions = ref(false);
    const clickOutsidetarget1 = ref(null);
    const selectedCatName = ref(null);
    const clickOutsidetarget2 = ref(null);
    const showTagFilteroptions = ref(false);
    const selectedTagname = ref(null);
    const clickOutsidetarget3 = ref(null);
    const showVendorFilteroptions = ref(false);
    const selectedVendorname = ref(null);
    const clickOutsidetarget4 = ref(null);
    const showModelFilteroptions = ref(false);
    const selectedModelname = ref(null);

    const { results: getTags, isLoading: tagsIsLoading } = useGetAllModeResults('tags');
    const { results: getCats, isLoading: catsIsLoading } = useGetAllModeResults('categories');
    const { results: getVendors, isLoading: vendorsIsLoading } = useGetAllModeResults('vendors');
    const { results: getModels, isLoading: modelsIsLoading } = useGetAllModeResults('get-device-models');

    onClickOutside(clickOutsidetarget1, event => (showCatFilteroptions.value = false));
    onClickOutside(clickOutsidetarget2, event => (showTagFilteroptions.value = false));
    onClickOutside(clickOutsidetarget3, event => (showVendorFilteroptions.value = false));
    onClickOutside(clickOutsidetarget4, event => (showModelFilteroptions.value = false));

    // console.log(getTags);
    // console.log(getCats);
    // console.log(getVendors);
    // console.log(getModels);

    function filterSelect(type, id) {
      var obj = { [type]: id };

      if (type === 'status') {
        activeStatus.value = id;
      }

      let jsonObj = JSON.stringify(obj);
      emit('searchInput', jsonObj);
    }

    function openDrawer(id) {
      emit('openDrawer', id);
    }

    function handleInput(e) {
      emit('searchInput', e.target.value);
    }

    function showEditColumns() {
      emit('showEditColumns');
    }

    function setFilter(type, id, name) {
      if (type === 'category') {
        selectedCatName.value = name;
        selectedTagname.value = false;
        selectedVendorname.value = false;
        selectedModelname.value = false;
        showCatFilteroptions.value = false;
      }
      if (type === 'tag') {
        selectedTagname.value = name;
        selectedCatName.value = false;
        selectedVendorname.value = false;
        selectedModelname.value = false;
        showTagFilteroptions.value = false;
      }
      if (type === 'vendor') {
        selectedVendorname.value = name;
        selectedCatName.value = false;
        selectedTagname.value = false;
        selectedModelname.value = false;
        showVendorFilteroptions.value = false;
      }
      if (type === 'device_model') {
        selectedModelname.value = name;
        selectedCatName.value = false;
        selectedTagname.value = false;
        selectedVendorname.value = false;
        showModelFilteroptions.value = false;
      }

      var obj = { [type]: id };
      let jsonObj = JSON.stringify(obj);
      emit('searchInput', jsonObj);
    }

    function clear() {
      selectedModelname.value = false;
      selectedCatName.value = false;
      selectedTagname.value = false;
      selectedVendorname.value = false;
      activeStatus.value = null;
      searchInput.value = '';
      emit('searchInput', searchInput.value);
    }

    return {
      activeStatus,
      clear,
      filterSelect,
      handleInput,
      openDrawer,
      search,
      searchInput,
      showEditColumns,
      setFilter,
      tags: getTags,
      tagsIsLoading,
      cats: getCats,
      catsIsLoading,
      vendors: getVendors,
      vendorsIsLoading,
      models: getModels,
      modelsIsLoading,
      showCatFilteroptions,
      clickOutsidetarget1,
      selectedCatName,
      clickOutsidetarget2,
      showTagFilteroptions,
      selectedTagname,
      clickOutsidetarget3,
      showVendorFilteroptions,
      selectedVendorname,
      clickOutsidetarget4,
      showModelFilteroptions,
      selectedModelname
    };
  }
};
</script>

<style scoped>
.statusActive {
  opacity: 1;
}
.statusInactive {
  opacity: 0.3;
}
.statusInactive:hover {
  opacity: 0.5;
}
</style>
