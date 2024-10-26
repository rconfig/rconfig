import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useDebounceFn } from '@vueuse/core';

export function useDeviceViewPane(props, emit) {
  const isLoading = ref(false);
  const deviceData = ref(null);

  onMounted(() => {
    fetchDevice(props.editId);
  });

  function fetchDevice(id) {
    isLoading.value = true;
    axios.get(`/api/devices/${id}`).then(response => {
      deviceData.value = response.data;
      isLoading.value = false;
    });
  }

  return {
    isLoading,
    deviceData
  };
}
