<script setup>
import { ref, defineEmits } from 'vue';

const selectedValue = ref(null);
const emit = defineEmits(['itemChecked']);

const props = defineProps({
  model: Object
});

const handleCheck = value => {
  selectedValue.value = value;
  emit('itemChecked', value);
};
</script>

<template>
  <div>
    STEP5
    <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Finalize Task</h3>
    <!-- <pre> {{ model }}</pre> -->

    <div class="container">
      <div class="card">
        <h2>Task Details</h2>
        <p>
          <strong>Task Name:</strong>
          <span class="badge">{{ model.task_name }}</span>
        </p>
        <p>
          <strong>Description:</strong>
          {{ model.task_desc || 'No description provided' }}
        </p>
        <p>
          <strong>Command:</strong>
          {{ model.task_command }}
        </p>
        <p>
          <strong>Categories:</strong>
          {{ model.task_categories }}
        </p>
        <p>
          <strong>Devices:</strong>
          {{ model.task_devices }}
        </p>
        <p>
          <strong>Tags:</strong>
          {{ model.task_tags }}
        </p>
      </div>
      <div class="card">
        <h3>Tag Information</h3>

        <div
          class="ml-10 tag"
          v-for="tag in model.tag">
          <p>
            <strong>Tag Name:</strong>
            <span class="badge">{{ tag.tagname }}</span>
          </p>
          <p>
            <strong>Description:</strong>
            {{ tag.tagDescription || 'No description provided' }}
          </p>
          <p>
            <strong>Created At:</strong>
            {{ tag.created_at }}
          </p>
          <p>
            <strong>Updated At:</strong>
            {{ tag.updated_at }}
          </p>
          <p>
            <a
              href="{{ tag.view_url }}"
              class="badge">
              View Tag
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
