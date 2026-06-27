import axios from "axios";
import { ref } from "vue";
import { useToaster } from "@/composables/useToaster";

// Module-level state so the list and the create-modal share the same tokens.
const tokens = ref([]);
const isLoading = ref(false);
const isSubmitting = ref(false);

export function useRestApi() {
  const { toastSuccess, toastError } = useToaster();

  async function fetchTokens() {
    isLoading.value = true;
    try {
      const response = await axios.get("/api/settings/rest-api-token");
      tokens.value = response.data.data ?? [];
    } catch (e) {
      toastError("Error", "Unable to load API tokens.");
    } finally {
      isLoading.value = false;
    }
  }

  async function createToken(name) {
    isSubmitting.value = true;
    try {
      const response = await axios.post("/api/settings/rest-api-token", {
        api_token_name: name,
      });
      toastSuccess("Success", "API token created.");
      await fetchTokens();
      // The plaintext token is only returned here, once.
      return response.data.data;
    } catch (e) {
      const message =
        e?.response?.data?.message ?? "Unable to create API token.";
      toastError(
        "Error",
        typeof message === "string" ? message : "Unable to create API token.",
      );
      return null;
    } finally {
      isSubmitting.value = false;
    }
  }

  async function deleteToken(id) {
    try {
      await axios.delete(`/api/settings/rest-api-token/${id}`);
      toastSuccess("Success", "API token deleted.");
      await fetchTokens();
    } catch (e) {
      toastError("Error", "Unable to delete API token.");
    }
  }

  return {
    tokens,
    isLoading,
    isSubmitting,
    fetchTokens,
    createToken,
    deleteToken,
  };
}
