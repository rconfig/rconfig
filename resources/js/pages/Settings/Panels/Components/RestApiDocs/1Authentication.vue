<script setup>
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import AlertWarning from "@/pages/Shared/Alerts/AlertWarning.vue";
import {
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import {
  Copy,
  Code,
  Shield,
  KeyRound,
  AlertTriangle,
  CheckCircle,
  Check,
} from "lucide-vue-next";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ref } from "vue";
import { useCopy } from "@/composables/useCopy";

const activeTab = ref("token-auth");
const hasCopied = ref({
  header: false,
  url: false,
  curl: false,
  js: false,
});

// Sample code snippets for copying
const curlExample = `curl -i -H "Accept: application/json" -H "Content-Type: application/json" -H "apitoken: YOUR_API_TOKEN" https://your-rconfig-server.com/api/v1/configs/1`;
const headerExample = "apitoken: YOUR_API_TOKEN";
const urlExample =
  "https://your-rconfig-server.com/api/v1/configs/1?apitoken=YOUR_API_TOKEN";
const jsExample = `fetch('https://your-rconfig-server.com/api/v1/configs/1', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'apitoken': 'YOUR_API_TOKEN'
  }
})
.then(response => response.json())
.then(data => console.log(data));`;
const { copyItem, activeCopyIcon } = useCopy();
</script>

