<script setup>
import NavPanelI18N from "@/i18n/pages/Tasks/WizardPanels/NavPanel.i18n.js";
import { computed } from "vue";
import { Check, Info, Settings, Calendar, CheckCircle } from "lucide-vue-next";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const props = defineProps({
    currentStep: {
        type: Number,
        required: true,
        default: 1,
    },
    completedSteps: {
        type: Array,
        default: () => [],
    },
});

const { t } = useComponentTranslations(NavPanelI18N);

// Step configuration with icons and colors
const steps = [
    {
        id: 2,
        key: "taskInfo",
        label: t("taskInfo"),
        icon: Info,
        color: "#04a5e5",
        description: "Basic information"
    },
    {
        id: 3,
        key: "taskConfiguration", 
        label: t("taskConfiguration"),
        icon: Settings,
        color: "#c6a0f6",
        description: "Configure parameters"
    },
    {
        id: 4,
        key: "taskSchedule",
        label: t("taskSchedule"),
        icon: Calendar,
        color: "#f97316",
        description: "Set timing"
    },
    {
        id: 5,
        key: "taskFinalize",
        label: t("taskFinalize"),
        icon: CheckCircle,
        color: "#059669",
        description: "Review & finalize"
    }
];

const getStepStatus = (step) => {
    const completedStepsArray = Array.isArray(props.completedSteps) ? props.completedSteps : [];
    
    if (completedStepsArray.includes(step.id)) return 'completed';
    if (step.id === props.currentStep) return 'active';
    if (step.id < props.currentStep) return 'completed';
    return 'pending';
};

const completionPercentage = computed(() => {
    const completedStepsArray = Array.isArray(props.completedSteps) ? props.completedSteps : [];
    return Math.round((completedStepsArray.length / steps.length) * 100);
});
</script>

<template>
    <div class="space-y-6 ml-2">
        <!-- Vertical Step Timeline -->
        <div class="relative">
            <div 
                class="absolute left-4 top-0 w-0.5 bg-gradient-to-b from-cyan-500 via-purple-500 to-emerald-600 transition-all duration-1000 ease-out"
                :style="{ height: `${completionPercentage}%` }"
            ></div>

            <!-- Steps -->
            <div class="space-y-6">
                <div 
                    v-for="(step, index) in steps"
                    :key="step.id"
                    class="relative flex items-center group"
                >
                    <!-- Step Circle -->
                    <div 
                        class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300 hover:scale-110"
                        :class="{
                            'bg-gray-800 border-gray-600': getStepStatus(step) === 'pending',
                            'border-gray-500 shadow-lg': getStepStatus(step) === 'active',
                            'completed-step-circle': getStepStatus(step) === 'completed',
                        }"
                        :style="getStepStatus(step) === 'active' ? { 
                            borderColor: step.color, 
                            backgroundColor: step.color + '20',
                            boxShadow: `0 0 15px ${step.color}40`
                        } : {}"
                    >
                        <!-- Inner glow for completed steps -->
                        <div 
                            v-if="getStepStatus(step) === 'completed'"
                            class="absolute inset-0.5 rounded-full bg-gradient-to-br from-emerald-400/20 to-emerald-600/30 animate-pulse-slow"
                        ></div>

                        <Check 
                            v-if="getStepStatus(step) === 'completed'" 
                            class="w-4 h-4 text-emerald-100 relative z-10 drop-shadow-sm animate-check-in" 
                        />
                        <component 
                            v-else-if="getStepStatus(step) === 'active'"
                            :is="step.icon" 
                            class="w-4 h-4"
                            :style="{ color: step.color }"
                        />
                        <span 
                            v-else
                            class="text-xs font-medium text-gray-400"
                        >
                            {{ index + 1 }}
                        </span>
                    </div>

                    <!-- Step Content -->
                    <div class="ml-4 flex-1">
                        <div 
                            class="font-medium transition-all duration-300"
                            :class="{
                                'text-gray-400': getStepStatus(step) === 'pending',
                                'text-white': getStepStatus(step) === 'active' || getStepStatus(step) === 'completed',
                            }"
                            :style="getStepStatus(step) === 'active' ? { color: step.color } : {}"
                        >
                            {{ step.label }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ step.description }}
                        </div>
                        
                        <!-- Active Step Indicator -->
                        <div 
                            v-if="getStepStatus(step) === 'active'"
                            class="mt-1.5 flex items-center text-xs font-medium animate-pulse"
                            :style="{ color: step.color }"
                        >
                            <div class="w-1.5 h-1.5 rounded-full mr-2" :style="{ backgroundColor: step.color }"></div>
                            Current Step
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Summary -->
        <div class="p-3 bg-gray-800/30 rounded-lg border border-gray-700/50 mr-4">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-400">Status:</span>
                <span class="text-white font-medium">
                    Step {{ props.currentStep - 1 }} of {{ steps.length }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.completed-step-circle {
    background: linear-gradient(135deg, #065f46, #047857, #059669) !important;
    border: 2px solid #10b981 !important;
    box-shadow: 
        0 0 0 3px rgba(16, 185, 129, 0.1),
        0 0 20px rgba(16, 185, 129, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.1),
        inset 0 -1px 0 rgba(0, 0, 0, 0.2) !important;
}

@keyframes check-in {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-check-in {
    animation: check-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* Slow pulse for inner glow */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.6;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 3s ease-in-out infinite;
}
</style>