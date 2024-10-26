import axios from 'axios';
import { ref, onMounted, onUnmounted } from 'vue';
import { useFavoritesStore } from '@/stores/favorites';

export function useDeviceViewPane(props) {
  const favoritesStore = useFavoritesStore();
  const isLoading = ref(false);
  const deviceData = ref(null);
  const leftNavSelected = ref('details');
  const mainNavSelected = ref('notifications');

  const favoriteItem = ref({
    id: props.editId,
    label: '',
    icon: 'NetworkDeviceIcon',
    isFavorite: false,
    route: '/devices/view/' + props.editId
  });

  onMounted(() => {
    fetchDevice(props.editId);

    window.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        onEsc();
      }
    });

    if (deviceData) {
      [...favoritesStore.favorites].forEach(favorite => {
        if (favorite.id === props.editId) {
          favoriteItem.value.isFavorite = true;
        }
      });
    }
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', e => {
      if (e.key === 'Escape') {
        onEsc();
      }
    });
  });

  function fetchDevice(id) {
    isLoading.value = true;
    axios.get(`/api/devices/${id}`).then(response => {
      deviceData.value = response.data;
      isLoading.value = false;
    });
  }

  function addToFavorites() {
    if (deviceData) {
      favoriteItem.value.id = deviceData.value.id;
      favoriteItem.value.label = deviceData.value.device_name;
      favoriteItem.value.route = `/devices/view/${deviceData.value.id}`;
      favoriteItem.value.isFavorite = !favoriteItem.value.isFavorite;
      favoritesStore.toggleFavorite(favoriteItem.value);
    }
  }

  function selectLeftNavView(viewName) {
    if (viewName === 'details') {
      leftNavSelected.value = 'details';
    } else if (viewName === 'comments') {
      leftNavSelected.value = 'comments';
    } else {
      leftNavSelected.value = 'details';
    }
  }
  function selectMainNavView(viewName) {
    if (viewName === 'notifications') {
      mainNavSelected.value = 'notifications';
    } else if (viewName === 'configs') {
      mainNavSelected.value = 'configs';
    } else {
      mainNavSelected.value = 'notifications';
    }
  }

  function close() {
    emit('close');
  }

  function onEsc() {
    close();
  }

  return {
    addToFavorites,
    deviceData,
    favoriteItem,
    isLoading,
    mainNavSelected,
    selectLeftNavView,
    selectMainNavView
  };
}