<template>
  <CardHeader>
    <CardTitle>Authentication</CardTitle>
    <CardDescription
      >Secure access to the rConfig API using token-based
      authentication</CardDescription
    >
  </CardHeader>

  <CardContent>
    <div class="space-y-6">
      <div class="flex items-start gap-2">
        <Shield class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
        <div>
          <h3 class="text-base font-medium">Authentication Overview</h3>
          <p class="text-muted-foreground mt-1">
            The rConfig API uses token-based authentication. You must include a
            valid API token in all your API requests to access protected
            endpoints.
          </p>
        </div>
      </div>

      <div class="flex items-start gap-2">
        <KeyRound class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
        <div>
          <h3 class="text-base font-medium">Obtaining an API Token</h3>
          <p class="text-muted-foreground mt-1">
            You can generate API tokens from the rConfig web interface:
          </p>
          <ol class="list-decimal ml-5 mt-2 space-y-1 text-muted-foreground">
            <li>Navigate to Settings &gt; REST API</li>
            <li>Click on New Token</li>
            <li>Enter a descriptive name for your token</li>
            <li>Copy and securely store the generated token</li>
          </ol>
          <AlertWarning
            class="mt-6"
            variant="dark"
            title="Important"
            message="The token will only be shown once when it's created. If you lose it, you'll need to generate a new one."
          />
        </div>
      </div>

      <Tabs v-model="activeTab" class="w-full mt-6">
        <TabsList>
          <TabsTrigger value="token-auth">Token Authentication</TabsTrigger>
          <TabsTrigger value="header-format">Example Requests</TabsTrigger>
          <TabsTrigger value="troubleshooting">Troubleshooting</TabsTrigger>
        </TabsList>

        <TabsContent value="token-auth" class="space-y-4 mt-4">
          <h4 class="font-medium">Using API Token Authentication</h4>
          <p class="text-muted-foreground">
            You can authenticate your requests in two ways: by passing the token
            in the URL or in the HTTP header.
          </p>

          <div class="space-y-6">
            <div>
              <h5 class="text-sm font-medium mb-2">
                Option 1: Pass token in URL
              </h5>

              <div
                class="pre-container bg-muted rounded-md p-4 font-mono text-sm relative"
              >
                <pre class="whitespace-pre-wrap">{{ urlExample }}</pre>
                <Button
                  class="absolute top-2 right-2 h-6 p-1 ml-auto"
                  variant="ghost"
                  title="copy raw data"
                  @click="copyItem('urlExample', urlExample)"
                >
                  <RcIcon
                    name="copy-transition"
                    :isActive="activeCopyIcon['urlExample']"
                    :size="16"
                  />
                </Button>
              </div>
            </div>

            <div>
              <h5 class="text-sm font-medium mb-2">
                Option 2: Pass token in header
              </h5>
              <div
                class="pre-container bg-muted rounded-md p-4 font-mono text-sm relative"
              >
                <pre class="whitespace-pre-wrap">{{ headerExample }}</pre>
                <Button
                  class="absolute top-2 right-2 h-6 p-1 ml-auto"
                  variant="ghost"
                  title="copy raw data"
                  @click="copyItem('headerExample', headerExample)"
                >
                  <RcIcon
                    name="copy-transition"
                    :isActive="activeCopyIcon['headerExample']"
                    :size="16"
                  />
                </Button>
              </div>
            </div>
          </div>

          <div class="flex items-start gap-2 mt-4">
            <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0" />
            <p class="text-sm">
              Replace YOUR_API_TOKEN with the actual token you generated in the
              rConfig web interface.
            </p>
          </div>
        </TabsContent>

        <TabsContent value="header-format" class="mt-4">
          <h4 class="font-medium">Example Request with curl</h4>
          <p class="text-muted-foreground mb-4">
            Here's an example of how to make an authenticated request using cURL
            with the header method:
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

          <h4 class="font-medium mt-6">Using with JavaScript</h4>
          <div
            class="pre-container bg-muted rounded-md p-4 font-mono text-sm relative"
          >
            <pre class="whitespace-pre-wrap">{{ jsExample }}</pre>
            <Button
              class="absolute top-2 right-2 h-6 p-1 ml-auto"
              variant="ghost"
              title="copy raw data"
              @click="copyItem('jsExample', jsExample)"
            >
              <RcIcon
                name="copy-transition"
                :isActive="activeCopyIcon['jsExample']"
                :size="16"
              />
            </Button>
          </div>
        </TabsContent>

        <TabsContent value="troubleshooting" class="mt-4 space-y-4">
          <h4 class="font-medium">Common Authentication Issues</h4>

          <div class="border rounded-md">
            <div class="p-4 border-b">
              <h5 class="font-medium">401 Unauthorized Error</h5>
              <p class="text-muted-foreground text-sm mt-1">
                If you receive a 401 error, your token may be invalid or
                expired. Try generating a new token.
              </p>
            </div>

            <div class="p-4 border-b">
              <h5 class="font-medium">Missing API Token</h5>
              <p class="text-muted-foreground text-sm mt-1">
                Ensure you're including the API token either in the URL as a
                query parameter or in the header.
              </p>
            </div>

            <div class="p-4">
              <h5 class="font-medium">403 Forbidden Error</h5>
              <p class="text-muted-foreground text-sm mt-1">
                Your token may not have sufficient permissions to access the
                requested resource.
              </p>
            </div>
          </div>

          <AlertTip
            class="mt-4"
            variant="dark"
            title="Support Tip"
            message="If you continue to experience authentication issues, check the rConfig system logs for more detailed error information."
          />
        </TabsContent>
      </Tabs>

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-4">Token Security Best Practices</h3>
        <ul class="space-y-3">
          <li class="flex items-start gap-2">
            <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
            <p class="text-muted-foreground">
              <span class="text-foreground font-medium"
                >Store tokens securely:</span
              >
              Never hardcode tokens in your application or commit them to
              version control.
            </p>
          </li>
          <li class="flex items-start gap-2">
            <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
            <p class="text-muted-foreground">
              <span class="text-foreground font-medium"
                >Use environment variables:</span
              >
              Store tokens as environment variables or in secure credential
              storage.
            </p>
          </li>
          <li class="flex items-start gap-2">
            <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
            <p class="text-muted-foreground">
              <span class="text-foreground font-medium"
                >Rotate tokens regularly:</span
              >
              Generate new tokens periodically and revoke unused ones.
            </p>
          </li>
          <li class="flex items-start gap-2">
            <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
            <p class="text-muted-foreground">
              <span class="text-foreground font-medium">Use HTTPS:</span> Always
              use HTTPS to encrypt API requests and prevent token interception.
            </p>
          </li>
        </ul>
      </div>

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-4">Related Documentation</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Button
            variant="outline"
            class="justify-start h-auto p-4"
            @click="$router.push('/settings/restapi-docs/get-started')"
          >
            <div class="flex flex-col items-start">
              <span class="font-medium">Getting Started</span>
              <span class="text-sm text-muted-foreground"
                >Introduction to the rConfig API</span
              >
            </div>
          </Button>
          <Button
            variant="outline"
            class="justify-start h-auto p-4"
            @click="$router.push('/settings/restapi-docs/testing-the-api')"
          >
            <div class="flex flex-col items-start">
              <span class="font-medium">Testing the API</span>
              <span class="text-sm text-muted-foreground"
                >How to test API endpoints</span
              >
            </div>
          </Button>
        </div>
      </div>
    </div>
  </CardContent>
</template>
