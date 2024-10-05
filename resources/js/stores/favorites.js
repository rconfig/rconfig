import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useFavoritesStore = defineStore('favorites', () => {
  const favorites = ref(new Set(JSON.parse(localStorage.getItem('favorites') || '[]')));

  function toggleFavorite(view) {
    const favorite = [...favorites.value].find(item => item.id === view.id);
    if (favorite) {
      favorites.value.delete(favorite);
    } else {
      favorites.value.add(view);
    }
    saveToLocalStorage();
  }

  function isFavorite(viewId) {
    return [...favorites.value].some(item => item.id === viewId);
  }

  function saveToLocalStorage() {
    localStorage.setItem('favorites', JSON.stringify([...favorites.value]));
  }

  return {
    favorites,
    toggleFavorite,
    isFavorite
  };
});
