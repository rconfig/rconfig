import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useFavoritesStore } from '@/stores/favorites';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router

export function useInventory() {
  const favoritesStore = useFavoritesStore();
  const currentView = ref(localStorage.getItem('inventorySelectedView') || 'devices');
  const route = useRoute();
  const router = useRouter();

  const viewItems = [
    { id: 'devices', label: 'Devices', icon: 'DeviceIcon', isFavorite: ref(false), route: '/devices' },
    { id: 'commandgroups', label: 'Command Groups', icon: 'CommandGroupIcon', isFavorite: ref(false), route: '/commandgroups' },
    { id: 'commands', label: 'Commands', icon: 'CommandsIcon', isFavorite: ref(false), route: '/commands' },
    { id: 'templates', label: 'Templates', icon: 'TemplateIcon', isFavorite: ref(false), route: '/templates' },
    { id: 'vendors', label: 'Vendors', icon: 'VendorIcon', isFavorite: ref(false), route: '/vendors' },
    { id: 'tags', label: 'Tags', icon: 'TagIcon', isFavorite: ref(false), route: '/tags' }
  ];

  onMounted(() => {
    if (route.params.view) {
      changeView(route.params.view);
    } // Set currentView if path is not Inventory
    else if (route.name === 'devicesview') {
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

  return {
    changeView,
    currentView,
    favoritesStore,
    route,
    router,
    toggleFavorite,
    viewItems
  };
}
