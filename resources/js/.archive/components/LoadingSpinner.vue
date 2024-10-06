<template>
    <div v-if="showSpinner">
        <span class="pf-c-spinner" style="margin: 0 auto; display: table" role="progressbar" aria-valuetext="Loading...">
            <span class="pf-c-spinner__clipper"></span>
            <span class="pf-c-spinner__lead-ball"></span>
            <span class="pf-c-spinner__tail-ball"></span>
        </span>
    </div>
</template>

<script>
export default {
    data: () => ({}),
    props: ['showSpinner'],
    components: {},
    methods: {},
    mounted() {},
    created() {}
};
</script>

<style lang="scss" scoped></style>
