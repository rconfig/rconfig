<script setup>
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Input } from "@/components/ui/input";
import ExternalLinkButtonComponent from "@/pages/Shared/Links/ExternalLinkButtonComponent.vue";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import {
  Search,
  BookOpen,
  FileText,
  Server,
  Database,
  FileDown,
  Code,
  ArrowLeft,
} from "lucide-vue-next";
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();
const searchQuery = ref("");

function goBack() {
  router.push("/settings/restapi");
}

// Documentation navigation, scoped to the endpoints Core actually ships.
const docNavigation = [
  {
    id: "overview",
    label: "Overview",
    icon: BookOpen,
    children: [
      {
        id: "get-started",
        label: "Getting Started",
        path: "/settings/restapi-docs/get-started",
      },
      {
        id: "authentication",
        label: "Authentication",
        path: "/settings/restapi-docs/authentication",
      },
      {
        id: "testing-the-api",
        label: "Testing the API",
        path: "/settings/restapi-docs/testing-the-api",
      },
      {
        id: "dashboard-health-latest-v2",
        label: "Dashboard Health Latest v2",
        path: "/settings/restapi-docs/dashboard/health-latest-v2",
        badge: "New",
      },
    ],
  },
  {
    id: "devices",
    label: "Devices API",
    icon: Server,
    children: [
      {
        id: "devices",
        label: "Devices v1",
        path: "/settings/restapi-docs/devices",
      },
      {
        id: "devices-v2",
        label: "Devices v2",
        path: "/settings/restapi-docs/devices/devices-v2",
      },
      {
        id: "device-creds",
        label: "Device Credentials",
        path: "/settings/restapi-docs/devices/device-creds",
      },
      {
        id: "templates",
        label: "Templates",
        path: "/settings/restapi-docs/devices/templates",
      },
      {
        id: "categories",
        label: "Categories",
        path: "/settings/restapi-docs/devices/categories",
      },
      {
        id: "commands",
        label: "Commands",
        path: "/settings/restapi-docs/devices/commands",
      },
      {
        id: "vendors",
        label: "Vendors",
        path: "/settings/restapi-docs/devices/vendors",
      },
      {
        id: "tags",
        label: "Tags",
        path: "/settings/restapi-docs/devices/tags",
      },
    ],
  },
  {
    id: "configs",
    label: "Configs API",
    icon: FileText,
    children: [
      {
        id: "configs",
        label: "Configs v1",
        path: "/settings/restapi-docs/configs",
      },
      {
        id: "configs-v2",
        label: "Configs v2",
        path: "/settings/restapi-docs/configs-v2",
      },
      {
        id: "config-changes-v2",
        label: "Config Changes v2",
        path: "/settings/restapi-docs/configs/config-changes-v2",
        badge: "New",
      },
      {
        id: "download-now",
        label: "Download Now",
        path: "/settings/restapi-docs/download-now",
      },
    ],
  },
  {
    id: "tasks",
    label: "Tasks API",
    icon: Database,
    children: [
      { id: "tasks", label: "Tasks", path: "/settings/restapi-docs/tasks" },
    ],
  },
  {
    id: "users",
    label: "Users API",
    icon: FileDown,
    children: [
      { id: "users", label: "Users", path: "/settings/restapi-docs/users" },
    ],
  },
];

const allMenuItems = computed(() => {
  const items = [];
  docNavigation.forEach((category) => {
    (category.children || []).forEach((child) => {
      items.push({ ...child, categoryLabel: category.label });
    });
  });
  return items;
});

const filteredMenuItems = computed(() => {
  if (!searchQuery.value) {
    return [];
  }
  const query = searchQuery.value.toLowerCase();
  return allMenuItems.value.filter((item) =>
    item.label.toLowerCase().includes(query),
  );
});

function navigateTo(path) {
  router.push(path);
  searchQuery.value = "";
}

function isActive(path) {
  return route.path === path;
}
</script>

<template>
  <div
    class="space-y-6 w-full px-6 mt-2 max-h-[calc(100vh-100px)] overflow-y-auto"
  >
    <Card class="border shadow-xs">
      <CardHeader class="flex flex-row items-center justify-between">
        <div>
          <CardTitle class="text-2xl font-semibold flex items-center gap-2">
            <Code class="h-6 w-6" />
            REST API Documentation
          </CardTitle>
          <CardDescription
            >Explore and learn how to use the rConfig REST API</CardDescription
          >
        </div>
        <Button
          variant="outline"
          size="sm"
          @click="goBack"
          class="flex items-center gap-2"
        >
          <ArrowLeft class="h-4 w-4" />
          Back to API Settings
        </Button>
      </CardHeader>
      <CardContent>
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Sidebar navigation -->
          <div class="md:w-1/4 space-y-4">
            <div class="relative">
              <Search
                class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground"
              />
              <Input
                type="search"
                placeholder="Search documentation..."
                class="pl-8"
                v-model="searchQuery"
              />
            </div>

            <div
              v-if="searchQuery && filteredMenuItems.length > 0"
              class="absolute z-50 bg-rcgray-100 dark:bg-rcgray-900 border rounded-md shadow-md w-[calc(25%-1rem)] py-1 max-h-[300px] overflow-y-auto"
            >
              <button
                v-for="item in filteredMenuItems"
                :key="item.path"
                @click="navigateTo(item.path)"
                class="w-full px-3 py-2 text-left text-sm hover:bg-accent flex flex-col"
              >
                <span class="font-medium">{{ item.label }}</span>
                <span class="text-xs text-muted-foreground">{{
                  item.categoryLabel
                }}</span>
              </button>
            </div>

            <ScrollArea class="h-[calc(100vh-260px)] min-h-[320px] pr-2">
              <nav class="space-y-4">
                <div v-for="category in docNavigation" :key="category.id">
                  <h3
                    class="flex items-center gap-2 px-2 mb-1 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground"
                  >
                    <component
                      :is="category.icon"
                      class="h-3.5 w-3.5 shrink-0"
                    />
                    <span class="truncate">{{ category.label }}</span>
                  </h3>
                  <ul class="space-y-px border-l border-border/60 ml-4">
                    <li v-for="item in category.children" :key="item.id">
                      <button
                        type="button"
                        @click="navigateTo(item.path)"
                        :class="[
                          'group w-full flex items-center justify-between gap-2 -ml-px pl-3 pr-2 py-1 text-sm rounded-r-md border-l-2 transition-colors',
                          isActive(item.path)
                            ? 'border-primary bg-accent/60 text-accent-foreground font-medium'
                            : 'border-transparent text-muted-foreground hover:text-foreground hover:bg-accent/40 hover:border-border',
                        ]"
                      >
                        <span class="truncate text-left">{{ item.label }}</span>
                        <RcBadge
                          v-if="item.badge"
                          variant="new"
                          class="text-[10px] shrink-0"
                          >{{ item.badge }}</RcBadge
                        >
                      </button>
                    </li>
                  </ul>
                </div>
              </nav>
            </ScrollArea>
          </div>

          <!-- Content -->
          <div class="md:w-3/4">
            <Card class="min-h-[600px]">
              <router-view></router-view>
            </Card>
          </div>
        </div>
      </CardContent>
      <CardFooter class="justify-end">
        <p class="text-sm text-muted-foreground mr-2">Need more help?&nbsp;</p>
        <ExternalLinkButtonComponent
          :to="'https://www.rconfig.com'"
          text="rConfig Support"
          variant="outline"
          size="sm"
        />
      </CardFooter>
    </Card>
  </div>
</template>
