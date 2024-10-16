<template>
  <div class="pf-c-wizard__main-body">
    <h4>
      <strong class="pf-u-color-300">Step 4 - Task Schedule</strong>
    </h4>
    <task-wizard-task-not-selected
      v-if="wizardData.selectedTask.id === 0"
      @goToPageOne="wizardData.currentPage = 1"></task-wizard-task-not-selected>

    <div v-else>
      <form
        novalidate
        class="pf-c-form pf-m-horizontal pf-u-pt-xl"
        style="max-width: 75%">
        <div class="pf-c-form__group">
          <div class="pf-c-form__group-label">
            <label
              class="pf-c-form__label"
              for="form-demo-basic-name">
              <span class="pf-c-form__label-text">Cron Examples</span>
            </label>
          </div>
          <div class="pf-c-form__group-control">
            <select
              id="exampleOptions"
              class="pf-c-form-control"
              @change="selectExample($event)">
              <option value="--">-- Select an option --</option>
              <option value="* * * * *">Every minute (* * * * *)</option>
              <option value="*/5 * * * *">Every 5 minutes (*/5 * * * *)</option>
              <option value="0,30 * * * *">Twice an hour (0,30 * * * *)</option>
              <option value="0 * * * *">Once an hour (0 * * * *)</option>
              <option value="0 0,12 * * *">Twice a day (0 0,12 * * *)</option>
              <option value="0 0 * * *">Once a day (0 0 * * *)</option>
              <option value="0 0 * * 0">Once a week (0 0 * * 0)</option>
              <option value="0 0 1,15 * *">1st and 15th (0 0 1,15 * *)</option>
              <option value="0 0 1 * *">Once a month (0 0 1 * *)</option>
              <option value="0 0 1 1 *">Once a year (0 0 1 1 *)</option>
            </select>
          </div>
        </div>
        <task-cron-form :cronProp="model.task_cron"></task-cron-form>
      </form>
      <div class="pf-u-pt-xl">
        <span
          class="pf-c-label pf-m-blue"
          v-if="cronToHuman != ''">
          <span class="pf-c-label__content">
            <span class="pf-c-label__icon">
              <i
                class="fas fa-fw fa-info-circle"
                aria-hidden="true"></i>
            </span>

            <b>Task Schedule is set:</b>
            &nbsp; {{ cronToHuman }}
          </span>
        </span>
      </div>
      <label
        v-if="wizardData.selectedTask.id != 0"
        class="pf-c-switch pf-m-reverse pf-u-pt-xl"
        for="task_email_notifyswitch-reverse-1">
        <input
          class="pf-c-switch__input"
          type="checkbox"
          id="task_email_notifyswitch-reverse-1"
          aria-labelledby="task_email_notifyswitch-reverse-1-on"
          name="switchExample1"
          v-model="model.task_email_notify" />

        <span class="pf-c-switch__toggle"></span>

        <span
          class="pf-c-switch__label pf-m-on"
          id="task_email_notifyswitch-reverse-1-on"
          aria-hidden="true">
          Send task email notification on
        </span>

        <span
          class="pf-c-switch__label pf-m-off"
          id="task_email_notifyswitch-reverse-1-off"
          aria-hidden="true">
          Send task email notification off
        </span>
      </label>
    </div>
  </div>
</template>

<script>
import TaskCronForm from './TaskCronForm.vue';
import TaskWizardTaskNotSelected from './TaskWizardTaskNotSelected.vue';
import cronstrue from 'cronstrue';
import { onMounted, ref, watchEffect } from 'vue';

export default {
  props: {
    wizardData: {
      type: Object
    },
    model: {
      type: Object
    }
  },
  components: {
    TaskCronForm,
    TaskWizardTaskNotSelected
  },
  setup(props) {
    const cronExampleArray = ref([]);
    const cronToHuman = ref('');

    function selectExample(event) {
      var exampleCron = event.target.value;
      var array = exampleCron.split(' ');
      props.model.task_cron = array;
    }

    onMounted(() => {
      if (props.model.task_cron != '') {
        cronExampleArray.value = props.model.task_cron;
      }
    });

    watchEffect(() => {
      if (props.model.task_cron) {
        cronToHuman.value = cronstrue.toString(props.model.task_cron.join(' '));
      }
    });

    return { cronExampleArray, selectExample, cronToHuman };
  }
};
</script>
