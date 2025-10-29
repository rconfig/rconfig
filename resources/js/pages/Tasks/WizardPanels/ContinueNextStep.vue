<script setup>
defineProps({
    title: {
        type: String,
        default: "Nothing to configure here"
    },
    subtitle: {
        type: String,
        default: "Go ahead and set your schedule for this task. Go to the next step."
    },
    icon: {
        type: String,
        default: "arrow-right"
    }
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-6 px-4 text-center">
        <div class="w-12 h-12 mb-4 flex items-center justify-center">
            <RcIcon name="continue" :width="36" :height="36" class="text-primary" />
        </div>
        
        <h3 class="text-lg font-semibold text-foreground mb-2">
            {{ title }}
        </h3>
        
        <p class="text-muted-foreground text-sm max-w-sm leading-relaxed">
            {{ subtitle }}
        </p>
        
        <div class="mt-4 p-3 bg-muted/50 rounded-lg border border-dashed border-muted-foreground/20">
            <div class="flex items-center justify-center space-x-2 text-xs text-muted-foreground">
                <RcIcon name="info" class="w-3 h-3" />
                <span>Ready to proceed to scheduling</span>
            </div>
        </div>
    </div>
</template>