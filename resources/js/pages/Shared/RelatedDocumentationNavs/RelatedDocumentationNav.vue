<script setup>
// -----------------------------------------------------------------------------
// RelatedDocumentationNav Component
// -----------------------------------------------------------------------------
// Purpose:  A reusable component to display documentation links throughout the app.
//           Handles both internal and external links with appropriate icons.
//           Useful for linking to related documentation, guides, or external resources.
// 			 Primarily used at the bottom/ end of tables, forms, or pages. See Users for example
// 		 	 resources/js/pages/Settings/Users/Main.vue
//
// Usage:    <RelatedDocumentationNav
// 				v-if="relatedDocs.length > 0"
//             :relatedDocs="[
//               {
//                 title: 'Title',           // Required: Link text
//                 description: 'Desc',      // Required: Short description
//                 link: '/route-or-url',    // Required: Internal path or external URL
//                 icon: 'icon-name'         // Optional: Icon key from iconMap
//               }
//             ]"
//             header="Optional Header"      // Optional: Custom heading
//             align="left"                  // Optional: left, center, right
//           />
//
// Features: - Auto-detects external vs internal links
//           - Responsive grid layout (1 or 2 columns based on item count)
//           - Configurable heading alignment
//           - Custom icons with fallback
// -----------------------------------------------------------------------------

import { ExternalLink, BookOpen, GitBranch, Code, Settings, Download, FileCog, ShieldCheck } from "lucide-vue-next";
import { computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

// Define props with validation
const props = defineProps({
	header: {
		type: String,
		default: "Related Documentation",
	},
	docs: {
		type: Array,
		required: true,
		validator: (value) => {
			return value.every((item) => typeof item.title === "string" && typeof item.description === "string" && typeof item.link === "string" && (!item.icon || typeof item.icon === "string"));
		},
	},
	align: {
		type: String,
		default: "left",
		validator: (value) => ["left", "center", "right"].includes(value),
	},
});

// Function to open links properly based on type
const openLink = (link) => {
	// Check if it's an external link (starts with http or https)
	if (link.startsWith("http://") || link.startsWith("https://")) {
		window.open(link, "_blank", "noopener,noreferrer");
	} else {
		// It's an internal link, use Vue Router
		router.push(link);
	}
};

// Map icon names to components
const getIcon = (iconName) => {
	const iconMap = {
		"external-link": ExternalLink,
		book: BookOpen,
		git: GitBranch,
		code: Code,
		settings: Settings,
		download: Download,
		file: FileCog,
		security: ShieldCheck,
		// Add more icons as needed
	};

	return iconMap[iconName] || BookOpen; // Default to BookOpen if icon not found
};

// Compute header alignment class
const headerClass = computed(() => {
	switch (props.align) {
		case "center":
			return "text-center";
		case "right":
			return "text-right";
		default:
			return "text-left";
	}
});

// Determine grid columns based on number of items
const gridClass = computed(() => {
	return props.docs.length === 1 ? "grid-cols-1" : "grid-cols-1 md:grid-cols-2";
});
</script>

<template>
	<div class="border-t pt-6">
		<h3 :class="['text-lg font-medium mb-4', headerClass]">{{ header }}</h3>
		<div :class="['grid gap-4', gridClass]">
			<Button v-for="(doc, index) in docs" :key="index" variant="outline" class="justify-start h-auto p-4 bg-gradient-to-r from-rcgray-800 hover:from-rcgray-900 to-rcgray-900 border border-rcgray-700 rounded-lg transition-all duration-200 hover:border-rcgray-600" @click="openLink(doc.link)">
				<div class="flex flex-col items-start w-full">
					<div class="flex items-center gap-2 w-full">
						<span class="font-medium">{{ doc.title }}</span>

						<!-- Show external link icon for external links, or specified icon -->
						<component :is="doc.link.startsWith('http') ? ExternalLink : getIcon(doc.icon)" class="h-3.5 w-3.5 text-muted-foreground ml-auto" />
					</div>
					<span class="text-sm text-muted-foreground truncate max-w-full">{{ doc.description }}</span>
				</div>
			</Button>
		</div>
	</div>
</template>
