<script setup>
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import { CardContent } from "@/components/ui/card";
import { Terminal, FileJson } from "lucide-vue-next";
import { useCopy } from "@/composables/useCopy";

const props = defineProps({
  pagename: {
    type: String,
    required: true,
  },
  endpoints: {
    type: Object,
    required: true,
  },
});

const { copyItem, activeCopyIcon } = useCopy();

// Format parameters as a JSON object for POST/PUT request bodies.
const formatParamsBody = (params) => {
  let result = {};
  params.forEach((param) => {
    if (param.name && param.example !== undefined) {
      try {
        if (typeof param.example === "object" && param.example !== null) {
          result[param.name] = param.example;
        } else if (
          typeof param.example === "string" &&
          (param.example.startsWith("[") || param.example.startsWith("{"))
        ) {
          try {
            result[param.name] = JSON.parse(param.example);
          } catch (e) {
            result[param.name] = param.example;
          }
        } else {
          result[param.name] = param.example;
        }
      } catch (e) {
        result[param.name] = param.example;
      }
    }
  });
  return JSON.stringify(result, null, 2);
};

function getMethodVariant(method) {
  const methodMap = {
    get: "secondary",
    post: "success",
    put: "warning",
    delete: "danger",
    patch: "info",
  };
  return methodMap[method.toLowerCase()] || "default";
}

function formatUrlWithToken(endpoint) {
  return `${endpoint.url}${endpoint.hasQueryString ? "&" : "?"}apitoken=YOUR_API_TOKEN`;
}

function formatCurlExample(endpoint) {
  if (
    endpoint.method.toLowerCase() === "get" ||
    endpoint.method.toLowerCase() === "delete"
  ) {
    return `curl -X ${endpoint.method.toUpperCase()} \\
  "https://your-rconfig-server.com${endpoint.url}" \\
  -H "Accept: application/json" \\
  -H "Content-Type: application/json" \\
  -H "apitoken: YOUR_API_TOKEN"`;
  }
  return `curl -X ${endpoint.method.toUpperCase()} \\
  "https://your-rconfig-server.com${endpoint.url}" \\
  -H "Accept: application/json" \\
  -H "Content-Type: application/json" \\
  -H "apitoken: YOUR_API_TOKEN" \\
  -d '${formatParamsBody(endpoint.parameters)}'`;
}
</script>

