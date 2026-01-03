<script setup lang="ts">
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { Kbd, KbdGroup } from "@/components/ui/kbd";

interface Props {
	shortcut?: "submit" | "cancel" | "esc" | "new" | string;
	keys?: string | string[];
	className?: string;
	noBorder?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	className: "",
	noBorder: false,
});

const presets = {
	submit: { keys: ["Ctrl", "Enter"], icon: "enter" },
	cancel: { keys: ["Esc"] },
	esc: { keys: ["Esc"] },
	new: { keys: ["ALT + N"] },
	edit: { keys: ["ALT + E"] },
	history: { keys: ["ALT + H"] },
	save: { keys: ["ALT + S"] },
	backup: { keys: ["ALT + B"] },
	space: { keys: ["Space"] },
	alt1: { keys: ["ALT + 1"] },
	alt2: { keys: ["ALT + 2"] },
	alt3: { keys: ["ALT + 3"] },
	alt4: { keys: ["ALT + 4"] },
	ctrlslash: { keys: ["⌘ Ctrl+/"] },
	ctrlk: { keys: ["⌘ Ctrl+K"] },
};

const shortcutConfig = computed(() => {
	if (props.shortcut && props.shortcut in presets) {
		return presets[props.shortcut as keyof typeof presets];
	}

	const keys = typeof props.keys === "string" ? props.keys.split("+").map((k) => k.trim()) : props.keys || [];

	return { keys };
});

// <KeyboardShortcut shortcut="submit" />
// <KeyboardShortcut shortcut="esc" />
// <KeyboardShortcut shortcut="cancel" /> <!-- same as esc -->
// <KeyboardShortcut shortcut="new"  />
// <KeyboardShortcut shortcut="edit"  />
// <KeyboardShortcut shortcut="history"  />
// <KeyboardShortcut shortcut="save"  />
// <KeyboardShortcut shortcut="backup"  />
// <KeyboardShortcut shortcut="space"  />

// <KeyboardShortcut shortcut="new" class-name="py-1 px-2" /> <!-- for custom styling
</script>

<template>
	<div :class="cn('pl-2 ml-auto font-mono', className)">
		<KbdGroup :class="{ 'no-border': noBorder }">
			<template v-for="(key, index) in shortcutConfig.keys" :key="index">
				<Kbd>
					<RcIcon v-if="key === 'Enter' && shortcutConfig.icon" :name="shortcutConfig.icon" class="ml-1" />
					<span v-else>{{ key }}</span>
				</Kbd>
				<span v-if="index < shortcutConfig.keys.length - 1">+</span>
			</template>
		</KbdGroup>
	</div>
</template>
