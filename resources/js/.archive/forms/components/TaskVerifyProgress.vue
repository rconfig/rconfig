<template>
  <div class="pf-u-pt-lg">
    <div
      class="pf-c-progress"
      style="max-width: 75%"
      v-if="progressStatus === 'checking'">
      <div class="pf-c-progress__description">Task Verification</div>
      <div
        class="pf-c-progress__status"
        aria-hidden="true">
        <span class="pf-c-progress__measure">{{ progressCount + '%' }}</span>
      </div>
      <div
        class="pf-c-progress__bar"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="10"
        aria-valuenow="4"
        aria-valuetext="Verifying Task Data">
        <div
          class="pf-c-progress__indicator"
          :style="'width: ' + progressCount + '%'"></div>
      </div>
    </div>

    <div
      class="pf-c-progress pf-m-inside pf-m-success"
      style="max-width: 75%"
      v-if="progressStatus === 'success'">
      <div class="pf-c-progress__description">Task Verification Success</div>
      <div
        class="pf-c-progress__status"
        aria-hidden="true">
        <span class="pf-c-progress__status-icon">
          <i
            class="fas fa-fw fa-check-circle"
            aria-hidden="true"></i>
        </span>
      </div>
      <div
        class="pf-c-progress__bar"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="100">
        <div
          class="pf-c-progress__indicator"
          style="width: 100%">
          <span class="pf-c-progress__measure">100%</span>
        </div>
      </div>
    </div>

    <div
      class="pf-c-progress pf-m-danger"
      style="max-width: 75%"
      v-if="progressStatus === 'error'">
      <div class="pf-c-progress__description">Task Verification Failed</div>
      <div
        class="pf-c-progress__status"
        aria-hidden="true">
        <span class="pf-c-progress__measure">100%</span>
        <span class="pf-c-progress__status-icon">
          <i
            class="fas fa-fw fa-times-circle"
            aria-hidden="true"></i>
        </span>
      </div>
      <div
        class="pf-c-progress__bar"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="100">
        <div
          class="pf-c-progress__indicator"
          :style="'width: 100%'"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watchEffect } from 'vue';

export default {
  props: {
    status: {
      type: String,
      required: true
    }
  },

  setup(props, { emit }) {
    const progressCount = ref(0);
    const interval = ref(null);
    const statusOptions = ref({
      checking: 'checking',
      success: 'success',
      error: 'error'
    });
    const progressStatus = ref('checking');

    onMounted(() => {
      interval.value = setInterval(frame, 10);
    });

    function frame() {
      if (progressCount.value >= 100) {
        clearInterval(interval.value);
        progressCount.value = 0;
      } else {
        progressCount.value++;
      }
    }

    watchEffect(() => {
      if (props.status === statusOptions.value.checking && progressCount.value < 100) {
        progressStatus.value = 'checking';
      } else if (props.status === statusOptions.value.success && progressCount.value === 100) {
        progressStatus.value = 'success';
      } else if (props.status === statusOptions.value.error && progressCount.value === 100) {
        progressStatus.value = 'error';
      }
    });

    watchEffect(() => {
      if (progressStatus.value != 'checking') {
        emit('verificationDone');
      }
    });

    return {
      progressCount,
      progressStatus
    };
  }
};
</script>
