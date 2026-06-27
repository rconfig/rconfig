<script setup>
import { onMounted, computed } from "vue";
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import { useToaster } from "@/composables/useToaster";
import { useVersionCheck } from "@/composables/useVersionCheck";
import { ArrowUpCircle, CheckCircle2, AlertTriangle, RefreshCw, Github, BookOpen, Copy, Server, Container } from "lucide-vue-next";

const { currentVersion, latestVersion, updateAvailable, latestUrl, reachable, checked, lastCheckedAt, consecutiveFailures, lastError, loading, hasFetched, fetchVersionStatus } = useVersionCheck();
const { toastSuccess, toastError } = useToaster();

const lastCheckedLabel = computed(() => {
	if (!lastCheckedAt.value) {
		return "Not checked yet";
	}
	return new Date(lastCheckedAt.value).toLocaleString();
});

// Standard (bare metal / git) update procedure, derived from the V8 Core docs update process page.
const standardSteps = [
	{
		title: "Move to the application directory",
		commands: ["cd /var/www/html/rconfig"],
	},
	{
		title: "Pull the latest release from GitHub",
		commands: ["git pull"],
	},
	{
		title: "Update Composer dependencies",
		commands: ["composer install --no-dev"],
	},
	{
		title: "Run database migrations",
		commands: ["php artisan migrate"],
	},
	{
		title: "Sync scheduled tasks",
		commands: ["php artisan rconfig:sync-tasks"],
	},
	{
		title: "Clear all caches",
		commands: ["php artisan rconfig:clear-all"],
	},
	{
		title: "Restart the web server and queue workers",
		commands: ["systemctl restart httpd        # CentOS / Rocky / RHEL / Alma", "sudo systemctl restart apache2  # Ubuntu", "systemctl restart supervisord"],
	},
];

// Docker image update procedure, derived from the V8 Core Docker container docs.
const dockerSteps = [
	{
		title: "Back up the database",
		commands: ["docker compose exec -T db mysqldump -u root -proot_password rconfig > backup.sql"],
	},
	{
		title: "Rebuild the containers with the latest image",
		commands: ["docker compose down", "docker compose build --no-cache", "docker compose up -d"],
	},
	{
		title: "Run migrations and clear caches",
		commands: ["docker compose exec app php artisan migrate --force", "docker compose exec app php artisan cache:clear", "docker compose exec app php artisan config:clear", "docker compose exec app php artisan horizon:terminate"],
	},
];

onMounted(() => {
	fetchVersionStatus();
});

