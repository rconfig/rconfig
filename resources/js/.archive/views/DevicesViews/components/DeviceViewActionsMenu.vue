<template>
  <div>
    <div class="pf-c-card">
      <div class="pf-c-card__title">
        <h2 class="pf-c-title pf-m-xl">Common Actions</h2>
      </div>
      <div class="pf-c-menu">
        <div class="pf-c-menu__content">
          <ul class="pf-c-menu__list">
            <li class="pf-c-menu__list-item">
              <button
                class="pf-c-menu__item"
                type="button"
                @click="copyDebug('cd ' + appDirPath + ' && php artisan rconfig:download-device ' + model.id + ' -d')">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i
                      class="fas fa-code"
                      aria-hidden="true"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Copy debug CLI command</span>
                </span>
                <span class="pf-c-menu__item-description pf-u-text-break-word">Copy command for CLI debug to clipboard</span>
              </button>
            </li>
            <li class="pf-c-menu__list-item">
              <router-link
                :to="{ path: '/device/view/configs/' + model.id, query: { id: model.id, devicename: model.device_name, status: 'all' } }"
                class="pf-c-menu__item"
                type="button">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i
                      class="pficon pf-icon-storage-domain"
                      aria-hidden="true"></i>
                  </span>
                  <span class="pf-c-menu__item-text">View configuration downloads</span>
                </span>
                <span class="pf-c-menu__item-description">View configuration files for this device</span>
              </router-link>
            </li>
            <li class="pf-c-menu__list-item">
              <button
                class="pf-c-menu__item"
                @click="downloadNow()">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i
                      class="pficon pf-icon-save"
                      aria-hidden="true"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Download now</span>
                </span>
                <span
                  class="pf-c-menu__item-description pf-u-text-break-word"
                  style="word-wrap: normal">
                  Start a download for this device
                </span>
              </button>
            </li>

            <li
              class="pf-c-menu__list-item"
              v-if="downloadStatus"
              style="cursor: auto">
              <button class="pf-c-menu__item">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <svg
                      class="pf-c-spinner pf-m-md"
                      role="progressbar"
                      viewBox="0 0 100 100"
                      aria-label="Loading..."
                      v-if="downloadStatus != 'The job finished, check configs and logs for details.'">
                      <circle
                        class="pf-c-spinner__path"
                        cx="50"
                        cy="50"
                        r="45"
                        fill="none" />
                    </svg>
                    <i
                      class="fa fa-check-circle pf-u-success-color-100"
                      aria-hidden="true"
                      v-if="downloadStatus === 'The job finished, check configs and logs for details.'"></i>
                    <i
                      class="fa fa-exclamation-circle pf-u-danger-color-100"
                      aria-hidden="true"
                      v-if="downloadStatus === 'Failed'"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Download status</span>
                </span>
                <span
                  class="pf-c-menu__item-description pf-u-text-break-word"
                  style="word-wrap: normal">
                  {{ downloadStatus }}
                </span>
              </button>
            </li>

            <li class="pf-c-menu__list-item">
              <button
                class="pf-c-menu__item"
                @click="openDrawer(model.id, true)">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i
                      class="fas fa-copy"
                      aria-hidden="true"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Clone device</span>
                </span>
                <span
                  class="pf-c-menu__item-description pf-u-text-break-word"
                  style="word-wrap: normal">
                  Create a new device with similar configuration
                </span>
              </button>
            </li>

            <li class="pf-c-menu__list-item">
              <button
                class="pf-c-menu__item"
                @click="purgeFailedConfigs()">
                <span class="pf-c-menu__item-main">
                  <span class="pf-c-menu__item-icon">
                    <i
                      class="fas fa-trash"
                      aria-hidden="true"></i>
                  </span>
                  <span class="pf-c-menu__item-text">Purge failed configs</span>
                </span>
                <span
                  class="pf-c-menu__item-description pf-u-text-break-word"
                  style="word-wrap: normal">
                  Purge all Failed Configs for this device
                </span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, inject, onMounted } from 'vue';
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
    const appDirPath = ref(false);
    const downloadStatus = ref(null);

    onMounted(() => {
      getAppDirPath();
    });

    function getAppDirPath() {
      axios.get('/api/app-dir-path').then(response => {
        appDirPath.value = response.data;
      });
    }

    function downloadNow() {
      downloadStatus.value = 'Downloading...';
      createNotification({
        type: 'success',
        title: 'Download Started',
        message: 'Download started for device ' + props.model.device_name
      });
      axios
        .post('/api/device/download-now', {
          device_id: props.model.id
        })
        .then(response => {
          downloadStatus.value = 'Queued...';
          createNotification({
            type: 'success',
            title: 'Download Started',
            message: 'Download job for ' + props.model.device_name + ' was pushed to the queue.'
          });
          checkTrackedJobStatus();
        })
        .catch(error => {
          createNotification({
            type: 'danger',
            title: 'Error',
            message: error.response.data.message
          });
        });
    }

    function checkTrackedJobStatus() {
      const interval = setInterval(function () {
        axios.get('/api/tracked-jobs/' + props.model.id).then(response => {
          downloadStatus.value = response.data.data.status;
          // console.log(downloadStatus.value);
        });

        if (downloadStatus.value === 'finished') {
          // console.log('checkTrackedJobStatus finished');
          createNotification({
            type: 'success',
            title: 'Download Finished',
            message: 'Download finished for device ' + props.model.device_name
          });
          clearInterval(interval); // thanks @Luca D'Amico
          setTimeout(() => {
            downloadStatus.value = null;
          }, 3000);
        }
      }, 2000);
    }

    function purgeFailedConfigs() {
      axios
        .post('/api/device/purge-failed-configs', {
          device_id: props.model.id
        })
        .then(response => {
          createNotification({
            type: 'success',
            title: 'Purge Successful',
            message: 'Purge successful for device ' + props.model.device_name
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

    function copyDebug(value) {
      try {
        toClipboard(value);
        createNotification({
          type: 'success',
          message: 'Copied to clipboard!',
          duration: 3
        });
      } catch (error) {
        createNotification({
          type: 'danger',
          title: 'Error',
          message: error.response
        });
      }
    }

    function openDrawer(id, isClone = false) {
      emit('openDrawer', { id: id, isClone: isClone });
    }

    return {
      appDirPath,
      copyDebug,
      downloadNow,
      downloadStatus,
      openDrawer,
      purgeFailedConfigs
    };
  }
};
</script>