<template>
  <CardContent>
    <div class="space-y-8">
      <!-- Endpoints summary -->
      <div>
        <h3 class="text-lg font-medium mb-4">{{ pagename }} Endpoints</h3>
        <div class="space-y-2">
          <div
            v-for="(endpoint, key) in endpoints"
            :key="key"
            class="flex items-center justify-between p-2 rounded-md hover:bg-muted/50 transition-colors"
          >
            <div class="flex items-center gap-2">
              <RcBadge
                :variant="getMethodVariant(endpoint.method)"
                class="uppercase"
                >{{ endpoint.method }}</RcBadge
              >
              <code class="font-mono text-sm">{{ endpoint.url }}</code>
            </div>
            <span class="text-sm text-muted-foreground">{{
              endpoint.description
            }}</span>
          </div>
        </div>
      </div>

      <!-- Detailed endpoints -->
      <div
        v-for="(endpoint, key) in endpoints"
        :key="`detail-${key}`"
        class="border-t pt-6"
      >
        <div class="flex items-center gap-2 mb-4">
          <RcBadge
            :variant="getMethodVariant(endpoint.method)"
            class="uppercase"
            >{{ endpoint.method }}</RcBadge
          >
          <h3 class="text-lg font-medium">{{ endpoint.description }}</h3>
        </div>

        <!-- Endpoint URL -->
        <div class="mb-6">
          <h4 class="text-sm font-medium mb-2">Endpoint URL</h4>
          <div
            class="pre-container bg-muted rounded-md p-3 font-mono text-sm relative"
          >
            <pre class="whitespace-pre-wrap">{{
              formatUrlWithToken(endpoint)
            }}</pre>
            <Button
              class="absolute top-2 right-2 h-6 p-1 ml-auto"
              variant="ghost"
              title="copy raw data"
              @click="copyItem(`url-${key}`, formatUrlWithToken(endpoint))"
            >
              <RcIcon
                name="copy-transition"
                :isActive="activeCopyIcon[`url-${key}`]"
                :size="16"
              />
            </Button>
          </div>
        </div>

        <!-- Parameters -->
        <div class="mb-6">
          <h4 class="text-sm font-medium mb-2">Parameters</h4>
          <p
            v-if="endpoint.parametersdescription"
            class="text-sm text-muted-foreground mb-2"
          >
            {{ endpoint.parametersdescription }}
          </p>

          <p
            v-if="endpoint.parameters.length === 0"
            class="text-sm text-muted-foreground"
          >
            No parameters required
          </p>

          <div v-else class="overflow-x-auto">
            <table class="w-full border-collapse mb-4">
              <thead>
                <tr class="bg-muted/50 text-left">
                  <th
                    class="px-4 py-2 text-xs font-medium text-muted-foreground uppercase"
                  >
                    Name
                  </th>
                  <th
                    class="px-4 py-2 text-xs font-medium text-muted-foreground uppercase"
                  >
                    Type
                  </th>
                  <th
                    class="px-4 py-2 text-xs font-medium text-muted-foreground uppercase"
                  >
                    Description
                  </th>
                  <th
                    class="px-4 py-2 text-xs font-medium text-muted-foreground uppercase"
                  >
                    Example
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border">
                <tr
                  v-for="(param, index) in endpoint.parameters"
                  :key="`param-${index}`"
                  class="hover:bg-muted/30"
                >
                  <td class="px-4 py-2 font-medium">{{ param.name }}</td>
                  <td class="px-4 py-2 text-muted-foreground">
                    {{ param.type }}
                  </td>
                  <td class="px-4 py-2 text-muted-foreground">
                    {{ param.description }}
                  </td>
                  <td class="px-4 py-2 font-mono text-xs">
                    {{ param.example }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            v-if="
              endpoint.parameters.length > 0 &&
              !endpoint.parametersUrlOnly &&
              (endpoint.method.toLowerCase() === 'post' ||
                endpoint.method.toLowerCase() === 'put')
            "
          >
            <h5 class="text-sm font-medium mt-4 mb-2">Example Request Body</h5>
            <div
              class="pre-container bg-muted rounded-md p-3 font-mono text-sm relative"
            >
              <pre
                class="whitespace-pre-wrap"
              ><code>{{ formatParamsBody(endpoint.parameters) }}</code></pre>
              <Button
                class="absolute top-2 right-2 h-6 p-1 ml-auto"
                variant="ghost"
                title="copy raw data"
                @click="
                  copyItem(`body-${key}`, formatParamsBody(endpoint.parameters))
                "
              >
                <RcIcon
                  name="copy-transition"
                  :isActive="activeCopyIcon[`body-${key}`]"
                  :size="16"
                />
              </Button>
            </div>
          </div>
        </div>

        <!-- Example request -->
        <div class="mb-6">
          <h4 class="text-sm font-medium mb-2 flex items-center gap-2">
            <Terminal class="h-4 w-4" />
            Example Request
          </h4>
          <div
            class="pre-container bg-muted rounded-md p-3 font-mono text-sm relative"
          >
            <pre class="whitespace-pre-wrap">{{
              formatCurlExample(endpoint)
            }}</pre>
            <Button
              class="absolute top-2 right-2 h-6 p-1 ml-auto"
              variant="ghost"
              title="copy raw data"
              @click="copyItem(`curl-${key}`, formatCurlExample(endpoint))"
            >
              <RcIcon
                name="copy-transition"
                :isActive="activeCopyIcon[`curl-${key}`]"
                :size="16"
              />
            </Button>
          </div>
        </div>

        <!-- Expected response -->
        <div class="mb-6">
          <h4 class="text-sm font-medium mb-2 flex items-center gap-2">
            <FileJson class="h-4 w-4" />
            Expected Response
          </h4>
          <p
            v-if="endpoint.responsesdescription"
            class="text-sm text-muted-foreground mb-2"
          >
            {{ endpoint.responsesdescription }}
          </p>
          <div
            class="pre-container bg-muted rounded-md p-3 font-mono text-sm relative"
          >
            <pre class="whitespace-pre-wrap">{{
              JSON.stringify(endpoint.responses, null, 2)
            }}</pre>
            <Button
              class="absolute top-2 right-2 h-6 p-1 ml-auto"
              variant="ghost"
              title="copy raw data"
              @click="
                copyItem(
                  `resp-${key}`,
                  JSON.stringify(endpoint.responses, null, 2),
                )
              "
            >
              <RcIcon
                name="copy-transition"
                :isActive="activeCopyIcon[`resp-${key}`]"
                :size="16"
              />
            </Button>
          </div>
        </div>

        <AlertInfo
          v-if="key === '0'"
          variant="dark"
          title="Authorization Required"
          message="All API requests require authentication. Include your API token in the apitoken header or as a URL parameter."
          class="mt-6"
        />
      </div>
    </div>
  </CardContent>
</template>
