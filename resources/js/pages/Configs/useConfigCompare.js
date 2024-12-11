import axios from 'axios';
import { inject, ref } from 'vue';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { useRouter } from 'vue-router';

export function useConfigCompare() {
  const leftConfigData = ref({
    selectedCommand: [],
    device: [],
    start_date: '',
    end_date: ''
  });
  const rightConfigData = ref({
    selectedCommand: [],
    device: [],
    start_date: '',
    end_date: ''
  });

  const leftConfigFilterKey = ref(100);
  const leftConfigResultsKey = ref(200);
  const leftSelectedId = ref([]);
  const loadComparison = ref(false);
  const navClosed = ref(false);
  const panelElement3 = ref(null);
  const rightConfigFilterKey = ref(300);
  const rightConfigResultsKey = ref(400);
  const rightSelectedId = ref([]);
  const router = useRouter();
  const { toastSuccess, toastError } = useToaster(); // Using toaster for notifications

  const updateConfigFilterData = (position, data) => {
    if (position === 'left') {
      leftConfigData.value = data;
    } else if (position === 'right') {
      rightConfigData.value = data;
    }
    leftConfigResultsKey.value += 1;
    rightConfigResultsKey.value += 1;
  };

  const sendConfigCompare = () => {
    if (leftSelectedId.length === 0 || rightSelectedId.length === 0) {
      toastError('Please select configurations for comparison');
      return;
    }

    toastSuccess('Comparing configurations...', '', 2000);
    loadComparison.value = true;
  };

  const close = () => {
    // nav back to previous page
    router.go(-1);
  };

  const reset = () => {
    leftSelectedId.value = [];
    leftConfigData.value = {
      selectedCommand: [],
      device: [],
      start_date: '',
      end_date: ''
    };
    rightSelectedId.value = [];
    rightConfigData.value = {
      selectedCommand: [],
      device: [],
      start_date: '',
      end_date: ''
    };
    loadComparison.value = false;
    leftConfigResultsKey.value += 1;
    leftConfigFilterKey.value += 1;
    rightConfigResultsKey.value += 1;
    rightConfigFilterKey.value += 1;
  };

  function closeNav() {
    panelElement3?.value.isCollapsed ? panelElement3?.value.expand() : panelElement3?.value.collapse();
    navClosed.value = !navClosed.value;
  }
  function openNav() {
    panelElement3?.value.isCollapsed ? panelElement3?.value.expand() : panelElement3?.value.collapse();
    navClosed.value = !navClosed.value;
  }

  return {
    close,
    closeNav,
    leftConfigData,
    leftConfigFilterKey,
    leftConfigResultsKey,
    leftSelectedId,
    loadComparison,
    navClosed,
    openNav,
    panelElement3,
    reset,
    rightConfigData,
    rightConfigFilterKey,
    rightConfigResultsKey,
    rightSelectedId,
    sendConfigCompare,
    updateConfigFilterData
  };
}
