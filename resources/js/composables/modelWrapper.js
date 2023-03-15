//Credit: https://vanoneang.github.io/article/v-model-in-vue3.html#turn-it-into-a-composable
import { computed } from 'vue';
export function useModelWrapper(props, name = 'modelValue') {
    return computed({
        get: () => props[name],
        set: (value) => emit(`update:${name}`, value)
    });
}
