<template>
    <tbody role="rowgroup">
        <tr role="row">
            <td role="cell" colspan="8">
                <div class="pf-l-bullseye">
                    <div class="pf-c-empty-state pf-m-sm">
                        <div class="pf-c-empty-state__content">
                            <div class="pf-c-empty-state__icon">
                                <span class="pf-c-spinner" role="progressbar" aria-label="Loading...">
                                    <span class="pf-c-spinner__clipper"></span>
                                    <span class="pf-c-spinner__lead-ball"></span>
                                    <span class="pf-c-spinner__tail-ball"></span>
                                </span>
                            </div>
                            <h2 class="pf-c-title pf-m-lg">Loading</h2>
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
