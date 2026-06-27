<script setup>
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Activity } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Dashboard Health Latest v2";

const endpoints = {
  0: {
    name: "Health Stats",
    description: "Get latest dashboard health checks and logging health",
    method: "get",
    url: "/api/v2/dashboard/health-latest",
    parameters: [
      {
        name: "clearcache",
        description: "optional",
        type: "boolean",
        example: "?clearcache=true",
      },
    ],
    parametersdescription:
      "Use clearcache=true to clear cached heartbeat metadata before retrieving the latest health batch.",
    responses: {
      success: true,
      message: "Success",
      data: [
        {
          success: true,
          data: [
            {
              name: "Database",
              label: "Database",
              notificationMessage: "ok",
              shortSummary: "",
              status: "Ok",
              meta: {
                connection_name: "pgsql",
              },
            },
            {
              name: "Cache",
              label: "Cache",
              notificationMessage: "ok",
              shortSummary: "",
              status: "Ok",
              meta: {
                driver: "redis",
              },
            },
            {
              name: "CpuLoad",
              label: "Cpu Load",
              notificationMessage: "ok",
              shortSummary: "",
              status: "0.05 0.05 0.02",
              meta: {
                last_minute: 0.05,
                last_5_minutes: 0.05,
                last_15_minutes: 0.02,
              },
            },
            {
              name: "Horizon",
              label: "Horizon",
              notificationMessage: "ok",
              shortSummary: "",
              status: "Running",
              meta: [],
            },
            {
              name: "Ping",
              label: "Ping",
              notificationMessage: "ok",
              shortSummary: "",
              status: "Reachable",
              meta: [],
            },
            {
              name: "logging",
              label: "Logging",
              notificationMessage: "File logging operational",
              shortSummary: "normal",
              status: "ok",
              meta: {
                ok: true,
                mode: "normal",
                message: "File logging operational",
                state_file:
                  "/var/www/html/rconfig8/storage/framework/logfile-backoff.state",
                next_retry_at: null,
                seconds_until_retry: null,
              },
            },
          ],
          message: "Success",
        },
      ],
    },
    responsesdescription:
      "Successful response. The checks array includes a synthetic logging entry in addition to stored health checks.",
  },
};
</script>

<template>
	<CardHeader>
		<CardTitle>Dashboard Health Latest API</CardTitle>
		<CardDescription>
			System health status endpoint for API v2 dashboard
			consumers
		</CardDescription>
	</CardHeader>

	<div class="px-6 pb-4">
		<div class="flex items-start gap-2 mb-6">
			<Activity class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
			<div>
				<h3 class="text-base font-medium">
					Endpoint Overview
				</h3>
				<p class="text-muted-foreground mt-1">
					Use this endpoint to retrieve the latest health check batch used by
					dashboard experiences and operational summaries.
				</p>
			</div>
		</div>

		<AlertInfo
			class="mt-4 mb-6"
			variant="dark"
			title="Authentication"
			message="A valid apitoken is required in the request header or query string."
		/>
	</div>

	<ApiDocsTemplate
		:pagename="pagename"
		:endpoints="endpoints"
	/>
</template>