async function copyCommand(command) {
	try {
		await navigator.clipboard.writeText(command);
		toastSuccess("Copied", "Command copied to clipboard.");
	} catch (error) {
		toastError("Copy failed", "Could not copy the command to the clipboard.");
	}
}
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col w-full gap-6 md:w-3/4">
			<div>
				<h3 class="mb-1 text-2xl font-semibold leading-7 tracking-tight font-inter">
					Update rConfig V8 Core
				</h3>
				<p class="text-sm text-muted-foreground">
					Check for new releases and follow the steps below to update your installation.
				</p>
			</div>

			<!-- Version status card -->
			<Card>
				<CardHeader>
					<div class="flex items-center justify-between">
						<div>
							<CardTitle>Version status</CardTitle>
							<CardDescription>Compared against the latest release tagged on GitHub.</CardDescription>
						</div>
						<Button
							variant="outline"
							size="sm"
							:disabled="loading"
							@click="fetchVersionStatus(true)"
						>
							<RefreshCw
								class="w-4 h-4 mr-2"
								:class="{ 'animate-spin': loading }"
							/>
							Recheck
						</Button>
					</div>
				</CardHeader>
				<CardContent class="space-y-4">
					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">
								Installed version
							</dt>
							<dd class="font-medium">
								{{ currentVersion || "—" }}
							</dd>
						</div>
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">
								Latest version
							</dt>
							<dd class="font-medium">
								{{ latestVersion || "Unknown" }}
							</dd>
						</div>
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">
								Last checked
							</dt>
							<dd class="font-medium">
								{{ lastCheckedLabel }}
							</dd>
						</div>
					</dl>

					<!-- Surfaced when recent background checks could not reach GitHub. -->
					<div
						v-if="checked && !reachable && consecutiveFailures > 0"
						class="flex items-start gap-2 px-3 py-2 text-sm rounded-md bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300"
					>
						<AlertTriangle class="w-4 h-4 mt-0.5 shrink-0" />
						<span>
							Couldn't reach GitHub on the last {{ consecutiveFailures }} check{{ consecutiveFailures === 1 ? "" : "s" }}.
							<span
								v-if="lastError"
								class="opacity-80"
							>({{ lastError }})</span>
							The version shown may be out of date.
						</span>
					</div>

					<Separator />

					<div class="flex flex-wrap items-center justify-between gap-3">
						<div class="flex items-center gap-2">
							<Badge
								v-if="!hasFetched || !reachable"
								variant="secondary"
								class="gap-1"
							>
								<AlertTriangle class="w-3.5 h-3.5" />
								Couldn't reach GitHub
							</Badge>
							<Badge
								v-else-if="updateAvailable"
								class="gap-1 bg-amber-500 hover:bg-amber-500/90 text-white"
							>
								<ArrowUpCircle class="w-3.5 h-3.5" />
								Update available
							</Badge>
							<Badge
								v-else
								class="gap-1 bg-green-600 hover:bg-green-600/90 text-white"
							>
								<CheckCircle2 class="w-3.5 h-3.5" />
								Up to date
							</Badge>
						</div>

						<a
							v-if="reachable && updateAvailable && latestUrl"
							:href="latestUrl"
							target="_blank"
							rel="noopener"
						>
							<Button
								variant="outline"
								size="sm"
							>
								<Github class="w-4 h-4 mr-2" />
								View release on GitHub
							</Button>
						</a>
					</div>
				</CardContent>
			</Card>

			<!-- Backup reminder -->
			<AlertTip
				title="Back up before you update"
				:small="true"
				message="Always take a full database backup, a copy of your <strong>.env</strong> file, and your <strong>storage</strong> directory before updating. Never update without verified backups."
			/>

			<!-- Update steps -->
			<Card>
				<CardHeader>
					<CardTitle>Update steps</CardTitle>
					<CardDescription>Run these commands on your rConfig server once your backups are complete. Choose the tab that matches how you installed rConfig.</CardDescription>
				</CardHeader>
				<CardContent class="space-y-5">
					<Tabs
						default-value="standard"
						class="w-full"
					>
						<TabsList class="grid w-full grid-cols-2 max-w-sm">
							<TabsTrigger value="standard">
								<Server class="w-4 h-4 mr-2" />
								Standard
							</TabsTrigger>
							<TabsTrigger value="docker">
								<Container class="w-4 h-4 mr-2" />
								Docker
							</TabsTrigger>
						</TabsList>

						<TabsContent
							value="standard"
							class="mt-4 space-y-5"
						>
							<div
								v-for="(step, index) in standardSteps"
								:key="step.title"
								class="space-y-2"
							>
								<div class="flex items-center gap-2">
									<span class="flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full bg-rcgray-700 text-rcgray-200 shrink-0">{{ index + 1 }}</span>
									<span class="text-sm font-medium">{{ step.title }}</span>
								</div>
								<div
									v-for="command in step.commands"
									:key="command"
									class="flex items-center justify-between gap-2 px-3 py-2 font-mono text-xs rounded-md bg-rcgray-900 border border-rcgray-700"
								>
									<code class="overflow-x-auto whitespace-pre text-rcgray-200">{{ command }}</code>
									<Button
										variant="ghost"
										size="icon"
										class="w-6 h-6 shrink-0"
										title="Copy command"
										@click="copyCommand(command)"
									>
										<Copy class="w-3.5 h-3.5" />
									</Button>
								</div>
							</div>
						</TabsContent>

						<TabsContent
							value="docker"
							class="mt-4 space-y-5"
						>
							<p class="text-sm text-muted-foreground">
								Run these from the directory containing your <code class="px-1 font-mono text-xs rounded bg-rcgray-900">docker-compose.yml</code>. Adjust the database credentials to match your environment.
							</p>
							<div
								v-for="(step, index) in dockerSteps"
								:key="step.title"
								class="space-y-2"
							>
								<div class="flex items-center gap-2">
									<span class="flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full bg-rcgray-700 text-rcgray-200 shrink-0">{{ index + 1 }}</span>
									<span class="text-sm font-medium">{{ step.title }}</span>
								</div>
								<div
									v-for="command in step.commands"
									:key="command"
									class="flex items-center justify-between gap-2 px-3 py-2 font-mono text-xs rounded-md bg-rcgray-900 border border-rcgray-700"
								>
									<code class="overflow-x-auto whitespace-pre text-rcgray-200">{{ command }}</code>
									<Button
										variant="ghost"
										size="icon"
										class="w-6 h-6 shrink-0"
										title="Copy command"
										@click="copyCommand(command)"
									>
										<Copy class="w-3.5 h-3.5" />
									</Button>
								</div>
							</div>
							<p class="text-sm text-muted-foreground">
								To pin a specific release, rebuild with a build argument, for example <code class="px-1 font-mono text-xs rounded bg-rcgray-900">docker compose build --no-cache --build-arg RCONFIG_VERSION=core-{{ latestVersion || "8.0.2" }}</code>.
							</p>
						</TabsContent>
					</Tabs>

					<Separator />

					<div class="flex flex-wrap items-center justify-between gap-3">
						<p class="text-sm text-muted-foreground">
							For the full procedure including backup, rollback and troubleshooting steps, see the documentation.
						</p>
						<a
							:href="$rconfigDocsUrl + '/installation-upgrades/v8-core/update-process/'"
							target="_blank"
							rel="noopener"
						>
							<Button
								variant="outline"
								size="sm"
							>
								<BookOpen class="w-4 h-4 mr-2" />
								Full update guide
							</Button>
						</a>
					</div>
				</CardContent>
			</Card>
		</div>
	</div>
</template>
