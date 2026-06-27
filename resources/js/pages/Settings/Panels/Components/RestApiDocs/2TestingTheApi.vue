<script setup>
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import {
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import {
  Copy,
  Terminal,
  Brackets,
  FileJson,
  InfoIcon,
  AlertTriangle,
  CheckCircle,
  Check,
} from "lucide-vue-next";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ref, reactive } from "vue";
import { useCopy } from "@/composables/useCopy";
import { useRouter } from "vue-router";

const activeTab = ref("curl");
const browserExample =
  "https://your-rconfig-server.com/api/v1/devices?apitoken=YOUR_API_TOKEN";
const curlExample =
  'curl -i -H "Accept: application/json" -H "Content-Type: application/json" -H "apitoken: YOUR_API_TOKEN" https://your-rconfig-server.com/api/v1/devices';
const router = useRouter();
const { copyItem, activeCopyIcon } = useCopy();

// Track copy states for each button
const hasCopied = ref({
  main: false,
  browser: false,
});

// Track copy states for endpoints
const endpointCopied = ref([false, false]);

// Define sample endpoints for testing
const endpoints = reactive([
  {
    description: "Get all devices",
    method: "GET",
    url: "/api/v1/devices",
    curlExample:
      'curl -i -H "Accept: application/json" -H "Content-Type: application/json" -H "apitoken: YOUR_API_TOKEN" https://your-rconfig-server.com/api/v1/devices',
    responseExample: {
      status: "success",
      data: [
        {
          id: 1,
          name: "CoreRouter01",
          ip: "192.168.1.1",
          vendor: "Cisco",
          model: "ISR4431",
          category: "Core",
        },
        {
          id: 2,
          name: "EdgeSwitch02",
          ip: "192.168.1.2",
          vendor: "HP",
          model: "Aruba 2930F",
          category: "Edge",
        },
      ],
    },
  },
  {
    description: "Get a single device",
    method: "GET",
    url: "/api/v1/devices/{id}",
    curlExample:
      'curl -i -H "Accept: application/json" -H "Content-Type: application/json" -H "apitoken: YOUR_API_TOKEN" https://your-rconfig-server.com/api/v1/devices/1',
    responseExample: {
      status: "success",
      data: {
        id: 1,
        name: "CoreRouter01",
        ip: "192.168.1.1",
        vendor: "Cisco",
        model: "ISR4431",
        category: "Core",
        created_at: "2023-01-15T14:23:12.000000Z",
        updated_at: "2023-03-22T09:45:17.000000Z",
      },
    },
  },
]);
</script>

