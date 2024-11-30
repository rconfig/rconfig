import axios from 'axios';
import { ref, onMounted, nextTick } from 'vue';
import { useFavoritesStore } from '@/stores/favorites';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

export function useConfigs() {
  const favoritesStore = useFavoritesStore();
  const currentView = ref(localStorage.getItem('inventorySelectedView') || 'devices');
  const route = useRoute();
  const router = useRouter();
  const configsId = ref(parseInt(route.params.id) || 0);
  const statusIdParam = ref(route.query || null);
  const bottomBorderStyle = ref({});

  const viewItems = [
    { id: 'configs', label: 'Configurations', icon: 'ConfigToolsIcon', isFavorite: ref(false), route: '/configs' },
    { id: 'configsearch', label: 'Config Search', icon: 'ConfigSearchIcon', isFavorite: ref(false), route: '/config-search' },
    { id: 'configcompare', label: 'Config Compare', icon: 'ConfigCompareIcon', isFavorite: ref(false), route: '/config-compare' }
  ];

  onMounted(() => {
    if (route.params.view) {
      changeView(route.params.view);
    } // Set currentView if path is not Inventory
    else if (route.name === 'configs') {
      changeView(route.name); // If route starts with /devices, load the devices component
    } else if (!route.path.includes('inventory')) {
      changeView(route.name); // loads the current view based on the route name
    }

    // Preserve query params
    const queryParams = route.query;
    if (Object.keys(queryParams).length > 0) {
      router.push({ name: currentView.value, query: queryParams });
    }

    viewItems.forEach(item => {
      item.isFavorite.value = favoritesStore.isFavorite(item.id);
    });
  });

  function changeView(view) {
    updateBottomBorder(view);
    localStorage.setItem('inventorySelectedView', view);
    currentView.value = view;
    router.push({ name: view });
  }

  function toggleFavorite(viewId) {
    const viewItem = viewItems.find(item => item.id === viewId);
    if (viewItem) {
      viewItem.isFavorite.value = !viewItem.isFavorite.value;
      favoritesStore.toggleFavorite(viewItem);
    }
  }

  function updateBottomBorder(view) {
    const selectedButton = document.querySelector(`[data-nav='${view}']`);

    nextTick(() => {
      if (selectedButton) {
        const { offsetLeft, offsetWidth } = selectedButton;
        bottomBorderStyle.value = {
          left: `${offsetLeft}px`,
          width: `${offsetWidth}px`
        };
      }
    });
  }

  return {
    bottomBorderStyle,
    configsId,
    statusIdParam,
    changeView,
    currentView,
    favoritesStore,
    route,
    router,
    toggleFavorite,
    viewItems
  };
}
