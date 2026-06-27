<script setup>
import AlertWarning from "@/pages/Shared/Alerts/AlertWarning.vue";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";
import {
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { CheckCircle, Code, Copy, Check } from "lucide-vue-next";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ref } from "vue";
import { useCopy } from "@/composables/useCopy";

const activeTab = ref("overview");
const hasCopied = ref(false);
// Sample code to copy
const curlExample = `curl -X GET \\
  https://your-rconfig-server.com/api/v1/devices \\
	-H 'apitoken: YOUR_API_TOKEN' \\
  -H 'Content-Type: application/json'`;
const { copyItem, activeCopyIcon } = useCopy();
</script>

<template>
	<CardHeader>
		<CardTitle>Getting Started with rConfig API</CardTitle>
		<CardDescription>
			Everything you need to know to get started with the rConfig REST
			API
		</CardDescription>
	</CardHeader>

	<CardContent>
		<div class="space-y-6">
			<div class="flex items-start gap-2">
				<CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
				<div>
					<h3 class="text-base font-medium">
						API Overview
					</h3>
					<p class="text-muted-foreground mt-1">
						The rConfig REST API allows you to programmatically access and
						manage your network device configurations. You can create, read,
						update, and delete resources using standard HTTP methods.
					</p>
				</div>
			</div>

			<div class="flex items-start gap-2">
				<CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
				<div>
					<h3 class="text-base font-medium">
						Base URL
					</h3>
					<p class="text-muted-foreground mt-1">
						All API requests should be made to the following base URL:
					</p>
					<div class="bg-muted p-3 rounded-md mt-2 font-mono text-sm">
						https://your-rconfig-server.com/api/v1/
					</div>
				</div>
			</div>

			<div class="border-t pt-6">
				<h3 class="text-lg font-medium mb-2">
					New Endpoint in API v2
				</h3>
				<p class="text-muted-foreground mb-4">
					We added a new v2 endpoint for dashboard health checks:
					<span class="font-mono">GET /api/v2/dashboard/health-latest</span>
				</p>
				<router-link to="/settings/restapi-docs/dashboard/health-latest-v2">
					<Button
						variant="outline"
						size="sm"
					>
						Open Dashboard Health Latest v2 Docs
					</Button>
				</router-link>
			</div>

			<div class="border-t pt-6">
				<h3 class="text-lg font-medium mb-2">
					Pagination Behavior (v2)
				</h3>
				<p class="text-muted-foreground mb-3">
					Pagination parameters and limits are endpoint-specific. For endpoints
					that support pagination, use the endpoint docs for exact behavior.
				</p>
				<ul class="list-disc ml-5 space-y-2 text-muted-foreground">
					<li>
						Devices v2 list supports both
						<span class="font-mono">per_page</span> and
						<span class="font-mono">perPage</span>.
					</li>
					<li>
						When both are supplied,
						<span class="font-mono">per_page</span> takes precedence.
					</li>
					<li>
						Devices v2 list is capped at
						<span class="font-mono">100</span> records per page. The effective
						<span class="font-mono">per_page</span> is returned in the response
						metadata.
					</li>
					<li>
						If the requested value exceeds the cap, the API applies the cap
						rather than silently using the default.
					</li>
					<li>
						Response shape note: list endpoints return paginated payloads with
						<span class="font-mono">data[]</span>, while some single-resource
						endpoints return a flat object.
					</li>
				</ul>

				<AlertInfo
					class="mt-4"
					variant="dark"
					title="Integration Recommendation"
					message="For new integrations, prefer per_page for paginated requests and always read the effective per_page field in the response metadata."
				/>
			</div>

			<div class="border-t pt-6">
				<h3 class="text-lg font-medium mb-4">
					API Versioning
				</h3>
				<p class="text-muted-foreground">
					The rConfig API uses versioning to ensure backward compatibility. The
					current version is
				</p>

				<Tabs
					v-model="activeTab"
					class="w-full mt-4"
				>
					<TabsList>
						<TabsTrigger value="overview">
							Overview
						</TabsTrigger>
						<TabsTrigger value="versioning">
							Versioning
						</TabsTrigger>
						<TabsTrigger value="changelog">
							Changelog
						</TabsTrigger>
					</TabsList>

					<TabsContent
						value="overview"
						class="space-y-4 mt-4"
					>
						<h4 class="font-medium">
							Using the API
						</h4>
						<p class="text-muted-foreground">
							To make API requests, you'll need to:
						</p>
						<ol class="list-decimal ml-5 space-y-2">
							<li>Generate an API token in your rConfig settings</li>
							<li>Include the token in the Authorization header</li>
							<li>Make requests to the endpoints using HTTP methods</li>
						</ol>

						<AlertWarning
							variant="dark"
							title="Important"
							message="Keep your API token secure. It has the same permissions as your user account."
						/>
					</TabsContent>

					<TabsContent
						value="versioning"
						class="mt-4"
					>
						<div class="space-y-4">
							<div>
								<h4 class="font-medium">
									Version History
								</h4>
								<ul class="mt-2 space-y-3">
									<li class="flex items-start gap-2">
										<RcBadge variant="outline">
											v1
										</RcBadge>
										<span class="text-muted-foreground">Initial API release (Current)</span>
									</li>
									<li class="flex items-start gap-2">
										<RcBadge variant="outline">
											v2
										</RcBadge>
										<span class="text-muted-foreground">Beta - New endpoints for devices and configs</span>
									</li>
								</ul>
							</div>
						</div>
					</TabsContent>

					<TabsContent
						value="changelog"
						class="mt-4"
					>
						<div class="space-y-4">
							<div>
								<h4 class="font-medium">
									v1.2.0 (Current)
								</h4>
								<ul class="list-disc ml-5 mt-2 space-y-1 text-muted-foreground">
									<li>Added endpoints for tags management</li>
									<li>
										Improved error responses with more detailed information
									</li>
									<li>Fixed issue with device template association</li>
								</ul>
							</div>
							<div>
								<h4 class="font-medium">
									v1.1.0
								</h4>
								<ul class="list-disc ml-5 mt-2 space-y-1 text-muted-foreground">
									<li>Added bulk operations for devices</li>
								</ul>
							</div>
							<div>
								<h4 class="font-medium">
									v1.0.0
								</h4>
								<ul class="list-disc ml-5 mt-2 space-y-1 text-muted-foreground">
									<li>Initial API release</li>
									<li>
										Basic CRUD operations for devices, configs, and templates
									</li>
								</ul>
							</div>
						</div>
					</TabsContent>
				</Tabs>
			</div>

			<div class="border-t pt-6">
				<h3 class="text-lg font-medium mb-4">
					Example Request
				</h3>
				<p class="text-muted-foreground mb-4">
					Here's an example of how to make a request to the API using cURL:
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
							:is-active="activeCopyIcon['curlExample']"
							:size="16"
						/>
					</Button>
				</div>
			</div>
		</div>
	</CardContent>
</template>
