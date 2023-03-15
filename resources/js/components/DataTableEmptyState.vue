<template>
    <tbody role="rowgroup">
        <tr role="row">
            <td role="cell" colspan="8">
                <div class="pf-l-bullseye">
                    <div class="pf-c-empty-state pf-m-sm">
                        <div class="pf-c-empty-state__content">
                            <i class="fas fa- fa-search pf-c-empty-state__icon" aria-hidden="true"></i>

                            <h2 class="pf-c-title pf-m-lg">No results found</h2>
                            <div class="pf-c-empty-state__body">
                                No results match the filter criteria. Remove all filters or clear all filters to show results.
                            </div>
                            <div class="pf-c-empty-state__primary">
                                <button class="pf-c-button pf-m-link" type="button" @click="$emit('clear')">Clear all filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {},

    setup() {
        return {};
    }
};
</script>
