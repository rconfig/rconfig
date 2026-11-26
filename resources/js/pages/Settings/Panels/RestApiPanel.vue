<script setup>
import { computed, getCurrentInstance, ref } from "vue";
import { useRouter } from "vue-router";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Maximize2 } from "lucide-vue-next";
import RestApiToken from "@/pages/Settings/Panels/Components/RestApiToken.vue";

const router = useRouter();
const activeTab = ref("token");

// Every token has full Admin rights, so only Admin accounts can manage tokens.
// The server enforces this; here we avoid showing a list that would return 403.
// The role is matched case insensitively, as User::isAdmin() does on the server.
const userRole =
  getCurrentInstance()?.appContext.config.globalProperties.$userRole ?? "";
const isAdmin = computed(() => String(userRole).toLowerCase() === "admin");

function navigateToDocs() {
  router.push("/settings/restapi-docs/get-started");
}
</script>

<template>
	<Card>
		<CardHeader>
			<CardTitle>REST API</CardTitle>
			<CardDescription>
				Manage API tokens for the external rConfig REST API (versions v1 and
				v2).
			</CardDescription>
		</CardHeader>
		<CardContent>
			<Tabs
				v-model="activeTab"
				class="w-full"
			>
				<TabsList class="grid grid-cols-2 w-[420px]">
					<TabsTrigger value="token">
						API Token
					</TabsTrigger>
					<TabsTrigger
						value="docs"
						class="relative hover:text-muted-foreground"
						@click="navigateToDocs"
					>
						Documentation
						<Maximize2 class="h-3.5 w-3.5 ml-1.5 inline-block" />
					</TabsTrigger>
				</TabsList>

				<TabsContent value="token">
					<RestApiToken v-if="isAdmin" />
					<Alert
						v-else
						data-testid="rest-api-admin-only"
					>
						<AlertTitle>Admin account required</AlertTitle>
						<AlertDescription>
							Only Admin accounts can manage API tokens, because every token
							has full Admin rights on the REST API. Ask an Admin to create a
							token for you.
						</AlertDescription>
					</Alert>
				</TabsContent>
			</Tabs>
		</CardContent>
	</Card>
</template>
