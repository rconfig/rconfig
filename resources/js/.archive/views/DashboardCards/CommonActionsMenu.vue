<template>
  <div>
    <div class="pf-c-card">
      <div class="pf-c-card__title">
        <h2 class="pf-c-title pf-m-lg">Common Actions</h2>
      </div>
      <div class="pf-c-menu">
        <div class="pf-c-menu__content">
          <ul class="pf-c-menu__list">
            <li class="pf-c-menu__list-item">
              <router-link
                to="/devices"
                class="pf-c-menu__item"
                type="button">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i class="fas fa-plus-circle"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Create a new device</span>
                </span>
                <span class="pf-c-menu__item-description">Setup a new devices in rConfig</span>
              </router-link>
            </li>
            <li class="pf-c-menu__list-item">
              <router-link
                to="/devices"
                class="pf-c-menu__item"
                type="button">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i class="pficon pf-icon-storage-domain"></i>
                  </span>
                  <span class="pf-c-menu__item-text">View Devices</span>
                </span>
                <span class="pf-c-menu__item-description pf-u-text-break-word">View all devices</span>
              </router-link>
            </li>
            <li class="pf-c-menu__list-item">
              <router-link
                to="/config-search"
                class="pf-c-menu__item"
                type="button">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i class="fas fa-search"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Search Configs</span>
                </span>
                <span class="pf-c-menu__item-description pf-u-text-break-word">Search multiple configuration files</span>
              </router-link>
            </li>

            <li class="pf-c-menu__list-item">
              <button
                class="pf-c-menu__item"
                @click="purgeFailedConfigs()">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i class="fas fa-trash"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Purge Failed Configs</span>
                </span>
                <span class="pf-c-menu__item-description pf-u-text-break-word">Purge all Failed Configs for all devices</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, inject } from 'vue';
import useClipboard from 'vue-clipboard3';

export default {
  props: {
    model: {
      type: Object,
      default: () => ({})
    }
  },
  components: {},

  setup(props, { emit }) {
    const createNotification = inject('create-notification');
    const { toClipboard } = useClipboard();

    function purgeFailedConfigs() {
      axios
        .post('/api/device/purge-failed-configs', {
          device_id: '--all'
        })
        .then(response => {
          createNotification({
            type: 'success',
            title: 'Purge Successful',
            message: 'Purge failed config files for all devices.'
          });
        })
        .catch(error => {
          createNotification({
            type: 'danger',
            title: 'Error',
            message: error.response.data.message
          });
        });
    }

    return {
      purgeFailedConfigs
    };
  }
};
</script>