<template>
  <CardHeader>
    <CardTitle>Testing the API</CardTitle>
    <CardDescription
      >Learn how to test rConfig API endpoints effectively</CardDescription
    >
  </CardHeader>

  <CardContent>
    <div class="space-y-6">
      <div class="flex items-start gap-2">
        <Terminal class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
        <div>
          <h3 class="text-base font-medium">Making Test Requests</h3>
          <p class="text-muted-foreground mt-1">
            You can test the rConfig API using various HTTP clients such as
            cURL, Postman, or any programming language with HTTP capabilities.
            Below are examples to get you started.
          </p>
        </div>
      </div>

      <AlertInfo
        class="mt-4"
        variant="dark"
        title="Important"
        message="All API requests require authentication. Make sure to include your API token in the header or as a URL parameter."
      />

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-4">Testing Tools</h3>

        <Tabs v-model="activeTab" class="w-full">
          <TabsList>
            <TabsTrigger value="curl">cURL</TabsTrigger>
            <TabsTrigger value="postman">Postman</TabsTrigger>
            <TabsTrigger value="browser">Browser</TabsTrigger>
          </TabsList>

          <TabsContent value="curl" class="space-y-4 mt-4">
            <p class="text-muted-foreground">
              cURL is a command-line tool for making HTTP requests. It's
              available on most operating systems and is a quick way to test API
              endpoints.
            </p>

            <div
              class="pre-container bg-muted rounded-md p-4 font-mono text-sm relative"
            >
              <pre class="whitespace-pre-wrap">{{ curlExample }}</pre>
              <Button
                class="absolute top-2 right-2 h-6 p-1 ml-auto"
                variant="ghost"
                title="copy raw data"
                @click="copyItem('curlExample', curlExample)"
              >
                <RcIcon
                  name="copy-transition"
                  :isActive="activeCopyIcon['curlExample']"
                  :size="16"
                />
              </Button>
            </div>

            <div class="flex items-start gap-2 mt-2">
              <CheckCircle class="h-4 w-4 text-green-500 flex-shrink-0 mt-1" />
              <p class="text-sm text-muted-foreground">
                Replace YOUR_API_TOKEN with your actual API token.
              </p>
            </div>

            <div class="flex items-start gap-2 mt-1">
              <CheckCircle class="h-4 w-4 text-green-500 flex-shrink-0 mt-1" />
              <p class="text-sm text-muted-foreground">
                Replace your-rconfig-server.com with your actual server domain.
              </p>
            </div>
          </TabsContent>

          <TabsContent value="postman" class="mt-4">
            <p class="text-muted-foreground mb-4">
              Postman is a popular graphical tool for testing APIs. Follow these
              steps to test with Postman:
            </p>

            <ol class="list-decimal ml-5 space-y-2">
              <li>
                <span class="font-medium">Create a new request</span>
                <p class="text-muted-foreground text-sm mt-1">
                  Click the "New" button and select "Request"
                </p>
              </li>
              <li>
                <span class="font-medium">Set the request method and URL</span>
                <p class="text-muted-foreground text-sm mt-1">
                  Select the HTTP method (GET, POST, PUT, DELETE) from the
                  dropdown and enter your API endpoint URL
                </p>
              </li>
              <li>
                <span class="font-medium">Add authentication</span>
                <p class="text-muted-foreground text-sm mt-1">
                  Go to the "Headers" tab and add a key "apitoken" with your API
                  token as the value
                </p>
              </li>
              <li>
                <span class="font-medium">Send your request</span>
                <p class="text-muted-foreground text-sm mt-1">
                  Click the "Send" button to make the API call
                </p>
              </li>
            </ol>

            <AlertTip
              class="mt-4"
              variant="dark"
              title="Tip"
              message="You can create a Postman collection to save all your API requests for future use."
            />
          </TabsContent>

          <TabsContent value="browser" class="mt-4">
            <p class="text-muted-foreground mb-4">
              For simple GET requests, you can use your browser's address bar.
              Add your API token as a URL parameter:
            </p>

            <div
              class="pre-container bg-muted rounded-md p-4 font-mono text-sm relative"
            >
              <pre class="whitespace-pre-wrap">{{ browserExample }}</pre>
              <Button
                class="absolute top-2 right-2 h-6 p-1 ml-auto"
                variant="ghost"
                title="copy raw data"
                @click="copyItem('browser', browserExample)"
              >
                <RcIcon
                  name="copy-transition"
                  :isActive="activeCopyIcon['browser']"
                  :size="16"
                />
              </Button>
            </div>

            <p class="text-muted-foreground mt-4">
              For other request types (POST, PUT, DELETE) or to view detailed
              headers, use browser developer tools or a dedicated API client.
            </p>
          </TabsContent>
        </Tabs>
      </div>

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-4">Example Endpoints for Testing</h3>

        <div class="space-y-4">
          <div
            v-for="(endpoint, index) in endpoints"
            :key="index"
            class="border rounded-lg overflow-hidden"
          >
            <div class="flex items-center justify-between p-4 bg-muted/30">
              <div class="flex items-center gap-2">
                <RcBadge
                  :variant="
                    endpoint.method === 'GET'
                      ? 'secondary'
                      : endpoint.method === 'POST'
                        ? 'success'
                        : endpoint.method === 'PUT'
                          ? 'warning'
                          : 'destructive'
                  "
                >
                  {{ endpoint.method }}
                </RcBadge>
                <code class="font-mono text-sm">{{ endpoint.url }}</code>
              </div>
            </div>
            <div class="p-4">
              <p class="font-medium mb-2">{{ endpoint.description }}</p>

              <h4 class="text-sm font-medium mt-3 mb-2">Example Request:</h4>

              <div
                class="pre-container bg-muted rounded-md p-4 font-mono text-sm relative"
              >
                <pre class="whitespace-pre-wrap">{{
                  endpoint.curlExample
                }}</pre>
                <Button
                  class="absolute top-2 right-2 h-6 p-1 ml-auto"
                  variant="ghost"
                  title="copy raw data"
                  @click="
                    copyItem('curlExample2' + index, endpoint.curlExample)
                  "
                >
                  <RcIcon
                    name="copy-transition"
                    :isActive="activeCopyIcon['curlExample2' + index]"
                    :size="16"
                  />
                </Button>
              </div>

              <h4 class="text-sm font-medium mt-4 mb-2 flex items-center">
                <FileJson class="h-4 w-4 mr-1" />
                Example Response:
              </h4>
              <div class="bg-muted rounded-md p-3 font-mono text-xs">
                <pre class="whitespace-pre-wrap">{{
                  JSON.stringify(endpoint.responseExample, null, 2)
                }}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-2">HTTP Response Codes</h3>
        <p class="text-muted-foreground mb-4">
          The API uses standard HTTP response codes to indicate success or
          failure:
        </p>

        <div class="overflow-hidden border rounded-lg">
          <table class="min-w-full divide-y divide-border">
            <thead>
              <tr class="bg-muted/50">
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase"
                >
                  Code
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase"
                >
                  Description
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  200
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <RcBadge variant="success">OK</RcBadge>
                </td>
                <td class="px-6 py-4 text-sm text-muted-foreground">
                  The request was successful
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  201
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <RcBadge variant="success">Created</RcBadge>
                </td>
                <td class="px-6 py-4 text-sm text-muted-foreground">
                  A new resource was successfully created
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  400
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <RcBadge variant="warning">Bad Request</RcBadge>
                </td>
                <td class="px-6 py-4 text-sm text-muted-foreground">
                  The request was invalid or malformed
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  401
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <RcBadge variant="danger">Unauthorized</RcBadge>
                </td>
                <td class="px-6 py-4 text-sm text-muted-foreground">
                  Authentication failed or token is invalid
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  404
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <RcBadge variant="outline">Not Found</RcBadge>
                </td>
                <td class="px-6 py-4 text-sm text-muted-foreground">
                  The requested resource was not found
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  500
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <RcBadge variant="danger">Server Error</RcBadge>
                </td>
                <td class="px-6 py-4 text-sm text-muted-foreground">
                  An error occurred on the server
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </CardContent>
</template>
