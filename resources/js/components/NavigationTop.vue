<template>
  <header class="pf-c-page__header">
    <div class="pf-c-page__header-brand">
      <div class="pf-c-page__header-brand-toggle">
        <button
          class="pf-c-button pf-m-plain"
          type="button"
          id="primary-detail-panel-body-padding-nav-toggle"
          aria-label="Global navigation"
          aria-expanded="true"
          aria-controls="primary-detail-panel-body-padding-primary-nav"
          @click="changeNavState"
        >
          <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
      </div>
      <a href="#" class="pf-c-page__header-brand-link">
        <img
          class="pf-c-brand"
          src="/images/new/white/hex_logo_white_horizontal_256.png"
          alt="rConfig logo"
        />
      </a>
    </div>
    <div class="pf-c-page__header-tools">
      <div class="pf-c-page__header-tools-group">
        <div
          class="pf-c-page__header-tools-item pf-m-hidden pf-m-visible-on-lg"
          alt="settings"
          title="settings"
        >
          <router-link
            tag="button"
            class="pf-c-button pf-m-plain"
            to="/settings/overview"
            exact
          >
            <i class="fas fa-cog" aria-hidden="true"></i>
          </router-link>
        </div>

        <div
          class="pf-c-page__header-tools-item pf-m-hidden pf-m-visible-on-lg"
          alt="help"
          title="help"
        >
          <button
            class="pf-c-button pf-m-plain"
            type="button"
            aria-label="Help"
            @click="showAboutModal = true"
          >
            <i class="pf-icon pf-icon-help" aria-hidden="true"></i>
          </button>
        </div>
        <div
          class="pf-c-page__header-tools-item pf-m-hidden pf-m-visible-on-lg"
          alt="logout"
          title="logout"
        >
          <a href="" @click.prevent="logout()">
            <button
              class="pf-c-button pf-m-plain"
              type="button"
              aria-label="Sign out"
            >
              <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
            </button>
          </a>
        </div>
      </div>
      <div class="pf-c-page__header-tools-group">
        <div
          class="pf-c-page__header-tools-item pf-m-hidden pf-m-visible-on-md"
        >
          <div class="pf-c-dropdown">
            <router-link
              tag="button"
              class="pf-c-dropdown__toggle pf-m-plain"
              :to="'/settings/users/' + $userId"
              id="primary-detail-panel-body-padding-dropdown-button"
              >{{ $userName }}</router-link
            >
          </div>
        </div>
      </div>
    </div>
  </header>
  <modal-about
    v-if="showAboutModal"
    @close="showAboutModal = false"
  ></modal-about>
</template>

<script>
import ModalAbout from "./ModalAbout.vue";
import axios from "axios";
import { onClickOutside } from "@vueuse/core";
import { onMounted, ref, watch, inject } from "vue";
import { useNavState } from "./../composables/navstate";

export default {
  props: {},
  components: {
    ModalAbout,
  },
  setup(props) {
    const showAboutModal = ref(false);
    const { darkmode, globalState, localState, changeNavState } = useNavState();

    const clickOutsidetargetSearchResults = ref(null);
    const createNotification = inject("create-notification");

    onClickOutside(clickOutsidetargetSearchResults, (event) => closeSearch());

    function logout() {
      console.log("logout");
      axios
        .post("/logout")
        .then((response) => {
          window.location.href = "/login";
        })
        .catch((error) => {
          console.log(error);
        });
    }

    return {
      changeNavState,
      clickOutsidetargetSearchResults,
      globalState,
      localState,
      logout,
      showAboutModal,
    };
  },
};
</script>
<style scoped>
.updateBadge {
  background-color: var(--pf-global--palette--cyan-300);
}
.updateBadge:hover {
  background-color: var(--pf-global--palette--cyan-400);
}
/* Search */
#ws-global-search-wrapper {
  /* For icon */
  position: relative;
}
#algolia-autocomplete-listbox-0 {
  /* Fix search results overflowing page */
  min-width: 480px !important;
}
#ws-global-search {
  background-color: transparent;
  border: none;
  /* For icon */
  padding-left: var(--pf-global--spacer--xl);
  width: 200px;
}
.ws-hide-search-input .algolia-autocomplete,
.ws-hide-search-input #ws-global-search {
  display: none !important;
}
.ws-toggle-search {
  /* Search icon is taller than it is wide */
  margin-top: 2px;
}
#ws-global-search-wrapper > .global-search-icon {
  position: absolute;
  top: 10px;
  left: 4px;
}
#ws-global-search::-moz-placeholder {
  color: #fff;
}
#ws-global-search:-ms-input-placeholder {
  color: #fff;
}
#ws-global-search,
#ws-global-search::placeholder {
  color: #fff;
}
#ws-global-search:focus {
  outline-offset: 2px;
}

/* TODO: PageHeaderTools on small viewports */
@media (max-width: 670px) {
  #ws-global-search-wrapper,
  .ws-toggle-search {
    display: none;
    visibility: hidden;
  }
}
</style>
